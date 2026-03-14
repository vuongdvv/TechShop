<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once dirname(__DIR__, 2) . '/config/config.php';

require_once dirname(__DIR__, 2) . "/config/database.php";
$vnpay = require dirname(__DIR__, 2) . "/config/vnpay.php";

$vnp_HashSecret = $vnpay['vnp_HashSecret'];


if (!isset($_GET['vnp_SecureHash'])) {
    die("Invalid VNPay response");
}

$vnp_SecureHash = $_GET['vnp_SecureHash'];

/* =========================
   2. VERIFY CHỮ KÝ (CHUẨN VNPAY)
========================= */
$inputData = [];

foreach ($_GET as $key => $value) {
    if (strpos($key, 'vnp_') === 0 && $key !== 'vnp_SecureHash' && $key !== 'vnp_SecureHashType') {
        $inputData[$key] = $value;
    }
}

ksort($inputData);

$hashData = '';
$i = 0;

foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
    } else {
        $hashData .= urlencode($key) . '=' . urlencode($value);
        $i = 1;
    }
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

if ($secureHash !== $vnp_SecureHash) {
    die("Invalid signature");
}


$txnRef = $_GET['vnp_TxnRef'] ?? '';
$orderIdParts = explode('_', $txnRef);
$orderId = isset($orderIdParts[0]) ? (int)$orderIdParts[0] : 0;

$responseCode = $_GET['vnp_ResponseCode'] ?? '';
$transactionStatus = $_GET['vnp_TransactionStatus'] ?? '';
$amount = isset($_GET['vnp_Amount']) ? ($_GET['vnp_Amount'] / 100) : 0;
$transactionNo = $_GET['vnp_TransactionNo'] ?? '';

$isSuccess = ($responseCode === '00' && $transactionStatus === '00');


if ($orderId > 0) {
    mysqli_begin_transaction($conn);

    try {
        $stmt = $conn->prepare("
            SELECT id, 
            total_price, 
            status FROM orders WHERE id = ? 
            FOR UPDATE
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if (!$order) {
            throw new Exception('Order not found');
        }

        if ((int)$amount > 0 && (int)$order['total_price'] !== (int)$amount) {
            throw new Exception('Invalid amount');
        }

        if ($isSuccess) {
            $newOrderStatus = 'paid';
            $newPaymentStatus = 'success';
        } elseif ($responseCode === '24') {
            $newOrderStatus = 'cancel';
            $newPaymentStatus = 'cancel';
        } else {
            $newOrderStatus = 'failed';
            $newPaymentStatus = 'failed';
        }

        if ($order['status'] === 'pending' && $newOrderStatus === 'paid') {
            $itemStmt = $conn->prepare("
                SELECT product_id, 
                quantity FROM order_items 
                WHERE order_id = ?
            ");
            $itemStmt->bind_param("i", $orderId);
            $itemStmt->execute();
            $items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($items as $item) {
                $stockStmt = $conn->prepare("
                    UPDATE products 
                    SET stock = stock - ? 
                    WHERE id = ? AND stock >= ?
                ");
                $stockStmt->bind_param(
                    "iii",
                    $item['quantity'],
                    $item['product_id'],
                    $item['quantity']
                );
                $stockStmt->execute();

                if ($stockStmt->affected_rows == 0) {
                    throw new Exception('Product out of stock');
                }
            }
        }

        if ($order['status'] === 'pending') {
            $updateOrder = $conn->prepare("
                UPDATE orders 
                SET status = ?, 
                vnp_transaction_no = ? 
                WHERE id = ?
            ");
            $updateOrder->bind_param("ssi", $newOrderStatus, $transactionNo, $orderId);
            $updateOrder->execute();
        }

        $payload = json_encode($_GET);
        $updatePayment = $conn->prepare("
            UPDATE payments SET status = ?, 
            transaction_code = ?, 
            response_payload = ?, 
            updated_at = NOW() 
            WHERE order_id = ?
        ");
        $updatePayment->bind_param("sssi", $newPaymentStatus, $transactionNo, $payload, $orderId);
        $updatePayment->execute();

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $isSuccess = false;
    }
}





$products = [];

if ($orderId > 0) {
    $stmt = $conn->prepare("
        SELECT p.name, oi.quantity
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Kết quả thanh toán VNPay</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9fafb;
        }

        .box {
            max-width: 520px;
            margin: 80px auto;
            background: #fff;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
            text-align: center;
        }

        .success {
            color: #16a34a;
        }

        .error {
            color: #dc2626;
        }

        .info {
            margin-top: 16px;
            text-align: left;
            font-size: 15px;
        }

        .info p {
            margin: 6px 0;
        }

        .btn {
            display: inline-block;
            margin-top: 24px;
            padding: 10px 20px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>

<body>

    <div class="box">
        <?php if ($isSuccess): ?>
            <h2 class="success">🎉 Thanh toán thành công</h2>
            <p>Cảm ơn bạn đã thanh toán qua VNPay</p>
        <?php else: ?>
            <h2 class="error">❌ Thanh toán thất bại</h2>
            <p>Giao dịch không thành công hoặc đã bị hủy</p>
        <?php endif; ?>

        <div class="info">
            <p><strong>Mã đơn hàng:</strong> #<?= htmlspecialchars($orderId) ?></p>

            <p><strong>Sản phẩm đã thanh toán:</strong></p>
            <?php if (!empty($products)): ?>
                <ul>
                    <?php foreach ($products as $item): ?>
                        <li>
                            <?= htmlspecialchars($item['name']) ?>
                            (x<?= (int)$item['quantity'] ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <p><strong>Số tiền:</strong> <?= number_format($amount, 0, ',', '.') ?> VND</p>


        </div>

        <a href="<?= rtrim(FRONT_URL, '/') ?>/product/list.php" class="btn">
            Quay lại trang sản phẩm
        </a>


    </div>

</body>

</html>
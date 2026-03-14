<?php
session_start();

require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__) . "/includes/functions.php";
require_once dirname(__DIR__, 2) . "/back-end/orders/index.php";

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đơn hàng của tôi - <?= SITE_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/cart.css">
</head>

<body>

    <?php include dirname(__DIR__) . "/includes/header.php"; ?>
    <main>
        <div class="container">
            <div class="breadcrumbs">
                <a href="<?= FRONT_URL ?>/home.php">Trang Chủ</a> >
                <span>Đơn hàng của tôi</span>
            </div>
            <h2 class="page-title">📦 Đơn hàng của tôi</h2>

            <?php if (empty($orders)): ?>
                <p>Bạn chưa có đơn hàng nào.</p>
            <?php else: ?>

                <table class="cart-table">
                    <thead>
                        <tr>
                            <td data-label="Mã đơn">Mã đơn</td>
                            <td data-label="Ngày đặt">Ngày đặt</td>
                            <td data-label="Tổng tiền">Tổng tiền</td>
                            <td data-label="Trạng thái">Trạng thái</td>
                            <td></td>

                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>

                                <td>
                                    <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                </td>

                                <td>
                                    <?= formatPrice($order['total_price']) ?>
                                </td>

                                <td>
                                    <?php
                                    $status = $order['status'];

                                    $statusMap = [
                                        'pending'   => ['text' => 'Chờ xử lý', 'color' => '#6c757d'],
                                        'paid'      => ['text' => 'Đã thanh toán', 'color' => '#28a745'],
                                        'shipped'   => ['text' => 'Đang giao', 'color' => '#fd7e14'],
                                        'completed' => ['text' => 'Hoàn tất', 'color' => '#007bff'],
                                        // 'failed'    => ['text' => 'Thanh toán thất bại', 'color' => '#dc3545'],
                                        'cancel' => ['text' => 'Đã hủy', 'color' => '#dc3545'],
                                    ];

                                    if (isset($statusMap[$status])) {
                                        echo '<span style="color:' . $statusMap[$status]['color'] . ';font-weight:600">'
                                            . $statusMap[$status]['text'] .
                                            '</span>';
                                    } else {
                                        echo '<span style="color:#6c757d">Chờ xử lý</span>';
                                    }
                                    ?>
                                </td>

                                <td>
                                    <a href="<?= FRONT_URL ?>/orders/detail.php?id=<?= $order['id'] ?>"
                                        class="btn-detail">
                                        Xem chi tiết
                                    </a>

                                    <?php if (in_array($order['status'], ['pending'])): ?>
                                        <button type="button"
                                            class="btn-cancel"
                                            onclick="cancelOrder(<?= $order['id'] ?>)">
                                            Hủy đơn hàng
                                        </button>
                                    <?php endif; ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php endif; ?>

        </div>
    </main>
    <script>
        function cancelOrder(orderId) {
            if (!confirm("Bạn có chắc muốn hủy đơn này không?")) return;

            fetch("<?= BASE_URL ?>/back-end/cart/cancel_order.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "order_id=" + orderId
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert("Hủy đơn thành công!");
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(() => {
                    alert("Có lỗi xảy ra!");
                });
        }
    </script>

</body>

</html>
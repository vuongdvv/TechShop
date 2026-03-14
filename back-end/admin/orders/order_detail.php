<?php

require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 3) . "/front-end/includes/functions.php";

requireAdminAccess($conn);

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) {
    header("Location: index.php");
    exit;
}


/* ==============================
   LẤY ORDER + PAYMENT
============================== */
$stmt = $conn->prepare("
    SELECT 
        o.*,
        p.method AS payment_method,
        p.status AS payment_status,
        a.city AS city
    FROM orders o
    LEFT JOIN payments p ON p.order_id = o.id
    LEFT JOIN addresses a ON a.user_id = o.user_id
    WHERE o.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: index.php");
    exit;
}


/* ==============================
   LABEL ORDER STATUS
============================== */
$orderLabels = [
    'pending'   => 'Chờ xử lý',
    'shipped'   => 'Đang giao hàng',
    'completed' => 'Hoàn thành',
    'cancel'    => 'Đã huỷ đơn',
    // 'failed'    => 'Thanh toán thất bại'
];

$orderStatusLabel = $orderLabels[$order['status']] ?? 'Không xác định';


/* ==============================
   LABEL PAYMENT STATUS
============================== */
$paymentLabels = [
    'pending' => 'Chờ thanh toán',
    'success' => 'Thanh toán thành công',
    // 'failed'  => 'Thanh toán thất bại'
];

$paymentStatusLabel = $paymentLabels[$order['payment_status']] ?? 'Không xác định';


/* ==============================
   LẤY SẢN PHẨM TRONG ĐƠN
============================== */
$stmt = $conn->prepare("
    SELECT
        oi.quantity,
        oi.price,
        p.name,
        pi.image_url
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN product_images pi 
        ON pi.product_id = p.id AND pi.is_main = 1
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


/* ==============================
   ALLOWED NEXT ORDER STATUS
   (ADMIN CHỈ ĐỔI ORDER STATUS)
============================== */

$allowedNextStatus = [
    'pending'   => ['shipped', 'cancel'],
    'paid'      => ['shipped'],
    'shipped'   => ['completed'],
    'completed' => [],
    'cancel'    => []
    // 'failed'    => []
];

$statusClass = [
    'pending' => 'gray',
    'paid' => 'green',
    'shipped' => 'orange',
    'completed' => 'blue',
    'cancel' => 'red'
    // 'failed' => 'red'
];
/* ==============================
   AUTO SYNC: Nếu payment failed
   thì order phải cancel
============================== */
if ($order['payment_status'] === 'failed' && $order['status'] !== 'failed') {
    $update = $conn->prepare("UPDATE orders SET status = 'failed' WHERE id = ?");
    $update->bind_param("i", $orderId);
    $update->execute();
    $order['status'] = 'failed';
}
$currentPath = dirname($_SERVER['PHP_SELF']);

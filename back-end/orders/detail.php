<?php
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/front-end/includes/functions.php";

/* KIỂM TRA ĐĂNG NHẬP */
if (!isset($_SESSION['user_id'])) {
    header("Location: " . FRONT_URL . "/auth/login.php");
    exit;
}

$userId  = $_SESSION['user_id'];
$orderId = (int)($_GET['id'] ?? 0);

if ($orderId <= 0) {
    header("Location: " . FRONT_URL . "/orders/index.php");
    exit;
}

/* LẤY ĐƠN HÀNG (CHỈ CỦA USER) */
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

$orderLabels = [
    'pending'   => 'Chờ xử lý',
    'shipped'   => 'Đang giao hàng',
    'completed' => 'Hoàn thành',
    'cancel'    => 'Đã huỷ đơn'
];

$orderStatusLabel = $orderLabels[$order['status']] ?? 'Không xác định';
/* ==============================
   LABEL PAYMENT STATUS
============================== */
$paymentLabels = [
    'pending' => 'Chờ thanh toán',
    'success' => 'Thanh toán thành công',
    'cancel'  => 'Thanh toán thất bại'
];

$paymentStatusLabel = $paymentLabels[$order['payment_status']] ?? 'Thanh toán thất bại';

if (!$order) {
    header("Location: index.php");
    exit;
}

/* LẤY SẢN PHẨM TRONG ĐƠN */
$stmt = $conn->prepare("
    SELECT 
        oi.quantity,
        oi.price,
        p.name,
        p.slug,
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

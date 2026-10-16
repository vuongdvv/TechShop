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

$allowedNextStatus = [
    'pending'   => ['shipped', 'cancel'],
    'paid'      => ['shipped'],
    'shipped'   => ['completed'],
    'completed' => [],
    'cancel'    => []
];

if ($order['payment_status'] === 'cancel' && $order['status'] !== 'cancel') {
    $update = $conn->prepare("UPDATE orders SET status = 'cancel' WHERE id = ?");
    $update->bind_param("i", $orderId);
    $update->execute();
    $order['status'] = 'cancel';
}
$currentPath = dirname($_SERVER['PHP_SELF']);

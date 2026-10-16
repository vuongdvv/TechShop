<?php
require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 3) . "/front-end/includes/functions.php";

requireAdminAccess($conn);


$sql = "
    SELECT 
        o.id,
        o.customer_name,
        o.customer_phone,
        o.total_price,
        CASE
            WHEN o.status = 'pending' AND MAX(p.method) = 'vnpay' AND MAX(p.status) = 'cancel' THEN 'cancel'
            ELSE o.status
        END AS display_status,
        o.created_at,
        COUNT(oi.id) AS total_items
    FROM orders o
    LEFT JOIN payments p ON p.order_id = o.id
    LEFT JOIN order_items oi ON oi.order_id = o.id
    GROUP BY o.id, o.customer_name, o.customer_phone, o.total_price, o.status, o.created_at
    ORDER BY o.id DESC
";

$orders = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

$currentPath = dirname($_SERVER['PHP_SELF']);

$statusLabels = [
    'pending' => 'Chờ xử lý',
    'paid' => 'Đã thanh toán',
    'shipped' => 'Đang giao',
    'completed' => 'Hoàn thành',
    'cancel' => 'Đã hủy',

];

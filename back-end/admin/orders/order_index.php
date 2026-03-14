<?php
require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 3) . "/front-end/includes/functions.php";

requireAdminAccess($conn);

/* ===== GET ORDERS ===== */
$sql = "
    SELECT 
        o.id,
        o.customer_name,
        o.customer_phone,
        o.total_price,
        CASE
            WHEN o.status = 'pending' AND p.method = 'vnpay' AND p.status = 'failed' THEN 'failed'
            WHEN o.status = 'pending' AND p.method = 'vnpay' AND p.status = 'cancel' THEN 'cancel'
            ELSE o.status
        END AS display_status,
        o.created_at,
        COUNT(oi.id) AS total_items
    FROM orders o
    LEFT JOIN payments p ON p.order_id = o.id
    LEFT JOIN order_items oi ON oi.order_id = o.id
    GROUP BY o.id
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
    // 'failed' => 'Thanh toán thất bại'
];

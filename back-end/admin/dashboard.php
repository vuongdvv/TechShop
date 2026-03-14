<?php

require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/front-end/includes/functions.php";

/* ===== CHECK LOGIN & ADMIN PERMISSION ===== */
requireAdminAccess($conn);

/* ===== HELPER FUNCTION ===== */
function getSingleValue($conn, $sql)
{
    return $conn->query($sql)->fetch_row()[0] ?? 0;
}

/* ===== DASHBOARD STATS ===== */
$totalOrders = getSingleValue($conn, "
    SELECT COUNT(*) 
    FROM orders 
    WHERE status IN ('paid', 'completed')
");

$totalRevenue = getSingleValue($conn, "
    SELECT SUM(total_price) 
    FROM orders 
    WHERE status IN ('paid', 'completed')
");

$totalProducts = getSingleValue($conn, "SELECT COUNT(*) FROM products");

$totalUsers = getSingleValue($conn, "SELECT COUNT(*) FROM users");

/* ===== CURRENT PAGE ===== */
$currentPage = basename($_SERVER['PHP_SELF']);

/* ===== REVENUE 7 DAYS ===== */
$revenueByDay = [];

$sqlRevenueByDate = "
    SELECT SUM(total_price) 
    FROM orders 
    WHERE DATE(created_at) = '%s'
    AND status IN ('paid', 'completed')
";

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $query = sprintf($sqlRevenueByDate, $date);
    $revenueByDay[] = getSingleValue($conn, $query);
}

/* ===== TOP PRODUCTS ===== */
$topProductsSql = "
    SELECT 
        p.id,
        p.name,
        c.name AS category_name,
        MAX(CASE WHEN pi.is_main = 1 THEN pi.image_url END) AS image_url,
        COUNT(od.product_id) AS total_sold
    FROM order_items od
    JOIN orders o ON od.order_id = o.id
    JOIN products p ON od.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN product_images pi ON p.id = pi.product_id
    WHERE o.status IN ('paid', 'completed')
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 4
";

$topProducts = $conn->query($topProductsSql);

/* ===== RECENT ORDERS ===== */
$recentOrdersSql = "
    SELECT 
        o.id,
        u.full_name AS customer,
        (
            SELECT p.name
            FROM order_items od
            JOIN products p ON od.product_id = p.id
            WHERE od.order_id = o.id
            LIMIT 1
        ) AS product_name,
        o.total_price,
        CASE
            WHEN o.status = 'pending' AND p.method = 'vnpay' AND p.status = 'failed' THEN 'failed'
            WHEN o.status = 'pending' AND p.method = 'vnpay' AND p.status = 'cancel' THEN 'cancel'
            ELSE o.status
        END AS display_status
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN payments p ON p.order_id = o.id
    ORDER BY o.created_at DESC
    LIMIT 4
";

$recentOrders = $conn->query($recentOrdersSql);

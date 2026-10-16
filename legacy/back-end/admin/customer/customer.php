<?php

require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 3) . "/front-end/includes/functions.php";


requireAdminAccess($conn);

$currentPath = $_SERVER['REQUEST_URI'];


$totalCustomers = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

$newCustomers = $conn->query("
    SELECT COUNT(*) as total 
    FROM users 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
")->fetch_assoc()['total'];

$activeUsers = $conn->query("
    SELECT COUNT(*) as total 
    FROM users 
    WHERE status = '1'
")->fetch_assoc()['total'];


$sql = "
SELECT 
    u.id,
    u.full_name,
    u.email,
    u.phone,
    u.status,
    u.role,
    COUNT(o.id) as total_orders,
    COALESCE(SUM(o.total_price),0) as total_spent
FROM users u
LEFT JOIN orders o ON u.id = o.user_id
GROUP BY u.id
ORDER BY u.id DESC
LIMIT 10
";

$customers = $conn->query($sql);

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: customer.php?success=Xóa thành công");
        exit;
    } else {
        echo "Lỗi xóa!";
    }
}

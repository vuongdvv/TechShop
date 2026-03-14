<?php
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/front-end/includes/functions.php";

/* KIỂM TRA ĐĂNG NHẬP */
if (!isset($_SESSION['user_id'])) {
    header("Location: " . FRONT_URL . "/auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

/* LẤY DANH SÁCH ĐƠN HÀNG */
$stmt = $conn->prepare("
    SELECT 
        id,
        total_price,
        status,
        created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY id DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

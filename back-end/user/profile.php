<?php
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/front-end/includes/functions.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . FRONT_URL . "/auth/login.php");
    exit;
}
$userId = $_SESSION['user_id'];

$vStmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
$vStmt->bind_param("i", $userId);
$vStmt->execute();
$variant = $vStmt->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["full_name"]);
    $phone = trim($_POST["phone"]);
    $city = trim($_POST["city"]);
    $addressdetail = trim($_POST["address_detail"]);

    if (empty($name)) {
        $error = "Vui lòng nhập họ tên.";
    } elseif (empty($phone)) {
        $error = "Vui lòng nhập số điện thoại.";
    } elseif (empty($city)) {
        $error = "Vui lòng nhập tỉnh/thành phố.";
    } elseif (empty($addressdetail)) {
        $error = "Vui lòng nhập địa chỉ chi tiết.";
    } else {

        if ($variant) {
            $stmt = $conn->prepare("
                UPDATE addresses 
                SET full_name = ?, phone = ?, city = ?, address_detail = ?
                WHERE user_id = ?
            ");
            $stmt->bind_param("ssssi", $name, $phone, $city, $addressdetail, $userId);
        } else {
            $stmt = $conn->prepare("
                INSERT INTO addresses (user_id, full_name, phone, city, address_detail) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("issss", $userId, $name, $phone, $city, $addressdetail);
        }

        if ($stmt->execute()) {
            header("Location: " . FRONT_URL . "/user/profile.php?success=1");
            exit;
        } else {
            $error = "Có lỗi xảy ra. Vui lòng thử lại.";
        }
    }
}

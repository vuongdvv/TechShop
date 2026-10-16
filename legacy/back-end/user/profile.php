<?php
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/front-end/includes/functions.php";
require_once dirname(__DIR__) . "/api_client.php";

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

    if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
        $jwtCookie = defined('JWT_COOKIE_NAME') ? JWT_COOKIE_NAME : 'techshop_token';
        $token = $_COOKIE[$jwtCookie] ?? '';
        $cookieHeader = $token !== '' ? ($jwtCookie . '=' . $token) : '';

        $apiResult = callLaravelApi('PATCH', '/profile', [
            'full_name' => $name,
            'phone' => $phone,
            'city' => $city,
            'address_detail' => $addressdetail,
        ], $cookieHeader);

        if ($apiResult['ok']) {
            header("Location: " . FRONT_URL . "/user/profile.php?success=1");
            exit;
        }

        $error = $apiResult['json']['message'] ?? 'Có lỗi xảy ra. Vui lòng thử lại.';
    }

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

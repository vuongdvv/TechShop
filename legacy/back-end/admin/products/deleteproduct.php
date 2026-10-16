<?php
session_start();
require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 3) . "/front-end/includes/functions.php";
require_once dirname(__DIR__, 2) . "/api_client.php";

requireAdminAccess($conn);

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . FRONT_URL . "/admin/products/product.php");
    exit();
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "CSRF token không hợp lệ!";
    header("Location: " . FRONT_URL . "/admin/products/product.php");
    exit();
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $_SESSION['error'] = "ID không hợp lệ!";
    header("Location: " . FRONT_URL . "/admin/products/product.php");
    exit();
}

$product_id = (int)$_POST['id'];

if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
    $jwtCookie = defined('JWT_COOKIE_NAME') ? JWT_COOKIE_NAME : 'techshop_token';
    $token = $_COOKIE[$jwtCookie] ?? '';
    $cookieHeader = $token !== '' ? ($jwtCookie . '=' . $token) : '';
    $apiResult = callLaravelApi('DELETE', '/admin/products/' . $product_id, [], $cookieHeader);

    if ($apiResult['ok']) {
        $_SESSION['success'] = "Xóa sản phẩm thành công!";
    } else {
        $_SESSION['error'] = $apiResult['json']['message'] ?? "Không thể xóa sản phẩm!";
    }

    header("Location: " . FRONT_URL . "/admin/products/product.php");
    exit();
}

$imgStmt = $conn->prepare("
    SELECT image_url FROM product_images 
    WHERE product_id = ?
");
$imgStmt->bind_param("i", $product_id);
$imgStmt->execute();
$imgResult = $imgStmt->get_result();

$images = [];
while ($row = $imgResult->fetch_assoc()) {
    $images[] = $row['image_url'];
}


$deleteStmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$deleteStmt->bind_param("i", $product_id);
$deleteStmt->execute();

if ($deleteStmt->affected_rows > 0) {

    foreach ($images as $img) {
        $filePath = dirname(__DIR__, 3) . "/front-end/assets/images/products/" . $img;

        if (!empty($img) && file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $_SESSION['success'] = "Xóa sản phẩm thành công!";
} else {
    $_SESSION['error'] = "Không thể xóa sản phẩm!";
}

header("Location: " . FRONT_URL . "/admin/products/product.php");
exit();

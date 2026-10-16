<?php
session_start();

require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 3) . "/front-end/includes/functions.php";
require_once dirname(__DIR__, 2) . "/api_client.php";

requireAdminAccess($conn);


if (!isset($_GET['id']) || !isset($_GET['type'])) {
    header("Location: " . FRONT_URL . "/admin/categories/brand_category.php");
    exit;
}

$id = (int) $_GET['id'];
$type = $_GET['type'];

if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
    $jwtCookie = defined('JWT_COOKIE_NAME') ? JWT_COOKIE_NAME : 'techshop_token';
    $token = $_COOKIE[$jwtCookie] ?? '';
    $cookieHeader = $token !== '' ? ($jwtCookie . '=' . $token) : '';

    $apiResult = callLaravelApi('DELETE', '/admin/catalog', [
        'type' => $type,
        'id' => $id,
    ], $cookieHeader);

    $apiJson = $apiResult['json'] ?? [];
    if ((bool)($apiJson['success'] ?? $apiResult['ok'])) {
        $_SESSION['success'] = $apiJson['message'] ?? 'Thao tác thành công.';
    } else {
        $_SESSION['error'] = $apiJson['message'] ?? 'Thao tác thất bại.';
    }

    header("Location: " . FRONT_URL . "/admin/categories/brand_category.php");
    exit;
}

if ($type === "brand") {


    $check = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE brand_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();

    if ($result['total'] > 0) {
        $_SESSION['error'] = "Không thể xoá thương hiệu vì đang có sản phẩm.";
    } else {
        $stmt = $conn->prepare("DELETE FROM brands WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Xoá thương hiệu thành công.";
        } else {
            $_SESSION['error'] = "Xoá thất bại.";
        }
    }
} elseif ($type === "category") {

    $check = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();

    if ($result['total'] > 0) {
        $_SESSION['error'] = "Không thể xoá danh mục vì đang có sản phẩm.";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Xoá danh mục thành công.";
        } else {
            $_SESSION['error'] = "Xoá thất bại.";
        }
    }
}

header("Location: " . FRONT_URL . "/admin/categories/brand_category.php");
exit;

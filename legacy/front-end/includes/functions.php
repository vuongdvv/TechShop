<?php

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAjaxRequest()
{
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (!empty($_SERVER['HTTP_X_FETCH']))
        || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
        || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
}

function requireAdminAccess($conn)
{
    if (!isset($_SESSION['admin_id'])) {
        if (isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(["success" => false, "message" => "Chưa đăng nhập"]);
            exit;
        }
        header("Location: " . FRONT_URL . "/auth/login.php");
        exit;
    }

    $stmt = $conn->prepare("
        SELECT role, status 
        FROM users 
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || $user['role'] !== 'admin' || $user['status'] != 1) {
        session_unset();
        session_destroy();
        if (isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(["success" => false, "message" => "Không có quyền truy cập"]);
            exit;
        }
        header("Location: " . FRONT_URL . "/auth/login.php?error=unauthorized");
        exit;
    }
}


if (!function_exists('e')) {
    function e($str)
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

function getBrandBySlug($conn, $slug)
{
    $stmt = $conn->prepare("SELECT * FROM brands WHERE slug = ?");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getCategoryBySlug($conn, $slug)
{
    $stmt = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function cartCount($conn)
{
    if (!isset($_SESSION['user_id'])) return 0;
    $stmt = $conn->prepare("
        SELECT SUM(ci.quantity) AS total_qty
        FROM carts c
        JOIN cart_items ci ON c.id = ci.cart_id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)($row['total_qty'] ?? 0);
}

function formatPrice($price)
{
    return number_format($price, 0, ',', '.') . ' ₫';
}

<?php
session_start();

require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/front-end/includes/functions.php";
require_once dirname(__DIR__) . "/api_client.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . FRONT_URL . "/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . FRONT_URL . "/cart/index.php");
    exit;
}

$userId     = $_SESSION['user_id'];
$cartItemId = (int)($_POST['cart_item_id'] ?? 0);
$quantity   = max(1, (int)($_POST['quantity'] ?? 1));

if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
    $jwtCookie = defined('JWT_COOKIE_NAME') ? JWT_COOKIE_NAME : 'techshop_token';
    $token = $_COOKIE[$jwtCookie] ?? '';
    $cookieHeader = $token !== '' ? ($jwtCookie . '=' . $token) : '';

    $apiResult = callLaravelApi('PATCH', '/cart/items/' . $cartItemId, [
        'quantity' => $quantity,
    ], $cookieHeader);

    if (isAjaxRequest()) {
        header('Content-Type: application/json; charset=utf-8');
        if ($apiResult['json']) {
            echo json_encode($apiResult['json']);
        } else {
            echo json_encode([
                'success' => $apiResult['ok'],
                'message' => $apiResult['ok'] ? 'Cập nhật giỏ hàng thành công' : 'Không thể cập nhật giỏ hàng',
            ]);
        }
        exit;
    }

    header("Location: " . FRONT_URL . "/cart/index.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT ci.id
    FROM cart_items ci
    INNER JOIN carts c ON ci.cart_id = c.id
    WHERE ci.id = ? AND c.user_id = ?
");
$stmt->bind_param("ii", $cartItemId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: " . FRONT_URL . "/cart/index.php");
    exit;
}

$stmt = $conn->prepare("
    UPDATE cart_items
    SET quantity = ?
    WHERE id = ?
");
$stmt->bind_param("ii", $quantity, $cartItemId);
$stmt->execute();


header("Location: " . FRONT_URL . "/cart/index.php");
exit;

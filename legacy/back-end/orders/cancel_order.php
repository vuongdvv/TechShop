<?php
session_start();
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/front-end/includes/functions.php";
require_once dirname(__DIR__) . "/api_client.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Bạn chưa đăng nhập"
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$orderId = (int)($_POST['order_id'] ?? 0);

if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
    $jwtCookie = defined('JWT_COOKIE_NAME') ? JWT_COOKIE_NAME : 'techshop_token';
    $token = $_COOKIE[$jwtCookie] ?? '';
    $cookieHeader = $token !== '' ? ($jwtCookie . '=' . $token) : '';

    $apiResult = callLaravelApi('PATCH', '/orders/' . $orderId . '/cancel', [], $cookieHeader);
    if ($apiResult['json']) {
        echo json_encode($apiResult['json']);
    } else {
        echo json_encode([
            'success' => $apiResult['ok'],
            'message' => $apiResult['ok'] ? 'Hủy đơn thành công' : 'Lỗi khi hủy đơn',
        ]);
    }
    exit;
}

if ($orderId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Đơn hàng không hợp lệ"
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT status FROM orders 
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Không tìm thấy đơn hàng"
    ]);
    exit;
}


if (!in_array($result['status'], ['pending'])) {
    echo json_encode([
        "success" => false,
        "message" => "Không thể hủy đơn này"
    ]);
    exit;
}

$conn->begin_transaction();

try {

    $stmt = $conn->prepare("
        SELECT product_id, quantity
        FROM order_items
        WHERE order_id = ?
    ");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($items as $item) {
        $stmt = $conn->prepare("
            UPDATE products
            SET stock = stock + ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "ii",
            $item['quantity'],
            $item['product_id']
        );
        $stmt->execute();
    }

    $stmt = $conn->prepare("
        UPDATE orders 
        SET status = 'cancel' 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();

    $conn->commit();

    echo json_encode([
        "success" => true
    ]);
} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "success" => false,
        "message" => "Lỗi khi hủy đơn"
    ]);
}

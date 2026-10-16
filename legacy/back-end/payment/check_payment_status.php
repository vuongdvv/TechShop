<?php
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__) . "/api_client.php";


header('Content-Type: application/json');

$orderId = (int) ($_GET['order_id'] ?? 0);

if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
    $jwtCookie = defined('JWT_COOKIE_NAME') ? JWT_COOKIE_NAME : 'techshop_token';
    $token = $_COOKIE[$jwtCookie] ?? '';
    $cookieHeader = $token !== '' ? ($jwtCookie . '=' . $token) : '';

    $apiResult = callLaravelApi('GET', '/payments/' . $orderId . '/status', [], $cookieHeader);
    if ($apiResult['json']) {
        echo json_encode($apiResult['json']);
    } else {
        echo json_encode([
            'success' => $apiResult['ok'],
            'status' => $apiResult['ok'] ? 'pending' : 'error',
            'message' => $apiResult['ok'] ? null : 'Không thể kiểm tra thanh toán',
        ]);
    }
    exit;
}

if ($orderId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Order không hợp lệ'
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT p.status, o.status AS order_status
    FROM payments p
    JOIN orders o ON o.id = p.order_id
    WHERE p.order_id = ?
    AND p.method = 'vnpay'
    LIMIT 1
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Không tìm thấy payment'
    ]);
    exit;
}

if ($result['status'] === 'success' && $result['order_status'] === 'paid') {

    echo json_encode([
        'success' => true,
        'status'  => 'success',
        'redirect' => FRONT_URL . "/cart/success.php?order_id=" . $orderId
    ]);
    exit;
}

if ($result['status'] === 'cancel') {

    echo json_encode([
        'success' => true,
        'status'  => 'cancel',
        'message' => 'Thanh toán thất bại'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'status'  => 'pending'
]);

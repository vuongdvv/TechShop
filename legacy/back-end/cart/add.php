<?php
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/front-end/includes/functions.php";
require_once dirname(__DIR__) . "/api_client.php";


if (!isset($_SESSION['user_id'])) {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
        exit;
    }
    header("Location: " . FRONT_URL . "/auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: " . FRONT_URL . "/home.php");
    exit;
}

$productId = (int)$_GET['id'];
$buyNow    = isset($_GET['buy_now']);
$redirect  = $_GET['redirect'] ?? null;

if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
    $jwtCookie = defined('JWT_COOKIE_NAME') ? JWT_COOKIE_NAME : 'techshop_token';
    $token = $_COOKIE[$jwtCookie] ?? '';
    $cookieHeader = $token !== '' ? ($jwtCookie . '=' . $token) : '';

    $apiResult = callLaravelApi('POST', '/cart/items', [
        'product_id' => $productId,
        'quantity' => 1,
        'buy_now' => $buyNow,
    ], $cookieHeader);

    $apiJson = $apiResult['json'] ?? [];
    $apiData = $apiJson['data'] ?? [];

    if ($buyNow && !empty($apiData['cart_item_id'])) {
        $_SESSION['buy_now_item'] = (int)$apiData['cart_item_id'];
        header("Location: " . FRONT_URL . "/cart/checkout.php?mode=buy_now");
        exit;
    }

    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => (bool)($apiJson['success'] ?? $apiResult['ok']),
            'message' => $apiJson['message'] ?? ($apiResult['ok'] ? 'Đã thêm sản phẩm vào giỏ hàng' : 'Không thể thêm sản phẩm'),
            'cartCount' => (int)($apiData['cart_count'] ?? 0),
        ]);
        exit;
    }

    if ($apiResult['ok']) {
        $_SESSION['toast_success'] = '✔ Đã thêm sản phẩm vào giỏ hàng';
    }

    if ($redirect === 'detail' && !empty($_GET['slug'])) {
        header("Location: " . FRONT_URL . "/product/detail.php?slug=" . urlencode($_GET['slug']));
    } else {
        $referer = $_SERVER['HTTP_REFERER'] ?? FRONT_URL . "/product/list.php";
        header("Location: " . $referer);
    }
    exit;
}

$stmt = $conn->prepare("
    SELECT id, price, sale_price
    FROM products
    WHERE id = ? AND status = 1
    LIMIT 1
");

$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: " . FRONT_URL . "/home.php");
    exit;
}

$price = !empty($product['sale_price'])
    ? $product['sale_price']
    : $product['price'];

$stmt = $conn->prepare("
    SELECT id
    FROM carts
    WHERE user_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();

if (!$cart) {
    $stmt = $conn->prepare("
        INSERT INTO carts (user_id, created_at)
        VALUES (?, NOW())
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $cartId = $stmt->insert_id;
} else {
    $cartId = $cart['id'];
}

$stmt = $conn->prepare("
    SELECT id, quantity
    FROM cart_items
    WHERE cart_id = ?
      AND product_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $cartId, $productId);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if ($item) {

    $stmt = $conn->prepare("
        UPDATE cart_items
        SET quantity = quantity + 1
        WHERE id = ?
    ");
    $stmt->bind_param("i", $item['id']);
    $stmt->execute();

    $cartItemId = $item['id'];
} else {
    $stmt = $conn->prepare("
        INSERT INTO cart_items
            (cart_id, product_id, quantity, price)
        VALUES (?, ?, 1, ?)
    ");
    $stmt->bind_param("iid", $cartId, $productId, $price);
    $stmt->execute();
    $cartItemId = $stmt->insert_id;
}


if ($buyNow) {
    $_SESSION['buy_now_item'] = $cartItemId;
    header("Location: " . FRONT_URL . "/cart/checkout.php?mode=buy_now");
    exit;
}
if (isAjaxRequest()) {
    $cartTotal = cartCount($conn);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Đã thêm sản phẩm vào giỏ hàng', 'cartCount' => $cartTotal]);
    exit;
}

$_SESSION['toast_success'] = '✔ Đã thêm sản phẩm vào giỏ hàng';

if ($redirect === 'detail' && !empty($_GET['slug'])) {
    header("Location: " . FRONT_URL . "/product/detail.php?slug=" . urlencode($_GET['slug']));
} else {
    $referer = $_SERVER['HTTP_REFERER'] ?? FRONT_URL . "/product/list.php";
    header("Location: " . $referer);
}
exit;

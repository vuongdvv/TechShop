<?php
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/front-end/includes/functions.php";


/* =========================
   KIỂM TRA ĐĂNG NHẬP
========================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: " . FRONT_URL . "/auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

/* =========================
   LẤY CART ID
========================= */
$stmt = $conn->prepare("SELECT id FROM carts WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();

$cartItems = [];
$totalPrice = 0;

if ($cart) {
    $cartId = $cart['id'];

    /* =========================
       LẤY SẢN PHẨM TRONG GIỎ
    ========================= */
    $stmt = $conn->prepare("
        SELECT 
            ci.id AS cart_item_id,
            ci.quantity,
            ci.price,

            p.id AS product_id,
            p.name,
            p.slug,

            pi.image_url
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        LEFT JOIN product_images pi 
            ON pi.product_id = p.id AND pi.is_main = 1
        WHERE ci.cart_id = ?
        ORDER BY ci.id DESC
    ");
    $stmt->bind_param("i", $cartId);
    $stmt->execute();
    $cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($cartItems as $item) {
        $totalPrice += $item['price'] * $item['quantity'];
    }
}

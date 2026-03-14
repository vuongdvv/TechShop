<?php
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/front-end/includes/functions.php";


if (!isset($_SESSION['user_id'])) {
    header("Location: " . FRONT_URL . "/auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

$mode   = $_GET['mode'] ?? null;
$selectedItemIds = [];

if ($mode !== 'buy_now') {
    unset($_SESSION['checkout_selected_items']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: " . FRONT_URL . "/cart/index.php");
        exit;
    }

    if (isset($_POST['selected_items']) && is_array($_POST['selected_items'])) {
        $selectedItemIds = array_values(array_unique(array_filter(array_map('intval', $_POST['selected_items']))));
    }
}

$userAddress = null;

if ($userId) {
    $stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userAddress = $stmt->get_result()->fetch_assoc();
}

$stmt = $conn->prepare("SELECT id FROM carts WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();

if (!$cart) {
    header("Location: index.php");
    exit;
}


if ($mode === 'buy_now' && isset($_SESSION['buy_now_item'])) {


    $cartItemId = (int)$_SESSION['buy_now_item'];

    $stmt = $conn->prepare("
        SELECT 
            ci.id AS cart_item_id,
            ci.quantity,
            ci.price,
            p.name,
            p.slug,
            pi.image_url
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        LEFT JOIN product_images pi 
            ON pi.product_id = p.id AND pi.is_main = 1
        WHERE ci.id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $cartItemId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    unset($_SESSION['buy_now_item']);
    unset($_SESSION['checkout_selected_items']);
} else {

    if (empty($selectedItemIds)) {
        header("Location: " . FRONT_URL . "/cart/index.php");
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($selectedItemIds), '?'));
    $types = 'i' . str_repeat('i', count($selectedItemIds));
    $params = array_merge([$cart['id']], $selectedItemIds);

    $stmt = $conn->prepare("
        SELECT 
            ci.id AS cart_item_id,
            ci.quantity,
            ci.price,
            p.name,
            p.slug,
            pi.image_url
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        LEFT JOIN product_images pi 
            ON pi.product_id = p.id AND pi.is_main = 1
        WHERE ci.cart_id = ?
          AND ci.id IN ($placeholders)
    ");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


if (empty($items)) {
    header("Location: " . FRONT_URL . "/cart/index.php");
    exit;
}


$totalPrice = 0;
foreach ($items as $item) {
    $totalPrice += $item['price'] * $item['quantity'];
}

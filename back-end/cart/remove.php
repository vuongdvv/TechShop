<?php
session_start();

require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . FRONT_URL . "/auth/login.php");
    exit;
}

$userId     = $_SESSION['user_id'];
$cartItemId = (int)($_GET['id'] ?? 0);

if ($cartItemId <= 0) {
    header("Location: " . FRONT_URL . "/cart/index.php");
    exit;
}


$stmt = $conn->prepare("
    SELECT ci.id
    FROM cart_items ci
    JOIN carts c ON ci.cart_id = c.id
    WHERE ci.id = ? AND c.user_id = ?
");
$stmt->bind_param("ii", $cartItemId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: " . FRONT_URL . "/cart/index.php");
    exit;
}

/* XÓA ITEM */
$stmt = $conn->prepare("
    DELETE FROM cart_items 
    WHERE id = ?
");
$stmt->bind_param("i", $cartItemId);
$stmt->execute();

header("Location: " . FRONT_URL . "/cart/index.php");
exit;

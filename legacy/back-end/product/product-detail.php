<?php
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/front-end/includes/functions.php";

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: " . FRONT_URL . "/home.php");
    exit;
}

$category = isset($_GET['category']) ? trim($_GET['category']) : 0;

if (!empty($category)) {
    header("Location: " . FRONT_URL . "/home.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        p.*,
        b.name AS brand_name,
        c.name AS category_name,
        c.slug AS category_slug
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.slug = ? AND p.status = 1
    LIMIT 1
");

$stmt->bind_param("s", $slug);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: " . FRONT_URL . "/home.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT image_url, is_main
    FROM product_images
    WHERE product_id = ?
    ORDER BY is_main DESC, id ASC
");
$stmt->bind_param("i", $product['id']);
$stmt->execute();
$images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("
    SELECT *
    FROM product_variants
    WHERE product_id = ?
");
$stmt->bind_param("i", $product['id']);
$stmt->execute();
$variants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$productId = $product['id'];


$stmt = $conn->prepare("SELECT COUNT(*) AS total_reviews, COALESCE(AVG(rating), 0) AS avg_rating FROM reviews WHERE product_id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$reviewStats = $stmt->get_result()->fetch_assoc();

$ratingDistribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$stmt = $conn->prepare("SELECT rating, COUNT(*) AS cnt FROM reviews WHERE product_id = ? GROUP BY rating");
$stmt->bind_param("i", $productId);
$stmt->execute();
$distResult = $stmt->get_result();
while ($row = $distResult->fetch_assoc()) {
    $ratingDistribution[(int)$row['rating']] = (int)$row['cnt'];
}

$userHasReviewed = false;
if (isLoggedIn()) {
    $stmt = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $productId);
    $stmt->execute();
    $userHasReviewed = (bool)$stmt->get_result()->fetch_assoc();
}

$stmt = $conn->prepare("
    SELECT r.*, u.full_name AS user_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $productId);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$comments = [];
if (!empty($reviews)) {
    $reviewIds = array_column($reviews, 'id');
    $placeholders = implode(',', array_fill(0, count($reviewIds), '?'));
    $types = str_repeat('i', count($reviewIds));
    $stmt = $conn->prepare("
        SELECT c.*, u.full_name AS user_name
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.review_id IN ($placeholders)
        ORDER BY c.created_at ASC
    ");
    $stmt->bind_param($types, ...$reviewIds);
    $stmt->execute();
    $allComments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($allComments as $cmt) {
        $comments[(int)$cmt['review_id']][] = $cmt;
    }
}

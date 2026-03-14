<?php
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/front-end/includes/functions.php";




$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($_GET['slug'])) {
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

/* BIẾN THỂ */
$stmt = $conn->prepare("
    SELECT *
    FROM product_variants
    WHERE product_id = ?
");
$stmt->bind_param("i", $product['id']);
$stmt->execute();
$variants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

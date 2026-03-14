<?php
require_once dirname(__DIR__) . "/config/config.php";
require_once dirname(__DIR__) . "/config/database.php";
require_once dirname(__DIR__) . "/front-end/includes/functions.php";



$brandFilter = $_GET['brand'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

$sql = "
SELECT 
    p.id,
    p.name,
    p.slug,
    p.price,
    p.sale_price,
    p.rating,
    b.name AS brand_name,
    img.image_url,
    v.cpu,
    v.ram,
    v.ssd
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN brands b ON p.brand_id = b.id
LEFT JOIN product_images img 
    ON img.product_id = p.id AND img.is_main = 1
LEFT JOIN product_variants v ON v.product_id = p.id
WHERE p.stock > 0
";

$params = [];
$types = "";

if ($brandFilter !== '') {
    $sql .= " AND b.slug = ?";
    $params[] = $brandFilter;
    $types .= "s";
}
if ($categoryFilter !== '') {
    $sql .= " AND c.slug = ?";
    $params[] = $categoryFilter;
    $types .= "s";
}

if ($brandFilter !== '' || $categoryFilter !== '') {
    $sql .= " ORDER BY p.id DESC";
} else {
    $sql .= " ORDER BY p.price DESC LIMIT 4";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ===== BRANDS ===== */
$brandSql = "SELECT id, name, logo_image, slug FROM brands";
$brandResult = mysqli_query($conn, $brandSql);
$brands = mysqli_fetch_all($brandResult, MYSQLI_ASSOC);

// Lấy danh mục để hiển thị ở phần filter
$categorySql = "SELECT id, name, slug FROM categories";
$categoryResult = mysqli_query($conn, $categorySql);
$categories = mysqli_fetch_all($categoryResult, MYSQLI_ASSOC);

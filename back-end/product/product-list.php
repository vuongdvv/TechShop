<?php
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/front-end/includes/functions.php";
/* ===== PAGINATION ===== */
$limit = 8;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$params = [];

$types = "";
$where = "WHERE p.status = 1";
$category = null;
$invalidCategory = false;
$categorySlug = $_GET['category'] ?? null;
if ($categorySlug) {
    $category = getCategoryBySlug($conn, $categorySlug);

    if (!$category) {
        // Keep rendering the page, but force empty result for invalid category slug.
        $invalidCategory = true;
        $where .= " AND 1 = 0";
    } else {
        $where .= " AND p.category_id = ?";
        $params[] = $category['id'];
        $types .= "i";
    }
}
$keyword = $_GET['keyword'] ?? '';
if (!empty($keyword)) {
    $where .= " AND (
p.name LIKE ?
OR b.name LIKE ?
OR EXISTS (
SELECT 1 FROM product_variants pv2
WHERE pv2.product_id = p.id
AND pv2.cpu LIKE ?
)
)";
    $searchValue = "%" . $keyword . "%";
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $types .= "sss";
}

$brandSlug = $_GET['brand'] ?? '';
$brandFilter = null;

if (!empty($brandSlug)) {

    $brandData = getBrandBySlug($conn, $brandSlug);

    if ($brandData) {
        $brandFilter = $brandData['id'];

        $where .= " AND p.brand_id = ?";
        $params[] = $brandFilter;
        $types .= "i";
    } else {
        $where .= " AND 1 = 0";
    }
}

$sql = "
SELECT
p.id,
p.name,
p.slug,
p.price,
p.sale_price,
p.rating,
b.name AS brand_name,
c.name AS category_name,
MIN(pv.cpu) AS cpu,
MIN(pv.ram) AS ram,
MIN(pv.ssd) AS ssd,
MAX(pi.image_url) AS image_url
FROM products p
LEFT JOIN brands b ON p.brand_id = b.id
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN product_variants pv ON p.id = pv.product_id
LEFT JOIN product_images pi
ON p.id = pi.product_id AND pi.is_main = 1
$where
GROUP BY p.id
ORDER BY p.id DESC
LIMIT ? OFFSET ?
";

/* ===== LIMIT & OFFSET ===== */
$params[] = $limit;
$params[] = $offset;
$types .= "ii";


$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();

$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


$countParams = array_slice($params, 0, count($params) - 2);
$countTypes = substr($types, 0, strlen($types) - 2);

$countSql = "
SELECT COUNT(DISTINCT p.id) as total
FROM products p
LEFT JOIN brands b ON p.brand_id = b.id
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN product_variants pv ON p.id = pv.product_id
$where
";

$countStmt = $conn->prepare($countSql);

if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}

$countStmt->execute();
$countResult = $countStmt->get_result()->fetch_assoc();
$totalProducts = $countResult['total'] ?? 0;


$brands = [];
$brandSql = "SELECT id, name, logo_image, slug FROM brands ORDER BY name ASC";
$brandResult = $conn->query($brandSql);

if ($brandResult) {
    $brands = $brandResult->fetch_all(MYSQLI_ASSOC);
}

$allBrandQuery = $_GET;
unset($allBrandQuery['brand'], $allBrandQuery['page']);
$allBrandHref = '?' . http_build_query($allBrandQuery);
if ($allBrandHref === '?') {
    $allBrandHref = '?';
}

$categories = [];
$catSql = "SELECT id, name, slug FROM categories ORDER BY name ASC";
$catResult = $conn->query($catSql);
if ($catResult) {
    $categories = $catResult->fetch_all(MYSQLI_ASSOC);
}

$allCatQuery = $_GET;
unset($allCatQuery['category'], $allCatQuery['page']);
$allCatHref = '?' . http_build_query($allCatQuery);
if ($allCatHref === '?') {
    $allCatHref = '?';
}

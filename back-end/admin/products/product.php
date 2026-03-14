<?php
require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 3) . "/front-end/includes/functions.php";

requireAdminAccess($conn);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/* ===== FILTER ===== */

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$limit = 5;
$offset = ($page - 1) * $limit;

/* ===== STATS ===== */
$totalProduct = $conn->query("SELECT COUNT(*) as total FROM products")
    ->fetch_assoc()['total'];

$outOfStock = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock = 0")
    ->fetch_assoc()['total'];

$lowStockThreshold = 5;

$stmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM products
    WHERE stock > 0 AND stock <= ?
");
$stmt->bind_param("i", $lowStockThreshold);
$stmt->execute();
$lowStock = $stmt->get_result()->fetch_assoc()['total'];

$where = "WHERE p.name LIKE ?";
$params = ["%$keyword%"];
$types = "s";

// Lọc sắp hết hàng / hết hàng
if (isset($_GET['low_stock'])) {
    $where .= " AND p.stock > 0 AND p.stock <= ?";
    $params[] = $lowStockThreshold;
    $types .= "i";
} elseif (isset($_GET['out_stock'])) {
    $where .= " AND p.stock = 0";
}

/* ===== COUNT FILTERED ===== */
$countSql = "SELECT COUNT(*) as total FROM products p $where";
$countStmt = $conn->prepare($countSql);
$countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

$start = $totalRows > 0 ? $offset + 1 : 0;
$end = min($offset + $limit, $totalRows);

/* ===== PRODUCT LIST ===== */
$sql = "
SELECT p.*, b.name as brand_name, c.name as category_name, img.image_url
FROM products p
LEFT JOIN brands b ON p.brand_id = b.id
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN product_images img ON img.product_id = p.id AND img.is_main = 1
$where
ORDER BY p.created_at DESC
LIMIT $limit OFFSET $offset
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

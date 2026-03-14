<?php
session_start();
require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 3) . "/front-end/includes/functions.php";

header('Content-Type: application/json');

requireAdminAccess($conn);

function createSlug($string)
{
    $string = strtolower(trim($string));
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

$name = $_POST['name'] ?? '';
$slug = trim($_POST['slug'] ?? '');

if (!$name) {
    echo json_encode(["success" => false, "message" => "Thiếu tên"]);
    exit;
}

if ($slug === '') {
    $slug = createSlug($name);
}

$logoName = null;

// xử lý upload ảnh
if (!empty($_FILES['logo']['name'])) {
    $targetDir = dirname(__DIR__, 3) . "/front-end/assets/images/brands/";

    $logoName = time() . "_" . basename($_FILES['logo']['name']);
    $targetFile = $targetDir . $logoName;

    if (!move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {
        echo json_encode(["success" => false, "message" => "Upload lỗi"]);
        exit;
    }
}

// insert DB
$stmt = $conn->prepare("INSERT INTO brands (name, logo_image, slug) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $logoName, $slug);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}

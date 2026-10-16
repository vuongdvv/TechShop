<?php
session_start();
require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 3) . "/front-end/includes/functions.php";
require_once dirname(__DIR__, 2) . "/api_client.php";

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

if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
    $jwtCookie = defined('JWT_COOKIE_NAME') ? JWT_COOKIE_NAME : 'techshop_token';
    $token = $_COOKIE[$jwtCookie] ?? '';
    $cookieHeader = $token !== '' ? ($jwtCookie . '=' . $token) : '';

    $payload = [
        'name' => $name,
        'slug' => $slug,
    ];

    if (!empty($_FILES['logo']['tmp_name']) && is_uploaded_file($_FILES['logo']['tmp_name'])) {
        $payload['logo'] = new CURLFile(
            $_FILES['logo']['tmp_name'],
            $_FILES['logo']['type'] ?? 'application/octet-stream',
            $_FILES['logo']['name'] ?? 'logo'
        );
    }

    $apiResult = callLaravelApi('POST', '/admin/brands', $payload, $cookieHeader, true);
    if ($apiResult['json']) {
        echo json_encode($apiResult['json']);
    } else {
        echo json_encode(['success' => $apiResult['ok']]);
    }
    exit;
}

if (!$name) {
    echo json_encode(["success" => false, "message" => "Thiếu tên"]);
    exit;
}

if ($slug === '') {
    $slug = createSlug($name);
}

$logoName = null;

if (!empty($_FILES['logo']['name'])) {
    $targetDir = dirname(__DIR__, 3) . "/front-end/assets/images/brands/";

    $logoName = time() . "_" . basename($_FILES['logo']['name']);
    $targetFile = $targetDir . $logoName;

    if (!move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {
        echo json_encode(["success" => false, "message" => "Upload lỗi"]);
        exit;
    }
}

$stmt = $conn->prepare("INSERT INTO brands (name, logo_image, slug) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $logoName, $slug);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}

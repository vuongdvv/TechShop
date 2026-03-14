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

// ===== UPDATE BRAND =====
if (isset($_POST['type']) && $_POST['type'] === 'brand') {

    $id = $_POST['id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $slug = createSlug($name);

    if (!$id || !$name) {
        echo json_encode(["success" => false, "message" => "Missing data"]);
        exit;
    }

    $logoName = null;
    $logoUpdated = false;

    if (!empty($_FILES['logo']['name'])) {

        $targetDir = dirname(__DIR__, 3) . "/front-end/assets/images/brands/";

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $originalName = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_FILENAME));
        $originalName = preg_replace('/[^a-z0-9_-]/', '', $originalName);

        $logoName = $originalName . "_" . time() . "." . $ext;
        $targetFile = $targetDir . $logoName;

        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {
            echo json_encode([
                "success" => false,
                "message" => "Upload failed"
            ]);
            exit;
        }

        $stmt = $conn->prepare("UPDATE brands SET name = ?, slug = ?, logo_image = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $slug, $logoName, $id);
        $logoUpdated = true;
    } else {

        $stmt = $conn->prepare("UPDATE brands SET name = ?, slug = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $slug, $id);


        $stmtLogo = $conn->prepare("SELECT logo_image FROM brands WHERE id = ?");
        $stmtLogo->bind_param("i", $id);
        $stmtLogo->execute();
        $row = $stmtLogo->get_result()->fetch_assoc();
        $logoName = $row['logo_image'];
    }

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "logo" => $logoName,
            "logo_updated" => $logoUpdated
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => $stmt->error
        ]);
    }

    exit;
}

// ===== UPDATE CATEGORY =====
$id = $_POST['id'] ?? 0;
$name = trim($_POST['name'] ?? '');
$slug = trim($_POST['slug'] ?? '');

if (!$id || !$name) {
    echo json_encode([
        "success" => false,
        "message" => "Missing data"
    ]);
    exit;
}

$stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
$stmt->bind_param("ssi", $name, $slug, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode([
        "success" => false,
        "message" => $stmt->error
    ]);
}

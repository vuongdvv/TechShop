<?php

require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 3) . "/front-end/includes/functions.php";

requireAdminAccess($conn);
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
        || !empty($_SERVER['HTTP_X_FETCH']);

    if ($_POST['type'] === 'brand') {
        $name = trim($_POST['brand_name']);

        if ($name != "") {
            $slug = trim($_POST['slug'] ?? '');
            $stmt = $conn->prepare("INSERT INTO brands(name, slug) VALUES(?, ?)");
            $stmt->bind_param("ss", $name, $slug);
            $stmt->execute();
        }
    }

    if ($_POST['type'] === 'category') {
        $name = trim($_POST['category_name']);
        $slug = trim($_POST['slug']);

        if ($name != "") {
            $stmt = $conn->prepare("INSERT INTO categories(name, slug) VALUES(?, ?)");
            $stmt->bind_param("ss", $name, $slug);
            if ($stmt->execute()) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(["success" => true]);
                    exit;
                }
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(["success" => false, "message" => $stmt->error]);
                    exit;
                }
            }
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(["success" => false, "message" => "Tên danh mục trống"]);
                exit;
            }
        }
    }

    header("Location: " . FRONT_URL . "/admin/categories/brand_category.php");
    exit;
}


$totalBrands = $conn->query("SELECT COUNT(*) as total FROM brands")->fetch_assoc()['total'];
$totalCategories = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'];


$brands = $conn->query("SELECT * FROM brands ORDER BY id ASC");
$categories = $conn->query("SELECT * FROM categories ORDER BY id ASC");

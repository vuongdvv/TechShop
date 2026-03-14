<?php

require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 3) . "/front-end/includes/functions.php";

requireAdminAccess($conn);

$errors = [];
$currentPath = $_SERVER['PHP_SELF'];



function createSlug($string)
{
    $string = strtolower(trim($string));
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/* ================= LẤY BRAND ================= */
$brandResult = $conn->query("SELECT * FROM brands");

/* ================= LẤY CATEGORY ================= */
$categoryResult = $conn->query("SELECT * FROM categories");

/* ================= XỬ LÝ FORM ================= */



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // product
    $name = $_POST["name"];
    $slug = createSlug($name);
    $brand_id = $_POST["brand_id"];
    $category_id = $_POST["category_id"];
    $description = $_POST["description"];
    $price = $_POST["price"];
    $sale_price = $_POST["sale_price"];
    $stock = $_POST["stock"];
    $rating = $_POST["rating"];
    $status = $_POST["status"];

    // Variant
    $cpu = $_POST["cpu"];
    $ram = $_POST["ram"];
    $ssd = $_POST["ssd"];
    $gpu = $_POST["gpu"];
    $screen = $_POST["screen"];
    $pin = $_POST["pin"];
    $he_dieu_hanh = $_POST["he_dieu_hanh"];
    $kich_thuoc = $_POST["kich_thuoc"];

    if (empty($name)) {
        $errors[] = "Tên sản phẩm không được để trống";
    }

    if (empty($errors)) {

        // insert product
        $stmt = $conn->prepare("
            INSERT INTO products
            (name, slug, brand_id, category_id, description, price, sale_price, stock, rating, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?,?, ?, NOW())
        ");

        $stmt->bind_param(
            "ssiisddiid",
            $name,
            $slug,
            $brand_id,
            $category_id,
            $description,
            $price,
            $sale_price,
            $stock,
            $status,
            $rating
        );

        $stmt->execute();

        $product_id = $stmt->insert_id;

        // upload ảnh
        if (!empty($_FILES["image"]["name"])) {

            $extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $imageName = $slug . "-" . time() . "." . $extension;
            $target = dirname(__DIR__, 3) . "/front-end/assets/images/products/" . $imageName;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {

                $imgStmt = $conn->prepare("
            INSERT INTO product_images (product_id, image_url, is_main)
            VALUES (?, ?, 1)
        ");

                $imgStmt->bind_param("is", $product_id, $imageName);
                $imgStmt->execute();
            } else {
                die("Upload ảnh thất bại");
            }
        }

        // insert variant
        $variantStmt = $conn->prepare("
            INSERT INTO product_variants
            (product_id, cpu, ram, ssd, gpu, screen, pin, he_dieu_hanh, kich_thuoc)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $variantStmt->bind_param(
            "issssssss",
            $product_id,
            $cpu,
            $ram,
            $ssd,
            $gpu,
            $screen,
            $pin,
            $he_dieu_hanh,
            $kich_thuoc
        );

        $variantStmt->execute();

        header("Location: addproduct.php?success=1");
        exit();
    }
}

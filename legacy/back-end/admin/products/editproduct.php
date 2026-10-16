<?php
require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 3) . "/front-end/includes/functions.php";
require_once dirname(__DIR__, 2) . "/api_client.php";


requireAdminAccess($conn);

if (!isset($_GET['id'])) {
    header("Location: " . FRONT_URL . "/admin/products/product.php");
    exit();
}

$id = (int) $_GET['id'];
$errors = [];
$currentPath = $_SERVER['PHP_SELF'];

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: product.php");
    exit();
}


$vStmt = $conn->prepare("SELECT * FROM product_variants WHERE product_id = ?");
$vStmt->bind_param("i", $id);
$vStmt->execute();
$variant = $vStmt->get_result()->fetch_assoc();


$iStmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? AND is_main = 1");
$iStmt->bind_param("i", $id);
$iStmt->execute();
$image = $iStmt->get_result()->fetch_assoc();


$brands = $conn->query("SELECT * FROM brands");
$categories = $conn->query("SELECT * FROM categories");


function createSlug($string)
{
    $string = strtolower(trim($string));
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["name"];
    $slug = !empty($_POST["slug"]) ? $_POST["slug"] : createSlug($name);
    $brand_id = $_POST["brand_id"];
    $category_id = $_POST["category_id"];
    $description = $_POST["description"];
    $price = $_POST["price"];
    $sale_price = $_POST["sale_price"];
    $stock = $_POST["stock"];
    $status = $_POST["status"];

    $cpu = $_POST["cpu"];
    $ram = $_POST["ram"];
    $ssd = $_POST["ssd"];
    $gpu = $_POST["gpu"];
    $screen = $_POST["screen"];
    $pin = $_POST["pin"];
    $he_dieu_hanh = $_POST["he_dieu_hanh"];
    $kich_thuoc = $_POST["kich_thuoc"];

    if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
        $jwtCookie = defined('JWT_COOKIE_NAME') ? JWT_COOKIE_NAME : 'techshop_token';
        $token = $_COOKIE[$jwtCookie] ?? '';
        $cookieHeader = $token !== '' ? ($jwtCookie . '=' . $token) : '';

        $payload = [
            'name' => $name,
            'slug' => $slug,
            'brand_id' => $brand_id,
            'category_id' => $category_id,
            'description' => $description,
            'price' => $price,
            'sale_price' => $sale_price,
            'stock' => $stock,
            'status' => $status,
            'cpu' => $cpu,
            'ram' => $ram,
            'ssd' => $ssd,
            'gpu' => $gpu,
            'screen' => $screen,
            'pin' => $pin,
            'he_dieu_hanh' => $he_dieu_hanh,
            'kich_thuoc' => $kich_thuoc,
        ];

        $useMultipart = false;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0 && is_uploaded_file($_FILES['image']['tmp_name'])) {
            $payload['image'] = new CURLFile(
                $_FILES['image']['tmp_name'],
                $_FILES['image']['type'] ?? 'application/octet-stream',
                $_FILES['image']['name'] ?? 'image'
            );
            $useMultipart = true;
        }

        $apiResult = callLaravelApi('PATCH', '/admin/products/' . $id, $payload, $cookieHeader, $useMultipart);
        if ($apiResult['ok']) {
            header("Location: editproduct.php?id=" . $id . "&success=1");
            exit();
        }

        $errors[] = $apiResult['json']['message'] ?? 'Không thể cập nhật sản phẩm';
    }

    if (empty($name)) {
        $errors[] = "Tên sản phẩm không được để trống";
    }

    if (empty($errors)) {

        $update = $conn->prepare("
            UPDATE products 
            SET name=?, slug=?, brand_id=?, category_id=?, 
                description=?, price=?, sale_price=?, stock=?, status=?
            WHERE id=?
        ");

        $update->bind_param(
            "ssiisddiii",
            $name,
            $slug,
            $brand_id,
            $category_id,
            $description,
            $price,
            $sale_price,
            $stock,
            $status,
            $id
        );
        $update->execute();

        if ($variant) {


            $vUpdate = $conn->prepare("
        UPDATE product_variants
        SET cpu=?, ram=?, ssd=?, gpu=?, screen=?, 
            pin=?, he_dieu_hanh=?, kich_thuoc=?
        WHERE product_id=?
    ");

            $vUpdate->bind_param(
                "ssssssssi",
                $cpu,
                $ram,
                $ssd,
                $gpu,
                $screen,
                $pin,
                $he_dieu_hanh,
                $kich_thuoc,
                $id
            );

            $vUpdate->execute();
        } else {


            $vInsert = $conn->prepare("
        INSERT INTO product_variants
        (product_id, cpu, ram, ssd, gpu, screen, pin, he_dieu_hanh, kich_thuoc)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

            $vInsert->bind_param(
                "issssssss",
                $id,
                $cpu,
                $ram,
                $ssd,
                $gpu,
                $screen,
                $pin,
                $he_dieu_hanh,
                $kich_thuoc
            );

            $vInsert->execute();
        }
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {

            $uploadDir = dirname(__DIR__, 3) . "/front-end/assets/images/products/";
            $imageName = time() . "_" . basename($_FILES["image"]["name"]);
            $targetPath = $uploadDir . $imageName;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {


                if ($image) {

                    $imgUpdate = $conn->prepare("
                UPDATE product_images 
                SET image_url=? 
                WHERE product_id=? AND is_main=1
            ");
                    $imgUpdate->bind_param("si", $imageName, $id);
                    $imgUpdate->execute();
                } else {


                    $imgInsert = $conn->prepare("
                INSERT INTO product_images (product_id, image_url, is_main)
                VALUES (?, ?, 1)
            ");
                    $imgInsert->bind_param("is", $id, $imageName);
                    $imgInsert->execute();
                }
            }
        }

        header("Location: editproduct.php?id=" . $id . "&success=1");
        exit();
    }
}

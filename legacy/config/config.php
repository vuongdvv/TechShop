<?php

date_default_timezone_set('Asia/Ho_Chi_Minh');

if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === '127.0.0.1:8000') {
    define("BASE_URL", "");
} else {
    define("BASE_URL", "/TechShop");
}
define("FRONT_URL", BASE_URL);
define("SITE_NAME", "TechShop");
define("LARAVEL_API_ENABLED", true);
define("LARAVEL_API_BASE", "http://127.0.0.1:8000/api/v1");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// JWT Authentication - populate session from JWT cookie
require_once __DIR__ . "/jwt.php";

$jwtPayload = getJWTPayload();
if ($jwtPayload) {
    if ($jwtPayload['role'] === 'admin') {
        $_SESSION['admin_id']   = $jwtPayload['user_id'];
        $_SESSION['admin_name'] = $jwtPayload['user_name'];
    } else {
        $_SESSION['user_id']   = $jwtPayload['user_id'];
        $_SESSION['user_name'] = $jwtPayload['user_name'];
    }
} else {
    // JWT không hợp lệ hoặc hết hạn → xóa session auth
    unset($_SESSION['user_id'], $_SESSION['user_name']);
    unset($_SESSION['admin_id'], $_SESSION['admin_name']);
}

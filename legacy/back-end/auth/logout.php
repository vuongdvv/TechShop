<?php
session_start();
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__) . "/api_client.php";

if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
    $jwtCookie = defined('JWT_COOKIE_NAME') ? JWT_COOKIE_NAME : 'techshop_token';
    $token = $_COOKIE[$jwtCookie] ?? '';
    $cookieHeader = $token !== '' ? ($jwtCookie . '=' . $token) : '';
    callLaravelApi('POST', '/auth/logout', [], $cookieHeader);
}

clearJWTCookie();
session_unset();
session_destroy();

header("Location: " . FRONT_URL . "/home.php");
exit;

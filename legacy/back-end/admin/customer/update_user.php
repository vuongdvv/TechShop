<?php
session_start();
require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 3) . "/front-end/includes/functions.php";
require_once dirname(__DIR__, 2) . "/api_client.php";

header('Content-Type: application/json');

requireAdminAccess($conn);

$id = $_POST['id'] ?? 0;
$status = $_POST['status'] ?? 0;
$role = $_POST['role'] ?? 'user';

if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
    $jwtCookie = defined('JWT_COOKIE_NAME') ? JWT_COOKIE_NAME : 'techshop_token';
    $token = $_COOKIE[$jwtCookie] ?? '';
    $cookieHeader = $token !== '' ? ($jwtCookie . '=' . $token) : '';

    $apiResult = callLaravelApi('PATCH', '/admin/users/' . (int)$id, [
        'status' => (int)$status,
        'role' => $role,
    ], $cookieHeader);

    $apiJson = $apiResult['json'] ?? [];
    $logout = (bool)($apiJson['data']['logout'] ?? false);

    if ($logout) {
        session_unset();
        session_destroy();
    }

    echo json_encode([
        'success' => (bool)($apiJson['success'] ?? $apiResult['ok']),
        'logout' => $logout,
    ]);
    exit;
}

$stmt = $conn->prepare("UPDATE users SET status=?, role=? WHERE id=?");
$stmt->bind_param("isi", $status, $role, $id);

if ($stmt->execute()) {
    if ($id == $_SESSION['admin_id']) {
        if ($role !== 'admin' || $status == 0) {
            session_unset();
            session_destroy();
            echo json_encode(["success" => true, "logout" => true]);
            exit;
        }
    }
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}

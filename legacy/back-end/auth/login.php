<?php

require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__) . "/api_client.php";

$error = "";

// Thông báo từ admin redirect
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error = "Bạn không có quyền truy cập hoặc tài khoản đã bị thay đổi.";
}
if (isset($_GET['message']) && $_GET['message'] === 'role_changed') {
    $error = "Role của bạn đã bị thay đổi. Vui lòng đăng nhập lại.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = trim($_POST["email"]);
    $password = $_POST["password"];

    if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
        $apiResult = callLaravelApi('POST', '/auth/login', [
            'input' => $input,
            'password' => $password,
        ]);

        $apiJson = $apiResult['json'] ?? [];
        if ($apiResult['ok'] && !empty($apiJson['data']['token'])) {
            $user = $apiJson['data']['user'] ?? [];
            setJWTCookie((string)$apiJson['data']['token']);

            if (($user['role'] ?? 'user') === 'admin') {
                $_SESSION['admin_id'] = (int)($user['id'] ?? 0);
                $_SESSION['admin_name'] = (string)($user['name'] ?? '');
                header("Location: " . FRONT_URL . "/admin/dashboard.php");
                exit;
            }

            $_SESSION['user_id'] = (int)($user['id'] ?? 0);
            $_SESSION['user_name'] = (string)($user['name'] ?? '');
            header("Location: " . FRONT_URL . "/home.php");
            exit;
        }

        $error = $apiJson['message'] ?? 'Đăng nhập thất bại';
        return;
    }

    $user = null;

    $stmt = $conn->prepare(
        "SELECT id, full_name, password, status, role 
         FROM users 
         WHERE email = ? OR full_name = ?"
    );
    $stmt->bind_param("ss", $input, $input);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user["status"] == 0) {
        $error = "Tài khoản của bạn đã bị khóa";
        $user = null;
    }
    if (!$user) {
        if (empty($error)) {
            $error = "Tài khoản của bạn không tồn tại. Vui lòng đăng ký để tiếp tục";
        }
    } elseif (!password_verify($password, $user["password"])) {
        $error = "Mật khẩu không đúng";
    } else {
        // Tạo JWT token
        $token = generateJWT($user["id"], $user["full_name"], $user["role"]);
        setJWTCookie($token);

        if ($user["role"] === "admin") {
            $_SESSION["admin_id"]   = $user["id"];
            $_SESSION["admin_name"] = $user["full_name"];
            header("Location: " . FRONT_URL . "/admin/dashboard.php");
            exit;
        } else {
            $_SESSION["user_id"]   = $user["id"];
            $_SESSION["user_name"] = $user["full_name"];
            header("Location: " . FRONT_URL . "/home.php");
            exit;
        }
    }
}

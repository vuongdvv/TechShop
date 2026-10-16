<?php

require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__) . "/api_client.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"]);
    $email     = trim($_POST["email"]);
    $phone     = trim($_POST["phone"]);
    $password  = $_POST["password"];
    $confirm   = $_POST["confirm"];

    if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
        $apiResult = callLaravelApi('POST', '/auth/register', [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'confirm' => $confirm,
        ]);

        $apiJson = $apiResult['json'] ?? [];
        if ($apiResult['ok'] && !empty($apiJson['data']['token'])) {
            $user = $apiJson['data']['user'] ?? [];
            setJWTCookie((string)$apiJson['data']['token']);
            $_SESSION['user_id'] = (int)($user['id'] ?? 0);
            $_SESSION['user_name'] = (string)($user['name'] ?? '');
            header("Location: " . FRONT_URL . "/home.php");
            exit;
        }

        $error = $apiJson['message'] ?? 'Đăng ký thất bại';
        return;
    }

    if ($password !== $confirm) {
        $error = "Mật khẩu xác nhận không khớp";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE full_name = ?");
        $stmt->bind_param("s", $full_name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Tên người dùng đã tồn tại";
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Email đã được sử dụng";
            } else {
                $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
                $stmt->bind_param("s", $phone);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $error = "Số điện thoại đã được sử dụng";
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $conn->prepare(
                        "INSERT INTO users (full_name, email, password, phone, role, status)
                         VALUES (?, ?, ?, ?, 'user', 1)"
                    );
                    $stmt->bind_param("ssss", $full_name, $email, $hash, $phone);
                    if ($stmt->execute()) {
                        $newUserId = $stmt->insert_id;

                        // Tạo JWT token
                        $token = generateJWT($newUserId, $full_name, 'user');
                        setJWTCookie($token);

                        $_SESSION["user_id"]   = $newUserId;
                        $_SESSION["user_name"] = $full_name;

                        header("Location: " . FRONT_URL . "/home.php");
                        exit;
                    } else {
                        $error = "Có lỗi xảy ra, vui lòng thử lại";
                    }
                }
            }
        }
    }
}

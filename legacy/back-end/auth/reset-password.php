<?php

require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__) . "/api_client.php";

$error = "";
$success = "";
$validToken = false;
$token = $_GET["token"] ?? $_POST["token"] ?? "";

if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
    if (empty($token)) {
        $error = "Link đặt lại mật khẩu không hợp lệ.";
    } else {
        $check = callLaravelApi('GET', '/auth/password-resets/' . urlencode($token));
        $checkJson = $check['json'] ?? [];

        if (!$check['ok']) {
            $error = $checkJson['message'] ?? 'Link đặt lại mật khẩu không hợp lệ hoặc đã được sử dụng.';
        } else {
            $validToken = true;

            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $password = $_POST["password"] ?? "";
                $confirm = $_POST["confirm"] ?? "";

                $reset = callLaravelApi('POST', '/auth/reset-password', [
                    'token' => $token,
                    'password' => $password,
                    'confirm' => $confirm,
                ]);
                $resetJson = $reset['json'] ?? [];

                if ($reset['ok']) {
                    $success = $resetJson['message'] ?? 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập ngay.';
                    $validToken = false;
                } else {
                    $error = $resetJson['message'] ?? 'Có lỗi xảy ra. Vui lòng thử lại.';
                }
            }
        }
    }
    return;
}

if (empty($token)) {
    $error = "Link đặt lại mật khẩu không hợp lệ.";
} else {
    $stmt = $conn->prepare(
        "SELECT pr.email, pr.expires_at, u.id as user_id 
         FROM password_resets pr 
         JOIN users u ON u.email = pr.email 
         WHERE pr.token = ? AND pr.used = 0"
    );
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $reset = $result->fetch_assoc();

    if (!$reset) {
        $error = "Link đặt lại mật khẩu không hợp lệ hoặc đã được sử dụng.";
    } elseif (strtotime($reset['expires_at']) < time()) {
        $error = "Link đặt lại mật khẩu đã hết hạn. Vui lòng yêu cầu lại.";
    } else {
        $validToken = true;

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $password = $_POST["password"] ?? "";
            $confirm = $_POST["confirm"] ?? "";

            if (strlen($password) < 6) {
                $error = "Mật khẩu phải có ít nhất 6 ký tự.";
            } elseif ($password !== $confirm) {
                $error = "Mật khẩu xác nhận không khớp.";
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);

                // Update password
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hash, $reset['user_id']);
                $stmt->execute();

                // Mark token as used
                $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
                $stmt->bind_param("s", $token);
                $stmt->execute();

                $success = "Đặt lại mật khẩu thành công! Bạn có thể đăng nhập ngay.";
                $validToken = false;
            }
        }
    }
}

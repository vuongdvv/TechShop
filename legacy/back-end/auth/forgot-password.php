<?php

require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/vendor/autoload.php";
require_once dirname(__DIR__) . "/api_client.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");

    if (defined('LARAVEL_API_ENABLED') && LARAVEL_API_ENABLED) {
        $apiResult = callLaravelApi('POST', '/auth/forgot-password', [
            'email' => $email,
        ]);
        $apiJson = $apiResult['json'] ?? [];
        if ($apiResult['ok']) {
            $success = $apiJson['message'] ?? 'Nếu email tồn tại trong hệ thống, bạn sẽ nhận được link đặt lại mật khẩu.';
        } else {
            $error = $apiJson['message'] ?? 'Không thể gửi email. Vui lòng thử lại sau.';
        }
        return;
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Vui lòng nhập email hợp lệ";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE email = ? AND used = 0");
            $stmt->bind_param("s", $email);
            $stmt->execute();

            $token = bin2hex(random_bytes(32));
            $expiresAt = date("Y-m-d H:i:s", strtotime("+10 minutes"));

            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $token, $expiresAt);
            $stmt->execute();

            $resetLink = "http://localhost" . BASE_URL . "/auth/reset-password.php?token=" . $token;

            $mailConfig = require dirname(__DIR__, 2) . "/config/mail.php";
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = $mailConfig['host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $mailConfig['username'];
                $mail->Password   = $mailConfig['password'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $mailConfig['port'];
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
                $mail->addAddress($email, $user['full_name']);

                $mail->isHTML(true);
                $mail->Subject = 'Đặt lại mật khẩu - TechShop';
                $mail->Body = '
                <div style="font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #2563eb; text-align: center;">TechShop</h2>
                    <p>Xin chào <strong>' . htmlspecialchars($user['full_name']) . '</strong>,</p>
                    <p>Bạn đã yêu cầu đặt lại mật khẩu. Nhấn vào nút bên dưới để tiếp tục:</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . $resetLink . '" 
                           style="background: #2563eb; color: #fff; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                            Đặt lại mật khẩu
                        </a>
                    </div>
                    <p style="color: #6b7280; font-size: 13px;">Link có hiệu lực trong 30 phút.</p>
                    <p style="color: #6b7280; font-size: 13px;">Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này.</p>
                    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">
                    <p style="color: #9ca3af; font-size: 12px; text-align: center;">© TechShop</p>
                </div>';

                $mail->send();
                $success = "Đã gửi link đặt lại mật khẩu đến email của bạn. Vui lòng kiểm tra hộp thư.";
            } catch (Exception $e) {
                $error = "Không thể gửi email. Vui lòng thử lại sau.";
            }
        } else {
            $success = "Nếu email tồn tại trong hệ thống, bạn sẽ nhận được link đặt lại mật khẩu.";
        }
    }
}

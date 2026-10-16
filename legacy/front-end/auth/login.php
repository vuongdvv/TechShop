<?php
session_start();
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__) . "/includes/functions.php";
require_once dirname(__DIR__, 2) . "/back-end/auth/login.php";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - <?= SITE_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/login.css">
</head>

<body>
    <main>
        <div class="login-page">
            <div class="login-box">

                <h2>Đăng nhập</h2>
                <p class="login-desc">Chào mừng bạn quay lại <?= SITE_NAME ?></p>

                <?php if ($error): ?>
                    <div class="alert"><?= $error ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label>Email/Username </label>
                        <input type="text" name="email" required placeholder="user@gmail.com">
                    </div>

                    <div class="form-group">
                        <label>Mật khẩu</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" required placeholder="••••••••">
                            <i class="fa-regular fa-eye-slash toggle-password" onclick="togglePassword()"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        Đăng nhập
                    </button>
                </form>

                <div class="forgot-password" style="text-align: right; margin-top: 10px;">
                    <a href="<?= FRONT_URL ?>/auth/forgot-password.php" style="color: #2563eb; font-size: 13px; text-decoration: none;">Quên mật khẩu?</a>
                </div>

                <div class="login-footer">
                    <a href="<?= FRONT_URL ?>/home.php">← Về trang chủ</a>
                    <a href="<?= FRONT_URL ?>/auth/register.php" class="register-link">Đăng ký</a>
                </div>

            </div>
        </div>
    </main>
</body>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.querySelector('.toggle-password');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }
</script>

</html>
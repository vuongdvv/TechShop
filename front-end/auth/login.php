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

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/login.css">
</head>

<body>
    <main>
        <div class="login-page">
            <div class="login-box">

                <h2>Đăng nhập</h2>
                <p class="login-desc">Chào mừng bạn quay lại TechStore</p>

                <?php if ($error): ?>
                    <div class="alert"><?= $error ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label>Email/Username </label>
                        <input type="text" name="email" required placeholder="user@email.com">
                    </div>

                    <div class="form-group">
                        <label>Mật khẩu</label>
                        <input type="password" name="password" required placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn-login">
                        Đăng nhập
                    </button>
                </form>

                <div class="login-footer">
                    <a href="<?= FRONT_URL ?>/home.php">← Về trang chủ</a>
                    <a href="<?= FRONT_URL ?>/auth/register.php" class="register-link">Đăng ký</a>
                </div>

            </div>
        </div>
    </main>
</body>

</html>
<?php
session_start();
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__) . "/includes/functions.php";
require_once dirname(__DIR__, 2) . "/back-end/auth/forgot-password.php";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu - <?= SITE_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/login.css">
</head>

<body>
    <main>
        <div class="login-page">
            <div class="login-box">

                <h2>Quên mật khẩu</h2>
                <p class="login-desc">Nhập email để nhận link đặt lại mật khẩu</p>

                <?php if ($error): ?>
                    <div class="alert"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert" style="background: #dcfce7; color: #166534;"><?= $success ?></div>
                <?php else: ?>
                    <form method="POST" autocomplete="off">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" required placeholder="example@gmail.com">
                        </div>

                        <button type="submit" class="btn-login">
                            Gửi link đặt lại
                        </button>
                    </form>
                <?php endif; ?>

                <div class="login-footer">
                    <a href="<?= FRONT_URL ?>/auth/login.php">← Quay lại đăng nhập</a>
                </div>

            </div>
        </div>
    </main>
</body>

</html>
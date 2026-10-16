<?php
session_start();
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__) . "/includes/functions.php";
require_once dirname(__DIR__, 2) . "/back-end/auth/reset-password.php";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đặt lại mật khẩu - <?= SITE_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/login.css">
</head>

<body>
    <main>
        <div class="login-page">
            <div class="login-box">

                <h2>Đặt lại mật khẩu</h2>
                <p class="login-desc">Nhập mật khẩu mới cho tài khoản của bạn</p>

                <?php if ($error): ?>
                    <div class="alert"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert" style="background: #dcfce7; color: #166534;"><?= $success ?></div>
                    <div class="login-footer" style="margin-top: 18px;">
                        <a href="<?= FRONT_URL ?>/auth/login.php">→ Đăng nhập ngay</a>
                    </div>
                <?php elseif ($validToken): ?>
                    <form method="POST" autocomplete="off">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                        <div class="form-group">
                            <label>Mật khẩu mới</label>
                            <div class="password-wrapper">
                                <input type="password" name="password" id="password" required placeholder="••••••••" minlength="6">
                                <i class="fa-regular fa-eye-slash toggle-password" data-target="password"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Xác nhận mật khẩu</label>
                            <div class="password-wrapper">
                                <input type="password" name="confirm" id="confirm" required placeholder="••••••••" minlength="6">
                                <i class="fa-regular fa-eye-slash toggle-password" data-target="confirm"></i>
                            </div>
                        </div>

                        <button type="submit" class="btn-login">
                            Đặt lại mật khẩu
                        </button>
                    </form>
                <?php else: ?>
                    <div class="login-footer" style="margin-top: 18px;">
                        <a href="<?= FRONT_URL ?>/auth/forgot-password.php">Yêu cầu link mới</a>
                        <a href="<?= FRONT_URL ?>/auth/login.php">Đăng nhập</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>
</body>

<script>
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', function() {
            const input = document.getElementById(this.dataset.target);
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            }
        });
    });
</script>

</html>
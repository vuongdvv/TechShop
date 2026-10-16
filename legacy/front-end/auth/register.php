<?php
session_start();
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__) . "/includes/functions.php";
require_once dirname(__DIR__, 2) . "/back-end/auth/register.php";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng ký - <?= SITE_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/register.css">
</head>

<body>
    <main>
        <div class="register-page">
            <div class="register-box">

                <h2>Đăng ký</h2>
                <p class="register-desc">Tạo tài khoản mới tại <?= SITE_NAME ?></p>

                <?php if ($error): ?>
                    <div class="alert error"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" autocomplete="off">
                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text" name="full_name" required placeholder="Nguyễn Văn A">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required placeholder="example@gmail.com">
                    </div>

                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" required placeholder="0123456789">
                    </div>

                    <div class="form-group">
                        <label>Mật khẩu</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" required placeholder="••••••••">
                            <i class="fa-regular fa-eye-slash toggle-password" data-target="password"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Xác nhận mật khẩu</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm" id="confirm" required placeholder="••••••••">
                            <i class="fa-regular fa-eye-slash toggle-password" data-target="confirm"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-register">
                        Đăng ký
                    </button>
                </form>

                <div class="register-footer">
                    <p>Đã có tài khoản?</p>
                    <a href="<?= FRONT_URL ?>/auth/login.php">Đăng nhập</a>
                </div>

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
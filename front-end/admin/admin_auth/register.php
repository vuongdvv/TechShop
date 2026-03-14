<?php
session_start();
require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 3) . "/back-end/admin/admin_auth/register.php";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản Admin</title>
    <style>
        body {
            background: #f1f5f9;
            font-family: Arial, sans-serif;
        }

        .register-box {
            width: 360px;
            margin: 120px auto;
            background: #fff;
            padding: 28px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
        }

        .register-box h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 340px;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        .btn-login {
            width: 360px;
            padding: 10px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-login:hover {
            background: #1e40af;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 8px;
            border-radius: 6px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .register-footer {
            margin-top: 18px;
            display: flex;
            justify-content: center;
            font-size: 14px;
        }

        .register-footer p {
            margin: 0;
        }

        .register-footer a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="register-box">
        <h2><i class="fa fa-lock"></i> Đăng ký tài khoản Admin</h2>

        <?php if ($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>


        <form method="post">
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Xác nhận mật khẩu</label>
                <input type="password" name="confirm" required>
            </div>

            <button class="btn-login" type="submit">
                Đăng ký
            </button>

        </form>
        <div class="register-footer">
            <p>Đã có tài khoản?</p>
            <a href="<?= FRONT_URL ?>/admin/admin_auth/login.php">Đăng nhập</a>

        </div>
    </div>
</body>

</html>
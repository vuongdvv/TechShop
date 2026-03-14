<?php
session_start();

require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";
require_once dirname(__DIR__, 3) . "/back-end/admin/admin_auth/login.php";

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            background: #f1f5f9;
            font-family: Arial, sans-serif;
        }

        .login-box {
            width: 360px;
            margin: 120px auto;
            background: #fff;
            padding: 28px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
        }

        .login-box h2 {
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

        .login-footer {
            margin-top: 18px;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            text-decoration: none;
        }

        .login-footer a {
            color: #2563eb;
            text-decoration: none;
            margin: 0 auto;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="login-box">
        <h2><i class="fa fa-lock"></i> Admin Login</h2>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Tên đăng nhập / Email</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" required>
            </div>

            <button class="btn-login" type="submit">
                Đăng nhập
            </button>
        </form>
        <div class="login-footer">
            <a href="<?= FRONT_URL ?>/admin/admin_auth/register.php" class="register-link">Đăng ký tài khoản Admin</a>

        </div>
    </div>

</body>

</html>
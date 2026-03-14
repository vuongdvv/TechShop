<?php
session_start();
require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__) . "/includes/functions.php";
require_once dirname(__DIR__, 2) . "/back-end/user/profile.php";
require_once dirname(__DIR__) . "/includes/header.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= FRONT_URL ?>/assets/css/profile.css">
    <title>Cập nhật thông tin</title>
</head>

<body>

    <main>
        <div class="main-container">
            <h2>Thông tin cá nhân</h2>

            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label>Họ tên</label>
                    <input type="text" name="full_name"
                        value="<?= htmlspecialchars($variant['full_name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone"
                        value="<?= htmlspecialchars($variant['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Tỉnh/Thành phố</label>
                    <input type="text" name="city"
                        value="<?= htmlspecialchars($variant['city'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Địa chỉ chi tiết</label>
                    <textarea name="address_detail"><?= htmlspecialchars($variant['address_detail'] ?? '') ?></textarea>
                </div>

                <?php if (isset($error)): ?>
                    <p class="error"><?= $error ?></p>
                <?php endif; ?>

                <button type="submit" class="btn-submit">Cập nhật</button>

                <button type="button" class="back" onclick="window.location.href='<?= FRONT_URL ?>/home.php'"><span>Quay lại</span></button>
            </form>

            <?php if (isset($_GET['success'])): ?>
                <div id="toast" class="toast-success">
                    Cập nhật thông tin thành công!
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
<Script>
    setTimeout(function() {
        const toast = document.getElementById('toast');
        if (toast) {
            toast.remove();

        }
        if (window.location.search.includes('success')) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }

    }, 3000);
</Script>

</html>
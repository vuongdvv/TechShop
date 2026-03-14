<?php

require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";

$error = "";

// Kiểm tra các thông báo lỗi từ URL
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'unauthorized') {
        $error = "Bạn không còn là admin. Quyền của bạn đã bị thay đổi hoặc tài khoản đã bị khóa.";
    }
}

if (isset($_GET['message'])) {
    if ($_GET['message'] === 'role_changed') {
        $error = "Role của bạn đã bị thay đổi. Vui lòng đăng nhập lại với tài khoản admin.";
    }
}


if (isset($_SESSION['admin_id'])) {
    header("Location: ../dashboard.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Vui lòng nhập đầy đủ thông tin";
    } else {
        // Kiểm tra trong bảng admins
        $stmt = $conn->prepare("
            SELECT id, username, password, name, 'admin_table' as source
            FROM admins
            WHERE username = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        // Nếu không tìm thấy trong admins, kiểm tra bảng users với role=admin
        if (!$admin) {
            $stmt = $conn->prepare("
                SELECT id, email as username, password, full_name as name, 'users_table' as source
                FROM users
                WHERE (email = ? OR full_name = ?)
                AND role = 'admin'
                AND status = 1
                LIMIT 1
            ");
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $admin = $stmt->get_result()->fetch_assoc();
        }

        if (!$admin || !password_verify($password, $admin['password'])) {
            $error = "Tài khoản hoặc mật khẩu không đúng";
        } else {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_source'] = $admin['source']; // Đánh dấu nguồn để dùng khi đăng xuất
            header("Location: ../dashboard.php");
            exit;
        }
    }
}

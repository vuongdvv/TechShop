<?php

require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"]);
    $email     = trim($_POST["email"]);
    $phone     = trim($_POST["phone"]);
    $password  = $_POST["password"];
    $confirm   = $_POST["confirm"];

    // 1 Kiểm tra xác nhận mật khẩu
    if ($password !== $confirm) {
        $error = "Mật khẩu xác nhận không khớp";
    }
    //  Kiểm tra full_name trùng
    else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE full_name = ?");
        $stmt->bind_param("s", $full_name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Tên người dùng đã tồn tại";
        }
        //  Kiểm tra email trùng
        else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Email đã được sử dụng";
            }
            //  Kiểm tra phone trùng
            else {
                $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
                $stmt->bind_param("s", $phone);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $error = "Số điện thoại đã được sử dụng";
                }
                //  Thêm user mới
                else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);

                    $stmt = $conn->prepare(
                        "INSERT INTO users (full_name, email, password, phone, role, status)
                         VALUES (?, ?, ?, ?, 'user', 1)"
                    );
                    $stmt->bind_param("ssss", $full_name, $email, $hash, $phone);

                    if ($stmt->execute()) {
                        //  Tự động đăng nhập
                        $_SESSION["user_id"]   = $stmt->insert_id;
                        $_SESSION["user_name"] = $full_name;

                        //  Chuyển về trang chủ
                        header("Location: " . FRONT_URL . "/home.php");
                        exit;
                    } else {
                        $error = "Có lỗi xảy ra, vui lòng thử lại";
                    }
                }
            }
        }
    }
}

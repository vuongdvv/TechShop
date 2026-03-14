<?php
require_once dirname(__DIR__, 3) . "/config/config.php";
require_once dirname(__DIR__, 3) . "/config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password  = $_POST["password"];
    $confirm   = $_POST["confirm"];
    $name      = trim($_POST["name"]);

    if (empty($username) || empty($password) || empty($confirm) || empty($name)) {
        $error = "Vui lòng nhập đầy đủ thông tin";
    }
    // Sau đó mới kiểm tra mật khẩu khớp
    elseif ($password !== $confirm) {
        $error = "Mật khẩu xác nhận không khớp";
    } else {
        $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Tên người dùng đã tồn tại";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $conn->prepare(
                "INSERT INTO admins (username, password, name)
                         VALUES (?, ?, ?)"
            );
            $stmt->bind_param("sss", $username, $hash, $name);

            if ($stmt->execute()) {
                //Tự động đăng nhập
                $_SESSION['admin_id']   = $stmt->insert_id;
                $_SESSION['admin_name'] = $name;

                //Chuyển về trang chủ
                header("Location: ../dashboard.php");
                exit;
            } else {
                $error = "Có lỗi xảy ra, vui lòng thử lại";
            }
        }
    }
}

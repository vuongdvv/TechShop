<?php

require_once dirname(__DIR__, 2) . "/config/config.php";
require_once dirname(__DIR__, 2) . "/config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = trim($_POST["email"]);
    $password = $_POST["password"];

    $user = null;

    // Kiểm tra input có chứa '@' hay không
    if (strpos($input, '@') !== false) {
        // Input là email → kiểm tra bảng users
        $stmt = $conn->prepare(
            "SELECT id, full_name, password, status 
             FROM users 
             WHERE email = ?"
        );
        $stmt->bind_param("s", $input);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Kiểm tra user bị khóa
        if ($user && $user["status"] == 0) {
            $error = "Tài khoản của bạn đã bị khóa";
            $user = null;
        }
    } else {
        // Input là username → kiểm tra bảng admins
        $stmt = $conn->prepare(
            "SELECT id, name as full_name, password 
             FROM admins 
             WHERE username = ?"
        );
        $stmt->bind_param("s", $input);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    }

    // Không tồn tại 
    if (!$user) {
        if (empty($error)) {
            $error = "Tài khoản của bạn không tồn tại. Vui lòng đăng ký để tiếp tục";
        }
    }
    // Sai mật khẩu
    elseif (!password_verify($password, $user["password"])) {
        $error = "Mật khẩu không đúng";
    }
    // Thành công
    else {
        $_SESSION["user_id"]   = $user["id"];
        $_SESSION["user_name"] = $user["full_name"];

        header("Location: " . FRONT_URL . "/home.php");
        exit;
    }
}

<?php
session_start();
require_once dirname(__DIR__, 3) . "/config/config.php";

session_unset();
session_destroy();

header("Location: " . FRONT_URL . "/admin/admin_auth/login.php");
exit();

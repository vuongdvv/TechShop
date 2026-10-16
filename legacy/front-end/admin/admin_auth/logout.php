<?php
session_start();
require_once dirname(__DIR__, 3) . "/config/config.php";

clearJWTCookie();
session_unset();
session_destroy();

header("Location: " . FRONT_URL . "/auth/login.php");
exit();

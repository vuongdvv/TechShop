<?php
session_start();
require_once dirname(__DIR__, 2) . "/config/config.php";

clearJWTCookie();
session_unset();
session_destroy();

header("Location: " . FRONT_URL . "/home.php");
exit;

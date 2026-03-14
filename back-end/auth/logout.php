<?php
session_start();
session_unset();
session_destroy();

require_once dirname(__DIR__, 2) . "/config/config.php";
header("Location: " . FRONT_URL . "/home.php");
exit;

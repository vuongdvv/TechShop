<?php

define("BASE_URL", "/lapshop");
define("FRONT_URL", BASE_URL);
define("SITE_NAME", "TechShop");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

<?php

require("User.class.php");
require("db.php");

session_name("mappr");
session_set_cookie_params(86400 * 365, "/mappr/", "", true, true);
@session_start();

$isLoggedIn = false;
if (isset($_SESSION["user"]) && $_SESSION["user"] instanceof User) {
	$isLoggedIn = true;
}


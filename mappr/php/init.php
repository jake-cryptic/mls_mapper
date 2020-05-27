<?php

require("User.class.php");
require("db.php");
require("functions.php");

DEFINE("API_LIMIT",15000);
DEFINE("DEBUG",!empty($_GET["debug"]));

session_name("mappr");
session_set_cookie_params(86400 * 365, null, null, false, true);
@session_start();

$isLoggedIn = false;
if (isset($_SESSION["user"]) && $_SESSION["user"] instanceof User) {
	$isLoggedIn = true;
}

if (!isset($_SESSION["token"])) {
	$_SESSION["token"] = get_random_str(16);
}

$fv = time();

$allowed_pages = array(
	"map",
	"logout"
);
$allowed_apis = array(
	"account-handler",
	"get-mnc",
	"get-pins",
	"lookup-node"
);
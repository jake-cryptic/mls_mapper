<?php

require("init.php");

$api_request = false;
if (!empty($_GET["api"])) {
	if ($_GET["api"] === "true") {
		$api_request = true;
	}
} else {
	http_response_code(204);
	die();
}

$requested_page = "map";
if (!empty($_GET["requested"])) {
	$requested_page = trim($_GET["requested"]);
}

if (!in_array($requested_page, $api_request ? $allowed_apis : $allowed_pages)) {
	http_response_code(200);
	die();
}

if ($api_request) {
	$api_auth = $isLoggedIn;
	if ($requested_page === "account-handler") {
		$api_auth = true;
	}

	require("api/{$requested_page}.php");
} else {
	if (!$isLoggedIn) {
		$requested_page = "account";
	}

	$main_file = "{$requested_page}.php";
	require("views/template.php");
}
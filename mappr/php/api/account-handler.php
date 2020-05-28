<?php

if (!isset($api_auth) || !$api_auth) die();

function check_form_csrf($unsafe) {
	//print_r($_SESSION["token"] . " " . $unsafe . " ");
	if ($unsafe !== $_SESSION["token"]) {
		http_response_code(403);
		die($_SESSION["token"]);
	}
}

$valid_formtypes = array("login", "create");
if (empty($_POST["form_type"]) || !in_array($_POST["form_type"], $valid_formtypes)) {
	http_response_code(403);
	output();
}

if (empty($_POST["email"]) || empty($_POST["password"]) || empty($_POST["csrf"])) {
	http_response_code(403);
	output();
}

$email = $_POST["email"];
$pass = $_POST["password"];

if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
	$output["message"] = "Email address not valid";
	output();
}
if (strlen($pass) < 7) {
	$output["message"] = "Password must be 6 or more characters";
	output();
}

check_form_csrf($_POST["csrf"]);

$email = clean($email);
if ($_POST["form_type"] === "create") {
	if (empty($_POST["name"])) {
		http_response_code(403);
		output();
	}
	$name = clean($_POST["name"]);
	if (strlen($name) > 255 && ctype_alnum($name)) {
		$output["message"] = "Name failed validation";
		output();
	}

	$r = $db_connection->query("SELECT user_id FROM users WHERE email = '{$email}' LIMIT 1");
	if (!$r){
		output();
	}

	if($r->num_rows !== 0) {
		$output["message"] = "Email exists.";
		output();
	}

	$pass_hash = password_hash($pass, PASSWORD_ARGON2ID);
	$r = $db_connection->query("INSERT INTO " . DB_USERS . " (name, email, user_level, password_hash) VALUES ('{$name}', '{$email}', 1, '{$pass_hash}')");
	if (!$r) {
		$output["message"] = "Account creation has failed";
		output();
	}
}

$r = $db_connection->query("SELECT user_id, name, password_hash, user_level, time_created FROM " . DB_USERS . " WHERE email = '{$email}' LIMIT 1");
if (!$r || $r->num_rows !== 1){
	$output["message"] = "Failed email lookup";
	output();
}

$data = $r->fetch_object();

if ($data->user_level === 0) {
	$output["message"] = "Account not active";
	output();
}

if (!password_verify($pass, $data->password_hash)) {
	$output["message"] = "Password invalid";
	output();
}

$_SESSION["user"] = new User($data->user_id, $data->user_level, $data->name, $email, array());

$output["error"] = false;
$output["message"] = "Logged in";
output();
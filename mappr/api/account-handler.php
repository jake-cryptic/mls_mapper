<?php

$valid_formtypes = array("login", "create");
if (empty($_POST["form_type"]) || !in_array($_POST["form_type"], $valid_formtypes)) {
	http_response_code(403);
	die();
}

if (empty($_POST["email"]) || empty($_POST["password"]) || empty($_POST["csrf"])) {
	http_response_code(403);
	die();
}

$email = $_POST["email"];
$pass = $_POST["password"];

if (filter_var($email,FILTER_VALIDATE_EMAIL)) {
	die("Invalid email");
}
if (strlen($pass) < 7) {
	die("Password must be 6 or more characters");
}

require("init.php");

check_form_csrf($_POST["csrf"]);

$email = clean($email);
if ($_POST["form_type"] === "create") {
	print("Creating account.");
	$r = $db_connection->query("SELECT user_id FROM users WHERE email = '{$email}' LIMIT 1");
	if (!$r){
		die("Couldn't check email");
	}

	if($r->num_rows !== 0) {
		die("Email exist.");
	}

	$pass_hash = password_hash($pass, PASSWORD_ARGON2ID);
	$r = $db_connection->query("INSERT INTO users (email, passsword_hash) VALUES ('{$email}', '{$pass_hash}')");
	if (!$r) {
		die("Failed account make");
	}
}

print("Logging in.");
$r = $db_connection->query("SELECT user_id, password_hash, active, time_created FROM users WHERE email = '{$email}' LIMIT 1");
if (!$r){
	die();
}
print_r($r->fetch_object());
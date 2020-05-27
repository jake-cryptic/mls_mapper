<?php

if (!isset($api_auth) || !$api_auth) die();

$output = array(
	"error"=>true,
	"message"=>"Unknown",
	"bookmarks"=>array()
);

$valid_actions = array("get", "add", "remove");
if (empty($_POST["action"]) || !in_array($_POST["action"], $valid_actions)) {
	http_response_code(403);
	output();
}

function get_bookmarks() {
	global $db_connection;

	$r = $db_connection->query("SELECT ");
}
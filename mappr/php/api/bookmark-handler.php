<?php

if (empty($api_auth) || $api_auth === false) die();

$output["bookmarks"] = array();

$valid_actions = array("get", "add", "remove");
if (empty($_POST["action"]) || !in_array($_POST["action"], $valid_actions)) {
	http_response_code(403);
	output();
}

function get_bookmarks() {
	global $db_connection, $output;

	$user_id = $_SESSION["user"]->getId();
	$r = $db_connection->query("SELECT bookmark_id, name, description, lat, lng FROM " . DB_BOOKMARKS . " WHERE user_id = {$user_id} LIMIT 500");
	if (!$r || $r->num_rows === 0) {
		$output["message"] = "Cannot get bookmarks";
		output();
	}

	while ($d = $r->fetch_object()) {
		$output["bookmarks"][$d->bookmark_id] = array(
			"name"=>$d->name,
			"desc"=>$d->description,
			"lat"=>$d->lat,
			"lng"=>$d->lng
		);
	}

	$output["error"] = false;
	output();
}
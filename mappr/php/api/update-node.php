<?php

if (empty($api_auth) || $api_auth === false) die();

if (empty($_POST["lat"]) || empty($_POST["lng"]) || empty($_POST["mcc"]) || empty($_POST["mnc"]) || empty($_POST["enb"])) die();

$mcc = intval($_POST["mcc"]);
$mnc = intval($_POST["mnc"]);
$enb = intval($_POST["enb"]);
$lat = floatval($_POST["lat"]);
$lng = floatval($_POST["lng"]);
$time = time();
$uid = $_SESSION["user"]->getId();

$q = "INSERT INTO " . DB_LOCATIONS . " (mcc, mnc, node_id, updated, lat, lng, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $db_connection->prepare($q);
$stmt->bind_param("iiiiddi", $mcc, $mnc, $enb, $time, $lat, $lng, $uid);
$r = $stmt->execute();

if (!$r) {
	$output["dbe"] = $stmt->error;
	$output["message"] = "Database update failed";
	output();
}

$output["new_coords"] = array($lat, $lng);
$output["error"] = false;
$output["message"] = "eNB {$enb} was updated";
output();
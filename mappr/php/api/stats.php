<?php

if (empty($api_auth) || $api_auth === false) die();

set_time_limit(120);

if (!empty($_GET["mnc"]) && !empty($_GET["carrier"])) {
	$start = time() - (86400 * 365 * 7);
	$end = time();
	$int = 86400 * 7;
	$cumulative = false;
	$useupdated = false;

	if (!empty($_GET["start"]) && is_numeric($start)) $start = (int) $_GET["start"];
	if (!empty($_GET["end"]) && is_numeric($start)) $end = (int) $_GET["end"];
	if (!empty($_GET["int"]) && is_numeric($start)) $int = (int) $_GET["int"];

	if (!empty($_GET["cumulative"])) $cumulative = true;
	if (!empty($_GET["useupdated"])) $useupdated = true;

	get_carrier_trend(clean($_GET["mnc"]), $_GET["carrier"], $start, $end, $int, $cumulative, $useupdated);
}

function ret($out) {
	die(json_encode($out,JSON_PRETTY_PRINT));
}

function get_carrier_trend($mnc, $carrier, $time_start, $time_end, $time_interval, $showCumulative, $useUpdated) {
	global $db_connection;

	$searchField = $useUpdated ? "updated" : "created";

	$out = array(
		"min"=>time(),
		"max"=>time(),
		"results"=>array()
	);

	$getBounds = "SELECT MIN({$searchField}) as min, MAX({$searchField}) as max FROM " . DB_SECTORS . " WHERE 
		mnc = {$mnc} AND " . NETWORK_QUERIES[$mnc][$carrier];
	$r = $db_connection->query($getBounds);
	if (!$r) ret($out);
	$resultSet = $r->fetch_object();
	$out["min"] = $resultSet->min;
	$out["max"] = $resultSet->max;
	if ($out["min"] === null || $out["max"] === null) ret($out);

	$time_start = $out["min"];

	$getDataQuery = "SELECT COUNT(DISTINCT(enodeb_id)) AS total FROM " . DB_SECTORS . " WHERE 
		mnc = {$mnc} AND {$searchField} > ? AND {$searchField} < ? AND " . NETWORK_QUERIES[$mnc][$carrier];
	$get_sectors = $db_connection->prepare($getDataQuery);
	$get_sectors->bind_param("ii",$thisMin, $thisMax);

	$returnData = array();
	$count = 0;

	for ($i = $time_start; $i < $time_end; $i += $time_interval) {
		if ($showCumulative) {
			$thisMin = $time_start;
			$thisMax = $i;
		} else {
			$thisMin = $i;
			$thisMax = $i + $time_interval;
		}

		if (!$get_sectors->execute()){
			printf("Database Error: %s\n",$get_sectors->error);
			die();
		}

		$get_sectors->store_result();
		$get_sectors->bind_result($count);
		$get_sectors->fetch();

		$returnData[$i] = $count;
	}

	$out["results"] = $returnData;

	ret($out);
}

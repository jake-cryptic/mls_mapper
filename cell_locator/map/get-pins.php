<?php

// Database logins
DEFINE("DB_USERNAME","root");
DEFINE("DB_DATABASE","lte_database");
DEFINE("DB_PASSWORD","");
DEFINE("DB_HOSTNAME","localhost");

$db_connection = @new mysqli(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD,DB_DATABASE,3306);

// Check connection
if ($db_connection->connect_error) {
	unset($db_connection);
	die("Database connection error.");
}

$conditions  = "mnc = " . $_GET["mno"];
$conditions .= " AND lat > " . $_GET["swlat"];
$conditions .= " AND lng < " . $_GET["nelng"];
$conditions .= " AND lat < " . $_GET["nelat"];
$conditions .= " AND lng > " . $_GET["swlng"];

$get_enodebs = $db_connection->query("SELECT enodeb_id,lat,lng FROM masts2 WHERE " . $conditions . " LIMIT 1000");

$get_sectors = $db_connection->prepare("SELECT sector_id,created,updated,lat,lng FROM sectors WHERE mnc = {$_GET['mno']} AND enodeb_id = ? LIMIT 50");
$get_sectors->bind_param("i",$thisEnb);

$returnData = array();
while ($node = $get_enodebs->fetch_object()){
	$thisEnb = $node->enodeb_id;
	
	if (!$get_sectors->execute()){
		printf("Database Error: %s\n",$get_sectors->error);
		die();
	}
	
	$get_sectors->store_result();
	$get_sectors->bind_result($resSectorId,$resTimeCreated,$resTimeUpdated,$sectLat,$sectLng);
	
	$sectorList = array();
	while($get_sectors->fetch()){
		$sectorList[$resSectorId] = array($sectLat,$sectLng,$resTimeCreated,$resTimeUpdated);
	}
	
	$returnData[] = array(
		"lat"=>$node->lat,
		"lng"=>$node->lng,
		"id"=>$thisEnb,
		"sectors"=>$sectorList
	);
}

die(json_encode($returnData,JSON_PRETTY_PRINT));
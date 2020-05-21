<?php

// Check parameters
if (empty($_GET["mno"])) die();
if (empty($_GET["swlat"])) die();
if (empty($_GET["nelng"])) die();
if (empty($_GET["nelat"])) die();
if (empty($_GET["swlng"])) die();
if (empty($_GET["limit_m"])) die();
if (empty($_GET["limit_s"])) die();

require("db.php");

// SQL Limits
$mast_limit = 500;//intval($_GET["limit_m"]);
$sector_limit = intval($_GET["limit_s"]);

// MNO
$sel_mno = intval($_GET["mno"]);

// Check parameters again
if ($mast_limit > 7500) die();
if ($sector_limit > 100) die();
if ($sel_mno > 200) die();

// Coordinates
$c_swlat = clean($_GET["swlat"]);
$c_nelng = clean($_GET["nelng"]);
$c_nelat = clean($_GET["nelat"]);
$c_swlng = clean($_GET["swlng"]);

// SQL Query data
$conditions  = "mnc = " . $sel_mno;
$conditions .= " AND lat > " . $c_swlat;
$conditions .= " AND lng < " . $c_nelng;
$conditions .= " AND lat < " . $c_nelat;
$conditions .= " AND lng > " . $c_swlng;

// Get a list of eNBs matching parameters
$get_enodebs = $db_connection->query("SELECT enodeb_id,lat,lng FROM masts WHERE " . $conditions . " LIMIT {$mast_limit}");

if (!$get_enodebs) {
	die($db_connection->error());
}

// Query for getting sectors for eNBs
$get_sectors = $db_connection->prepare("SELECT id,created,updated,lat,lng FROM sectors WHERE mnc = {$_GET['mno']} AND enodeb_id = ? LIMIT {$sector_limit}");
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

$get_sectors->close();

die(json_encode($returnData,JSON_PRETTY_PRINT));
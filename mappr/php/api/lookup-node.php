<?php

if (!isset($api_auth) || !$api_auth) die();

// MNC
$mnc = null;
if (!empty($_GET["mnc"]) && is_numeric($_GET["mnc"])) {
	$mnc = intval($_GET["mnc"]);
} else {
	die();
}

// eNodeB ID
$enb = null;
if (!empty($_GET["enb"]) && is_numeric($_GET["enb"])) {
	$enb = intval($_GET["enb"]);
}

$returnData = array();

$limitMNC = "AND ".DB_MASTS.".mnc = ".DB_SECTORS.".mnc";
if ($mnc !== null){
	$limitMNC = "AND ".DB_SECTORS.".mnc={$mnc} AND ".DB_MASTS.".mnc = {$mnc}";
}

$limitIds = "";
if ($enb !== null) {
	$limitIds = "AND ".DB_MASTS.".enodeb_id = '$enb'";
} else if (count($enb_range) === 2){
	$limitIds = "AND ".DB_MASTS.".enodeb_id > {$enb_range[0]} AND ".DB_MASTS.".enodeb_id < {$enb_range[1]}";
}

$sql = "SELECT DISTINCT(".DB_SECTORS.".enodeb_id), ".DB_MASTS.".lat, ".DB_MASTS.".lng, ".DB_SECTORS.".mnc
		FROM ".DB_SECTORS.", ".DB_MASTS."
		WHERE ".DB_MASTS.".enodeb_id = ".DB_SECTORS.".enodeb_id
		{$limitMNC} {$limitIds}
		LIMIT 10";

if (DEBUG) die($sql);

// Run SQL query and return results
$get_enblist = $db_connection->query($sql);

$get_sectors = $db_connection->prepare("SELECT sector_id,pci,created,updated,lat,lng FROM ".DB_SECTORS." WHERE mcc = ? AND mnc = ? AND enodeb_id = ? LIMIT 50");
$get_sectors->bind_param("iii",$thisMcc, $thisMnc,$thisEnb);

while ($node = $get_enblist->fetch_object()){
	$thisMcc = 234;
	$thisMnc = $node->mnc;
	$thisEnb = $node->enodeb_id;

	if (!$get_sectors->execute()){
		printf("Database Error: %s\n",$get_sectors->error);
		die();
	}

	$get_sectors->store_result();
	$get_sectors->bind_result($resSectorId,$resPci,$resTimeCreated,$resTimeUpdated,$sectLat,$sectLng);

	$sectorList = array();
	while($get_sectors->fetch()){
		$sectorList[$resSectorId] = array($sectLat,$sectLng,$resTimeCreated,$resTimeUpdated,$resPci);
	}

	$returnData[] = array(
		"lat"=>$node->lat,
		"lng"=>$node->lng,
		"id"=>$thisEnb,
		"mnc"=>$thisMnc,
		"sectors"=>$sectorList
	);
}

die(json_encode($returnData,JSON_PRETTY_PRINT));
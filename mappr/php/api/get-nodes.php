<?php

if (empty($api_auth) || $api_auth === false) die();

// MNC
$mnc = null;
if (!empty($_GET["mnc"]) && is_numeric($_GET["mnc"])) {
	$mnc = intval($_GET["mnc"]);
}

// eNodeB ID
$enb = null;
if (!empty($_GET["enb"]) && is_numeric($_GET["enb"])) {
	$enb = intval($_GET["enb"]);
}

// eNodeB ID range
$enb_range = array();
if (!empty($_GET["enb_range"]) && is_array($_GET["enb_range"]) && count($_GET["enb_range"]) === 2) {
	$enb_range = intArray($_GET["enb_range"]);
}

// Sector IDs
$sector_list = array();
if (!empty($_GET["sectors"]) && is_array($_GET["sectors"]) && count($_GET["sectors"]) !== 0) {
	$sector_list = intArray($_GET["sectors"]);
}

// PCIs
$pci_list = array();
if (!empty($_GET["pcis"]) && is_array($_GET["pcis"]) && count($_GET["pcis"]) !== 0) {
	$pci_list = intArray($_GET["pcis"]);
}

$returnData = array();

// SQL parameters that apply to all queries
if (empty($_GET["swlat"]) || empty($_GET["nelng"]) || empty($_GET["nelat"]) || empty($_GET["swlng"])){
	die();
}

$sLatSw = clean($_GET["swlat"]);
$sLngSw = clean($_GET["swlng"]);
$sLatNe = clean($_GET["nelat"]);
$sLngNe = clean($_GET["nelng"]);

function getSqlParams($dbTbl) {
	global $sLatSw, $sLngSw, $sLatNe, $sLngNe, $mnc, $enb, $enb_range;

	$limitBounds = " AND ({$dbTbl}.lat > {$sLatSw})";
	$limitBounds .= " AND ({$dbTbl}.lng < {$sLngNe})";
	$limitBounds .= " AND ({$dbTbl}.lat < {$sLatNe})";
	$limitBounds .= " AND ({$dbTbl}.lng > {$sLngSw})";

	$limitMNC = "AND {$dbTbl}.mnc = ".DB_SECTORS.".mnc";
	if ($mnc !== null){
		$limitMNC .= " AND ".DB_SECTORS.".mnc={$mnc}";
	}

	$limitIds = "";
	if ($enb !== null) {
		$limitIds = "AND {$dbTbl}.enodeb_id = '{$enb}'";
	} else if (count($enb_range) === 2){
		$limitIds = "AND {$dbTbl}.enodeb_id > {$enb_range[0]} AND {$dbTbl}.enodeb_id < {$enb_range[1]}";
	}

	return "{$limitBounds} {$limitMNC} {$limitIds}";
}

$limitSectors = "";
if (count($sector_list) > 0){
	$sectorSql = implode(",",$sector_list);
	$limitSectors = "AND ".DB_SECTORS.".sector_id IN ({$sectorSql})";
}

$limitPCIs = "";
if (count($pci_list) > 0){
	$pciSql = implode(",",$pci_list);
	$limitSectors = "AND ".DB_SECTORS.".pci IN ({$pciSql})";
}

$fetchWithUserLocations = true;
if (!empty($_GET["alldata"])){
	$fetchWithUserLocations = true;
}

$fetchEstimatedLocations = false;
if (!empty($_GET["onlymls"])){
	$fetchEstimatedLocations = true;
}

$dbTblList = array(DB_LOCATIONS);
if ($fetchWithUserLocations) {
	$dbTblList = array(DB_LOCATIONS, DB_MASTS);
}
if ($fetchEstimatedLocations) {
	$dbTblList = array(DB_MASTS);
}

$enbList = array();
foreach ($dbTblList as $dbTbl) {
	$limitDbTbl = getSqlParams($dbTbl);
	$sql = "SELECT DISTINCT({$dbTbl}.enodeb_id), {$dbTbl}.id, {$dbTbl}.lat, {$dbTbl}.lng, {$dbTbl}.mnc
		FROM ".DB_SECTORS.", {$dbTbl}
		WHERE {$dbTbl}.enodeb_id = ".DB_SECTORS.".enodeb_id
		{$limitDbTbl} {$limitSectors} {$limitPCIs}
		LIMIT " . API_LIMIT;

	if (DEBUG) die($sql);

	// Run SQL query and return results
	$get_enblist = $db_connection->query($sql);
	echo $db_connection->error;
	while ($node = $get_enblist->fetch_object()) {
		if (array_key_exists($node->mnc . "_" . $node->enodeb_id, $enbList)) continue;

		$enbList[$node->mnc . "_" . $node->enodeb_id] = array(
			"lat"=>$node->lat,
			"lng"=>$node->lng,
			"verified"=>$dbTbl === DB_LOCATIONS
		);
	}
}

$get_sectors = $db_connection->prepare("SELECT sector_id,pci,created,updated,lat,lng FROM ".DB_SECTORS." WHERE mnc = ? AND enodeb_id = ? LIMIT 50");
$get_sectors->bind_param("ii",$thisMnc,$thisEnb);

foreach($enbList as $identifier=>$coords) {
	$node_id = explode("_", $identifier);
	$thisMnc = $node_id[0];
	$thisEnb = $node_id[1];

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
		"verified"=>$coords["verified"],
		"lat"=>$coords["lat"],
		"lng"=>$coords["lng"],
		"id"=>$thisEnb,
		"mnc"=>$thisMnc,
		"sectors"=>$sectorList
	);
}

die(json_encode($returnData,JSON_PRETTY_PRINT));
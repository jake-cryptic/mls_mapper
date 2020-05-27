<?php

if (!isset($api_auth)) die();

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
$limitBounds = "";
if (empty($_GET["swlat"]) || empty($_GET["nelng"]) || empty($_GET["nelat"]) || empty($_GET["swlng"])){
	die();
}
if (!empty($_GET["swlat"])) $limitBounds .= " AND ".DB_MASTS.".lat > " . clean($_GET["swlat"]);
if (!empty($_GET["nelng"])) $limitBounds .= " AND ".DB_MASTS.".lng < " . clean($_GET["nelng"]);
if (!empty($_GET["nelat"])) $limitBounds .= " AND ".DB_MASTS.".lat < " . clean($_GET["nelat"]);
if (!empty($_GET["swlng"])) $limitBounds .= " AND ".DB_MASTS.".lng > " . clean($_GET["swlng"]);

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

$sql = "SELECT DISTINCT(".DB_SECTORS.".enodeb_id), ".DB_MASTS.".lat, ".DB_MASTS.".lng, ".DB_SECTORS.".mnc
		FROM ".DB_SECTORS.", ".DB_MASTS."
		WHERE ".DB_MASTS.".enodeb_id = ".DB_SECTORS.".enodeb_id
		{$limitMNC} {$limitIds} {$limitPCIs} {$limitSectors} {$limitBounds}
		LIMIT " . API_LIMIT;

if (DEBUG) die($sql);

// Run SQL query and return results
$get_enblist = $db_connection->query($sql);

$get_sectors = $db_connection->prepare("SELECT sector_id,pci,created,updated,lat,lng FROM ".DB_SECTORS." WHERE mnc = ? AND enodeb_id = ? LIMIT 50");
$get_sectors->bind_param("ii",$thisMnc,$thisEnb);

while ($node = $get_enblist->fetch_object()){
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
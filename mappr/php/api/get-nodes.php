<?php

if (empty($api_auth) || $api_auth === false) die();

$timings = array(
	"start"=>array(round(array_sum( explode( ' ' , microtime() ) ),4), 0)
);
function t($label) {
	global $timings;

	$thisTime = array_sum( explode( ' ' , microtime() ) );
	$timings[$label] = array($thisTime, round($thisTime - $timings["start"][0], 4));
}

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

t("Get Base Parameters");

function getSqlCoordBounds($dbTbl) {
	global $sLatSw, $sLngSw, $sLatNe, $sLngNe, $mnc;

	$limitBounds = " AND ({$dbTbl}.lat > {$sLatSw})";
	$limitBounds .= " AND ({$dbTbl}.lng < {$sLngNe})";
	$limitBounds .= " AND ({$dbTbl}.lat < {$sLatNe})";
	$limitBounds .= " AND ({$dbTbl}.lng > {$sLngSw})";

	$limitMCC = "AND {$dbTbl}.mcc = ".DB_SECTORS.".mcc";

	$limitMNC = "AND {$dbTbl}.mnc = ".DB_SECTORS.".mnc";
	if ($mnc !== null){
		$limitMNC .= " AND ".DB_SECTORS.".mnc={$mnc}";
	}

	return "{$limitBounds} {$limitMNC} {$limitMCC}";
}

function getSqlInfoBounds($dbTbl) {
	global $enb, $enb_range, $sector_list, $pci_list;

	$limitIds = "";
	if ($enb !== null) {
		$limitIds = "AND {$dbTbl}.node_id = '{$enb}'";
	} else if (count($enb_range) === 2){
		$limitIds = "AND {$dbTbl}.node_id > {$enb_range[0]} AND {$dbTbl}.node_id < {$enb_range[1]}";
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

	return "{$limitSectors} {$limitPCIs} {$limitIds}";
}

function getEnbs($dbTbl) {
	global $db_connection;
	$enbList = array();

	$dbAreaLimit = getSqlCoordBounds($dbTbl);
	$dbInfoLimit = getSqlInfoBounds($dbTbl);

	$includeUserId = "";
	if ($dbTbl === DB_LOCATIONS) {
		$includeUserId = "{$dbTbl}.user_id, ";
	}

	$sql = "SELECT DISTINCT({$dbTbl}.node_id), {$includeUserId} {$dbTbl}.id, {$dbTbl}.lat, {$dbTbl}.lng, {$dbTbl}.mean_lat, {$dbTbl}.mean_lng, {$dbTbl}.mnc, {$dbTbl}.mcc
		FROM ".DB_SECTORS.", {$dbTbl}
		WHERE {$dbTbl}.node_id = ".DB_SECTORS.".node_id
		{$dbAreaLimit} {$dbInfoLimit}
		LIMIT " . API_LIMIT;

	// Run SQL query and return results
	$get_enblist = $db_connection->query($sql);
	echo $db_connection->error;
	while ($node = $get_enblist->fetch_object()) {
		// if (array_key_exists($node->mnc . "_" . $node->node_id, $enbList)) continue;
		$enbList[$node->mcc . "_" . $node->mnc . "_" . $node->node_id] = array(
			"lat"=>$node->lat,
			"lng"=>$node->lng,
			"mean_lat"=>$node->mean_lat,
			"mean_lng"=>$node->mean_lng,
			"verified"=> $node->user_id ?? 0
		);
	}

	return $enbList;
}

// Load DB data
//$enbListLoc = getEnbs(DB_LOCATIONS);
$enbListLoc = array();
t("Get Results for " . DB_LOCATIONS);

$enbListMls = getEnbs(DB_MASTS);
t("Get Results for " . DB_MASTS);

// Conditionally return
$enbList = array();
if (!empty($_GET["verified"])){
	$enbList = $enbListLoc;
}

if (!empty($_GET["estimate"])){
	$enbList = $enbListMls;
}

if (!empty($_GET["verified"]) && !empty($_GET["estimate"])) {
	$enbList = array_merge($enbListMls, $enbListLoc);
}

if (DEBUG) die($sql);

$get_sectors = $db_connection->prepare("SELECT sector_id,pci,created,updated,lat,lng FROM ".DB_SECTORS." WHERE mcc = ? AND mnc = ? AND node_id = ? LIMIT 50");
$get_sectors->bind_param("iii",$thisMcc,$thisMnc,$thisEnb);

t("Prepare stmt");
foreach($enbList as $identifier=>$coords) {
	$node_id = explode("_", $identifier);
	$thisMcc = $node_id[0];
	$thisMnc = $node_id[1];
	$thisEnb = $node_id[2];

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
		"verified"=>1,
		"lat"=>$coords["lat"],
		"lng"=>$coords["lng"],
		"id"=>$thisEnb,
		"mcc"=>$thisMcc,
		"mnc"=>$thisMnc,
		"sectors"=>$sectorList
	);

    $returnData[] = array(
        "verified"=>0,
        "lat"=>$coords["mean_lat"],
        "lng"=>$coords["mean_lng"],
        "id"=>$thisEnb . "_unverif",
        "mcc"=>$thisMcc,
        "mnc"=>$thisMnc,
        "sectors"=>$sectorList
    );
}
t("Process eNBs");

$returnSet = array(
	"timings"=>$timings,
	"results"=>$returnData
);

die(json_encode($returnSet,JSON_PRETTY_PRINT));
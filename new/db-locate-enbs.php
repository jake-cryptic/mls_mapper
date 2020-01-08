<?php
ini_set('memory_limit','-1');
set_time_limit(-1);

echo "Starting locator...\n";

$start = time();

// Database logins
DEFINE("DB_USERNAME","root");
DEFINE("DB_DATABASE","lte_cell_export");
DEFINE("DB_PASSWORD","");
DEFINE("DB_HOSTNAME","localhost");
DEFINE("DB_TABLE","sectors");

$db_connection = @new mysqli(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD,DB_DATABASE,3306);

// Check connection
if ($db_connection->connect_error) {
	unset($db_connection);
	die("Database connection error.");
}

$eNbList = array(
	10=>array(),
	15=>array(),
	20=>array(),
	30=>array()
);

$get_enodebs = $db_connection->query("SELECT DISTINCT mnc,enodeb_id FROM " . DB_TABLE);

$get_sectors = $db_connection->prepare("SELECT sector_id,lat,lng,samples,created,updated FROM " . DB_TABLE . " WHERE mnc = ? AND enodeb_id = ? LIMIT 50");
$get_sectors->bind_param("ii",$thisMnc,$thisEnb);

echo "Compiling data...\n";

while ($cell = $get_enodebs->fetch_object()){
	//print($cell->mnc . " eNB:" . $cell->enodeb_id . "\n");
	
	$thisMnc = $cell->mnc;
	$thisEnb = $cell->enodeb_id;
	
	if (!$get_sectors->execute()){
		printf("Database Error: %s\n",$get_sectors->error);
		die();
	}
	
	$get_sectors->store_result();
	$get_sectors->bind_result($resSectorId,$resCoordLat,$resCoordLng,$resNumSamples,$resTimeCreated,$resTimeUpdated);
	
	$sectorList = array();
	while($get_sectors->fetch()){
		$sectorList[$resSectorId] = array($resCoordLat,$resCoordLng,$resNumSamples,$resTimeCreated,$resTimeUpdated);
	}
	
	$eNbList[$thisMnc][$thisEnb] = $sectorList;
}

print("eNbs for O2:" . count(array_keys($eNbList[10])) . "\n");
print("eNbs for vf:" . count(array_keys($eNbList[15])) . "\n");
print("eNbs for h3:" . count(array_keys($eNbList[20])) . "\n");
print("eNbs for ee:" . count(array_keys($eNbList[30])) . "\n");

function sampleWeight($samples){
	return ceil(log($samples)) + 1;
}

function averageCoords($sector){
	$lat = 0; $latTot = 0;
	$lng = 0; $lngTot = 0;
	
	foreach ($sector as $sectorId=>$sectorData){
		$counter = sampleWeight($sectorData[2]);
		
		$latTot += $counter;
		$lngTot += $counter;
		
		$lat += floatval($sectorData[0]) * $counter;
		$lng += floatval($sectorData[1]) * $counter;
	}
	
	$lat /= $latTot;
	$lng /= $lngTot;
	
	return array($lat,$lng);
}

echo "Locating masts...\n";

$db_connection->query("START TRANSACTION");

// Insert back into database
$ins = $db_connection->prepare("INSERT INTO masts (mnc,enodeb_id,lat,lng,updated) VALUES (?, ?, ?, ?, ?)");
$ins->bind_param("iissi",$mnc,$eNodeB,$coordLat,$coordLng,$currTime);

$iter = 0;

foreach ($eNbList as $mncCode=>$mncData){
	echo "Locator processing data MNC[{$mncCode}]\n";
	$currTime = time();
	
	foreach ($mncData as $eNbId=>$eNbData){
		$mnc = $mncCode;
		$eNodeB = $eNbId;
		
		$siteLocation = averageCoords($eNbData);
		
		$coordLat = $siteLocation[0];
		$coordLng = $siteLocation[1];
		
		$ins->execute();
		
		$iter++;
	}
}

$ins->close();

$db_connection->query("COMMIT");

$db_connection->close();

echo "{$iter} masts located!\n";
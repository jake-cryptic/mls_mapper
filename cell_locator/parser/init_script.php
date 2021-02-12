<?php
ini_set('memory_limit', '-1');		// Remove memory limit
set_time_limit(-1); 				// Remove time limit.

// Database logins
DEFINE("DB_USERNAME","root");
DEFINE("DB_DATABASE","lte_database");
DEFINE("DB_PASSWORD","");
DEFINE("DB_HOSTNAME","localhost");
DEFINE("SECTORS_TBL","sectors");
DEFINE("MASTS_TBL","masts");
DEFINE("DB_FILE","lte_cell_export.sql");

$db_connection = @new mysqli(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD,DB_DATABASE,3306);

// Check connection
if ($db_connection->connect_error) {
	unset($db_connection);
	die("Database connection error.");
}

if ($argv[1] === "-setupdb"){
	echo "Will now install database structure.";
	
	if (!file_exists(DB_FILE)){
		die("Could not locate: " . DB_FILE);
	}
	
	$databaseStruct = file_get_contents(DB_FILE);
	
	if ($db_connection->multi_query($databaseStruct)) {
		echo "success";
		unlink(DB_FILE);
	} else {
		echo "error " . $db_connection->error;
	}
	
	die();
}

$start = time();
$cdb = $argv[1];	// CSV Database (from https://location.services.mozilla.com/downloads)
$mcc = 234;	
$rat = "LTE";
$limitMNC = false;

// Check if data directory exists/can be created
if (@!mkdir("data")){
	echo "Data directory already exists!\n";
} else {
	echo "Data directory created\n";
}

function csv_to_array($filename='', $delimiter=','){
	global $rat;
	
    if(!file_exists($filename) || !is_readable($filename))
        return FALSE;

    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE){
			if ($row[0] !== $rat) continue;
            $data[] = $row;
        }
        fclose($handle);
    }
    return $data;
}

$csvData = csv_to_array($cdb);

echo "Opened file\n";

$mData = array();
// Set variables
$r = 0;
$p = 0;
$v = 0;
$s = time()+1;
$outFile = "data/" . time() . "-" . $mcc . ".csv";
$mccFile = fopen($outFile,"a+");

// Put mcc data in mcc
foreach ($csvData as $row){
	// Update the "UI"
	if (time() > $s){
		$s = time();
		$v = $p;
		$p = 0;
		echo "Record " . $r++ . " [CellID: " . $row[4] . "] - " . ($v === 0 ? "??" : $v) . " records/sec\n";
	} else {
		$p++;
	}
	
	// Update
	if ($row[1] !== $mcc) continue;
	
	fputcsv($mccFile,$row);
}

echo "\nFinished. Took " . (-1 * ($start-time())) . " seconds to process " . $r . " cell ids.";

function cidToEnb($cid){
	$data = decbin($cid);
	$sector = substr($data,-8);
	$node = substr($data,0,-8);
	return array(bindec($node),bindec($sector));
}

function calcPerformance($iter){
	global $start;
	
	$tElapsed = time() - $start;
	echo "Record " . $iter . ", time elapsed: " . $tElapsed . "\n";
}

// Check input database
if (!file_exists($outFile)){
	die("Cannot find database file.");
}
if (!($fh = fopen($outFile,"r"))){
	die("Cannot open database file (check permissions maybe).");
}

$ins = $db_connection->prepare("INSERT INTO " . SECTORS_TBL . " (cell_id,mnc,enodeb_id,sector_id,pci,lat,lng,samples,created,updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$ins->bind_param("iiiiissiii",$cellId,$mnc,$eNodeB,$sectorId,$pci,$coordLat,$coordLng,$numSamples,$timeCreated,$timeUpdated);

// Load network data filter file
include("uk_networks_filter.php");

// Set variables
$iter = 0;
$r = 0;
$p = 0;
$v = 0;
$s = time()+1;

while (($data = fgetcsv($fh)) !== FALSE){
	if ($data[0] !== $rat) continue;
	if ($data[1] !== $mcc) continue;
	if ($limitMNC !== false && $data[2] !== $limitMNC) continue;
	
	$mnc = intval($data[2]);
	
	if (!in_array($mnc,array(10,15,20,30))) continue;
	
	$cellInfo = cidToEnb($data[4]);
	
	if ($uk_filter_map[$mnc]($cellInfo[0],$cellInfo[1]) === false){
		echo "Sector ID issue MNC " . $data[2] . " eNb:" . $cellInfo[0] . " Sector:" . $cellInfo[1] . "\n";
		continue;
	}
	
	$cellId = $data[4];
	$eNodeB = $cellInfo[0];
	$sectorId = $cellInfo[1];
	$pci = $data[5];
	
	$coordLat = $data[7];
	$coordLng = $data[6];
	
	$numSamples = $data[9];
	$timeCreated = $data[11];
	$timeUpdated = $data[12];
	
	$ins->execute();
	
	// Update the "UI"
	if (time() > $s){
		$s = time();
		$v = $p;
		$p = 0;
		echo ($v === 0 ? "??" : $v) . " records/sec\n";
	} else {
		$p++;
	}
	
	$iter++;
}

// Clean up memory
$ins->close();

$r = $db_connection->query("OPTIMIZE TABLE " . SECTORS_TBL);
if (!$r){
	echo "\nDatabase may perform poorly, optimisation couldn't complete.\n";
}

fclose($fh);

echo "Added " . $iter . " records to database in " . (-1 * ($start-time())) . " seconds.";

$eNbList = array(
	10=>array(),
	15=>array(),
	20=>array(),
	30=>array()
);

$get_enodebs = $db_connection->query("SELECT DISTINCT mnc,enodeb_id FROM " . SECTORS_TBL);

$get_sectors = $db_connection->prepare("SELECT sector_id,lat,lng,samples,created,updated FROM " . SECTORS_TBL . " WHERE mnc = ? AND enodeb_id = ? LIMIT 50");
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
	if ($samples === 1) 	return 1;
	if ($samples <= 3) 		return 3;
	if ($samples <= 7) 		return 4;
	if ($samples <= 10) 	return 5;
	if ($samples <= 50) 	return 7;
	if ($samples <= 100) 	return 11;
	if ($samples <= 250) 	return 13;
	return 14;
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

// Insert back into database
$ins = $db_connection->prepare("INSERT INTO " . MASTS_TBL . " (mnc,enodeb_id,lat,lng,updated) VALUES (?, ?, ?, ?, ?)");
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

$db_connection->close();

echo "{$iter} masts located!\n";

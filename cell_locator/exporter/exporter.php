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

if ($argv[1] === "-getmozfile") {
	$file_name = readline("> ");
	
	$content = file_get_contents($file_name);
	file_put_contents("latest.csv.gz",$content);
	
	die("Done!");
} else if ($argv[1] === "-unzip") {
	$file_name = readline("> ");

	$buffer_size = 4096; // read 4kb at a time
	$out_file_name = str_replace('.gz', '', $file_name); 

	$file = gzopen($file_name, 'rb');
	$out_file = fopen($out_file_name, 'wb'); 

	while (!gzeof($file)) {
		fwrite($out_file, gzread($file, $buffer_size));
	}

	// Files are done, close files
	fclose($out_file);
	gzclose($file);
	die("Done!");
}

$db_connection = @new mysqli(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD,DB_DATABASE,3306);

// Check connection
if ($db_connection->connect_error) {
	unset($db_connection);
	die("Database connection error.");
}

if ($argv[1] === "-setupdb"){
	echo "Will now install database structure.\n";
	
	if (!file_exists(DB_FILE)){
		die("Could not locate: " . DB_FILE);
	}
	
	$databaseStruct = file_get_contents(DB_FILE);
	
	if ($db_connection->multi_query($databaseStruct)) {
		echo "success\n";
		unlink(DB_FILE);
	} else {
		echo "error\n " . $db_connection->error;
	}
	
	die("Done!");
}

$start = time();
$cdb = $argv[1];	// CSV Database (from https://location.services.mozilla.com/downloads)
$mcc = 234;	
$rat = "LTE";
$limitMNC = false;

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

print("\nNow loading CSV file....\n");
$csvData = csv_to_array($cdb);

echo "Opened file\n";

$mData = array();

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

foreach ($csvData as $data){
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

echo "\nFinished. Took " . (-1 * ($start-time())) . " seconds to process " . $r . " cell ids.\n";

// Clean up memory
$ins->close();

$r = $db_connection->query("OPTIMIZE TABLE " . SECTORS_TBL);
if (!$r){
	echo "\nDatabase may perform poorly, optimisation couldn't complete.\n";
}

echo "Added " . $iter . " records to database in " . (-1 * ($start-time())) . " seconds.";

unset($csvData);

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

<?php
ini_set('memory_limit','-1');
set_time_limit(-1);

echo "Starting Converter...\n";

$start = time();

function cidToEnb($cid){
	$data = decbin($cid);
	$sector = substr($data,-8);
	$node = substr($data,0,-8);
	return array(bindec($node),bindec($sector));
}

// Settings
$limitMCC = "234";
$limitMNC = false;

$file = readline("Input File> ");

// Check input database
if (!file_exists($file)){
	die("Cannot find database file.");
}
if (!($fh = fopen($file,"r"))){
	die("Cannot open database file (check permissions maybe).");
}

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

$db_connection->query("START TRANSACTION");

$ins = $db_connection->prepare("INSERT INTO " . DB_TABLE . " (cell_id,mnc,enodeb_id,sector_id,pci,lat,lng,samples,created,updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$ins->bind_param("iiiiissiii",$cellId,$mnc,$eNodeB,$sectorId,$pci,$coordLat,$coordLng,$numSamples,$timeCreated,$timeUpdated);

// Load network data filter file
include("uk_networks_filter.php");

// Set variables
$iter = 0;

while (($data = fgetcsv($fh)) !== FALSE){
	if ($limitMCC !== false && $data[1] !== $limitMCC) continue;
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
	
	$iter++;
}

// Clean up memory
$ins->close();

$db_connection->query("COMMIT");

$r = $db_connection->query("OPTIMIZE TABLE " . DB_TABLE);
if (!$r){
	echo "\nDatabase may perform poorly, optimisation couldn't complete.\n";
}

$db_connection->close();

fclose($fh);

die("Added " . $iter . " records to database in " . (-1 * ($start-time())) . " seconds.");
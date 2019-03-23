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

function calcPerformance($iter){
	global $start;
	
	$tElapsed = time() - $start;
	echo "Record " . $iter . ", time elapsed: " . $tElapsed . "\n";
}

// Settings
$limitMCC = "234";
$limitMNC = false;
$limitRAT = "LTE";

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

$ins = $db_connection->prepare("INSERT INTO " . DB_TABLE . " (cell_id,mnc,enodeb_id,sector_id,lat,lng,samples,created,updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$ins->bind_param("iiiissiii",$cellId,$mnc,$eNodeB,$sectorId,$coordLat,$coordLng,$numSamples,$timeCreated,$timeUpdated);

$iter = 0;

while (($data = fgetcsv($fh)) !== FALSE){
	if ($limitRAT !== false && $data[0] !== $limitRAT) continue;
	if ($limitMCC !== false && $data[1] !== $limitMCC) continue;
	if ($limitMNC !== false && $data[2] !== $limitMNC) continue;
	
	$mnc = $data[2];
	
	$cellInfo = cidToEnb($data[4]);
	
	$cellId = $data[4];
	$eNodeB = $cellInfo[0];
	$sectorId = $cellInfo[1];
	
	$coordLat = $data[7];
	$coordLng = $data[6];
	
	$numSamples = $data[9];
	$timeCreated = $data[11];
	$timeUpdated = $data[12];
	
	$ins->execute();
	
	if ($iter % 1000 === 0) calcPerformance($iter);
	
	$iter++;
}

// Clean up memory
$ins->close();
$db_connection->close();

fclose($fh);

die("Added " . $iter . " records to database");
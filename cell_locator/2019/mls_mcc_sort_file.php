<?php
ini_set('memory_limit', '-1');		// Remove memory limit
set_time_limit(-1); 				// Remove time limit.

$start = time();
$cdb = readline("Input File> ");	// CSV Database (from https://location.services.mozilla.com/downloads)
$mcc = readline("Requested MCC> ");	
$rat = readline("Requested RAT> ");	

$file = fopen($cdb, "r");
echo "Opened file\n";

// Check if data directory exists/can be created
if (@!mkdir("data")){
	echo "Data directory already exists!\n";
} else {
	echo "Data directory created\n";
}

sleep(1);

$mData = array();
if ($file !== FALSE) {
	// Set variables
	$r = 0;
	$p = 0;
	$v = 0;
	$s = time()+1;
	$mccFile = fopen("data/" . $mcc . ".csv","a+");
	
	while (($data = fgetcsv($file)) !== FALSE) {
		if ($data[0] !== $rat) continue;
		if ($data[1] !== $mcc) continue;
		
		$mData[] = $data;
	}
	
	// Put mcc data in mcc
	foreach ($mData as $row){
		fputcsv($mccFile,$row);
	}
	
	// Close main file
	fclose($file);
}
echo "\nFinished. Took " . (-1 * ($start-time())) . " seconds to process " . $r . " cell ids.";

<?php
ini_set('memory_limit', '-1');		// Remove memory limit
set_time_limit(-1); 				// Remove time limit.

$start = time();
$cdb = readline("Input File> ");	// CSV Database (from https://location.services.mozilla.com/downloads)
$mcc = readline("Requested MCC> ");	

$file = fopen($cdb, "r");
echo "Opened file\n";

// Check if data directory exists/can be created
if (!mkdir("data")){
	echo "Data directory already exists!\n";
} else {
	echo "Data directory created\n";
}

sleep(1);

$mccs = array();
if ($file !== FALSE) {
	// Set variables
	$r = 0;
	$p = 0;
	$v = 0;
	$s = time()+1;
	$mccFile = fopen("data/" . $mcc . ".csv","a+");
	
	while (($data = fgetcsv($file)) !== FALSE) {
		// Update the "UI"
		if (time() > $s){
			$s = time();
			$v = $p;
			$p = 0;
			echo "Record " . $r++ . " [CellID: " . $data[4] . "] - " . ($v === 0 ? "??" : $v) . " records/sec\n";
		} else {
			$p++;
		}
		
		
		// Put mcc data in mcc
		fputcsv($mccFile,$data);
	}
	
	// Close main file
	fclose($file);
}
echo "\nFinished. Took " . (-1 * ($start-time())) . " seconds to process " . $r . " cell ids.";

<?php
ini_set('memory_limit', '-1');		// Remove memory limit
set_time_limit(-1); 				// Remove time limit.

$start = time();
$cdb = readline("Input File> ");	// CSV Database (from https://location.services.mozilla.com/downloads)
$mcc = readline("Requested MCC> ");	
$rat = readline("Requested RAT> ");	

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
$mccFile = fopen("data/" . $mcc . ".csv","a+");

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

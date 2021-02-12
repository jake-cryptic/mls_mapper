<?php 
ini_set('memory_limit','-1');
set_time_limit(-1);

echo "Starting Converter...\n";

function cidToEnb($cid){
	$data = decbin($cid);
	$sector = substr($data,-8);
	$node = substr($data,0,-8);
	return array(bindec($node),bindec($sector));
}

function tryMakeDir($d){
	if (@!mkdir($d)){
		return false;
	} else {
		return true;
	}
}

function unitFiles($s){
    $unit = array('Bytes','Kilobytes','Megabytes','Gigabytes');
    return @round($s/pow(1024,($i=floor(log($s,1024)))),2).' '.$unit[$i];
}

$start = time();
$file = readline("Input File> ");
$mccs = array();
$ilog = false;
$r = 0;
$p = 0;
$t = 0;
$v = 0;
$s = time()+1;
$mUsg = unitFiles(memory_get_usage(true));

tryMakeDir("export");
// Load DB
$main_db = @fopen($file,"r");

if ($main_db !== FALSE) {
	echo "Database loaded!\n";
	echo "Starting in 3 seconds...\n\n";
	sleep(3);
	cli_set_process_title("Working...");
	
	while (($data = fgetcsv($main_db)) !== FALSE){
		if ($data[1] != 234) continue;
		//if ($data[2] != 15) continue;
		if ($data[0] != "LTE") continue;
		
		// Work out records per second
		if (time() > $s){
			$s = time();
			$v = $p;
			$t = $t+$p;
			$p = 0;
			$mUsg = unitFiles(memory_get_usage(true));
			
			// Log
			if (!$ilog){
				echo "Record #{$t}; Loop #" . $r++ . " - " . ($v === 0 ? "Unknown" : $v) . " records/sec; MemUsg:" . $mUsg . "\n";
			}
		} else {
			$p++;
		}
		
		// Update the "UI"
		if ($ilog){
			echo "Record " . $r++ . " [CellID: " . $data[4] . "] - " . ($v === 0 ? "Unknown" : $v) . " records/sec; MemUsg:" . $mUsg . "\n";
		}
		
		// Check if mcc data file has been opened, if not do that
		if (!array_key_exists($data[1].$data[0].$data[2],$mccs)){
			tryMakeDir("export/" . $data[1]);
			$mccs[$data[1].$data[0].$data[2]] = fopen("export/{$data[1]}/{$data[2]}_{$data[0]}.csv","a+");
			fputcsv($mccs[$data[1].$data[0].$data[2]],array("lon","lat","area","cell","node","sector","range","samples","changeable","created","updated","avgSignal"));
		}
		
		$cData = cidToEnb($data[4]);
		$newdata = array(
			$data[6],	// lon
			$data[7],	// lat
			$data[3],	// area
			$data[4],	// cell
			$cData[0],	// node
			$cData[1],	// sector
			$data[8],	// range
			$data[9],	// samples
			$data[10],	// changeable
			$data[11],	// created
			$data[12],	// updated
			$data[13]	// avg signal
		);
		
		// Put mcc data in mcc
		fputcsv($mccs[$data[1].$data[0].$data[2]],$newdata);
	}
	
	// Close main file
	fclose($main_db);
} else {
	die("Failed to load " . $file . "\n");
}

cli_set_process_title("Done");
echo "\nFinished. Took " . (-1 * ($start-time())) . " seconds to process " . $t . " cell towers.";
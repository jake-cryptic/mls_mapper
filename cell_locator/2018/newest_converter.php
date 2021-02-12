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
$file = 'MLS-full-cell-export-2018-07-12T000000.csv';
$list = array();
$ilog = false;
$r = 0;
$p = 0;
$t = 0;
$v = 0;
$s = time()+1;
$mUsg = unitFiles(memory_get_usage(true));

tryMakeDir('export');

// Load DB
$main_db = @fopen($file,'r');

if ($main_db !== FALSE) {
	echo "Database loaded!\nStarting in 3 seconds...\n\n";
	sleep(3);
	cli_set_process_title('Working...');
	
	while (($data = fgetcsv($main_db)) !== FALSE){
		if ($data[1] != 234) continue;
		//if ($data[2] != 30) continue;
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
				echo "Record #{$t}; Loop #" . $r++ . " - " . ($v === 0 ? 'Unknown' : $v) . " records/sec; MemUsg:" . $mUsg . "\n";
				gc_collect_cycles();
			}
		} else {
			$p++;
		}
		
		// Update the "UI"
		if ($ilog){
			echo "Record " . $r++ . " [CellID: " . $data[4] . "] - " . ($v === 0 ? "Unknown" : $v) . " records/sec; MemUsg:" . $mUsg . "\n";
		}
		
		if (!array_key_exists($data[1],$list)){
			$list[$data[1]] = array();
		}
		if (!array_key_exists($data[2],$list[$data[1]])){
			$list[$data[1]][$data[2]] = array();
		}
		if (!array_key_exists($data[0],$list[$data[1]][$data[2]])){
			$list[$data[1]][$data[2]][$data[0]] = array();
		}
		
		//fputcsv($mccs[$data[1].$data[0].$data[2]],array("lon","lat","area","cell","node","sector","range","samples","changeable","created","updated","avgSignal"));
		
		$cData = cidToEnb($data[4]);
		if (!array_key_exists($cData[0],$list[$data[1]][$data[2]][$data[0]])){
			$list[$data[1]][$data[2]][$data[0]][$cData[0]] = array(
				"cell"=>$data[4],
				$cData[1]=>array($data[6],$data[7])
			);
		}
	}
	// Close main file
	fclose($main_db);
	
	$c23430 = @fopen('code23430.json','a+');
	fwrite($c23430,json_encode($list,JSON_PRETTY_PRINT));
	fclose($c23430);
} else {
	die('Failed to load ' . $file);
}

cli_set_process_title('Done');
echo "\nFinished. Took " . (-1 * ($start-time())) . " seconds to process " . $t . " cell towers.";
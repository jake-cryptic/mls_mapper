<?php

DEFINE("EXPORT_DIR","cells/regular/");

function tryMakeDir($d){
	if (@!mkdir($d)){
		return false;
	} else {
		return true;
	}
}

tryMakeDir(EXPORT_DIR);

// Update PHP config for large cell files
ini_set('memory_limit','-1');
set_time_limit(-1);

echo chr(27).chr(91).'H'.chr(27).chr(91).'J';
print("\nStarting cell parser script...\n");

// Default settings
$databaseFile = "";
$procLimit = 0;
$outFile = "cellexport-" . time() . ".json";
$limitMCC = "234";
$limitMNC = false;
$limitRAT = "LTE";

function displayHelp(){
	
	print <<<HELPMSG
 Cell Parser script by absolutedouble.co.uk
 
 Data Sources:
	https://location.services.mozilla.com
	https://www.opencellid.org/downloads.php

 Arguments:
	All arguments are in the form key=val
	Arguments with [*] are required.
	
	db[*] = Location of database file e.g. MLS-full-cell-export-2019-01-10T000000.csv (string)
	limit = Maximum number of sites to process, set to 0 for all records (int)
	export = The output file name and location, file format is JSON (string)
	mcc = The mobile country code you wish to get a file for (int)
	mnc = The mobile network code you wish to get a file for (int)
	rat = The RAT you wish to get a file for, either LTE, UMTS or GSM (string)
	
HELPMSG;
	die();
}

function argumentParse($argv){
	if (count($argv) <= 1) die("Arguments Error. No arguments set.\n");
	
	$parsed = array();
	foreach($argv as $v){
		if ($v[0] !== "-") continue;
		
		if ($v === "-help" || $v === "-h") displayHelp();
		
		$val = substr($v,1);
		$spl = explode("=",$val);
		
		if (count($spl) <= 1) die("Invalid argument specified, run script with -help to see arguments\n");
		
		$parsed[$spl[0]] = $spl[1];
	}
	
	return $parsed;
}

// Set settings
$parsed = argumentParse($argv);

if (isset($parsed["db"])) $databaseFile = $parsed["db"];
if (isset($parsed["limit"])) $procLimit = (int)$parsed["limit"];
if (isset($parsed["export"])) $outFile = $parsed["export"];
if (isset($parsed["mcc"])) $limitMCC = $parsed["mcc"];
if (isset($parsed["mnc"])) $limitMNC = $parsed["mnc"];
if (isset($parsed["rat"])) $limitRAT = $parsed["rat"];

// Check database
if (!file_exists($databaseFile)){
	die("Cannot find database file.");
}
if (!($fh = fopen($databaseFile,"r"))){
	die("Cannot open database file (check permissions maybe).");
}

// Process records
function cidToEnb($cid){
	$data = decbin($cid);
	$sector = substr($data,-8);
	$node = substr($data,0,-8);
	return array(bindec($node),bindec($sector));
}

print("Database found and opened. Parsing will now begin.");

//sleep(1);
$exportArray = array(
	"LTE"=>array(),
	"UMTS"=>array(),
	"RAT"=>array()
);
$iter = 0;

while (($data = fgetcsv($fh)) !== FALSE){
	if ($limitRAT === false || $data[0] !== $limitRAT) continue;
	if ($limitMCC === false || $data[1] !== $limitMCC) continue;
	if ($limitMNC === false || $data[2] !== $limitMNC) continue;
	if ($procLimit !== 0 && $iter > $procLimit) break;
	
	$id = cidToEnb($data[4]);
	
	if (!array_key_exists($id[0],$exportArray[$data[0]])){
		$exportArray[$data[0]][$id[0]] = array(
			"sectors"=>array()
		);
	}
	
	$exportArray[$data[0]][$id[0]]["sectors"]["{$id[1]}"] = array(
		"lon"=>$data[6],
		"lat"=>$data[7],
		"samples"=>$data[9],
		"created"=>$data[11],
		"updated"=>$data[12]
	);
	
	$iter++;
}

fclose($fh);

print("\nClosed file after {$iter} sites. Will now write new database export.\n");

$outFh = @fopen(EXPORT_DIR . $outFile,'a+');
fwrite($outFh,json_encode($exportArray,JSON_PRETTY_PRINT));
fclose($outFh);

print("Wrote new database: {$outFile}\n");
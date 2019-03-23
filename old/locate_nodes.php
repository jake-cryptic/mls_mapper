<?php

DEFINE("EXPORT_DIR","cells/located/");

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
print("\nStarting eNB locator...\n");

// Default settings
$inFile = "";
$outFile = "";
$limit = 10;

function displayHelp(){
	
	print <<<HELPMSG
 eNB locator script by absolutedouble.co.uk
 
 Data Sources:
	https://location.services.mozilla.com
	https://www.opencellid.org/downloads.php

 Arguments:
	All arguments are in the form key=val
	Arguments with [*] are required.
	
	in[*] = Location of database file e.g. outfile.json (string)
	limit = Limit of how many eNBs to process (int)
	
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

if (isset($parsed["in"])) $inFile = $parsed["in"];

// Check cells file
if (!file_exists($inFile)){
	die("Cannot find cells file.");
}
if (!($fData = file_get_contents($inFile))){
	die("Cannot read cells file.");
}

$outFile = explode(".",$inFile)[0] . "_located.json";
if (strpos($outFile,"/") !== -1){
	$e = explode("/",$outFile);
	$outFile = end($e);
}

$json = json_decode($fData);
print("Cells file found and opened. Locating will now begin.");

//sleep(1);
$exportArray = array(
	"LTE"=>array(),
	"UMTS"=>array(),
	"RAT"=>array()
);
$iter = 0;

foreach ($json->LTE as $site=>$data){
	$exportArray["LTE"][$site] = array(
		"lat"=>0,
		"lon"=>0,
		"sectors"=>$data->sectors
	);
	
	$sectorCount = 0;
	$sectorLatTotal = 0;
	$sectorLonTotal = 0;
	foreach ($data->sectors as $sector){
		$sectorCount++;
		$sectorLatTotal += $sector->lat;
		$sectorLonTotal += $sector->lon;
	}
	
	$exportArray["LTE"][$site]["lat"] = ($sectorLatTotal/$sectorCount);
	$exportArray["LTE"][$site]["lon"] = ($sectorLonTotal/$sectorCount);
	
	//print_r($exportArray["LTE"][$site]);
	
	$iter++;
}

print("\nClosed file after {$iter} sites. Will now write new database export.\n");

$outFh = @fopen(EXPORT_DIR . $outFile,'a+');
fwrite($outFh,json_encode($exportArray,JSON_PRETTY_PRINT));
fclose($outFh);

print("Wrote new database: {$outFile}\n");

<?php

function filter_uk_ee($eNodeB,$sectorId){
	// Discard eNB IDs that aren't between 10,000 - 50,000
	if ($eNodeB > 40000 || $eNodeB < 10000) return false;
	
	if($sectorId >= 21) return false;
	
	return true;
}

function filter_uk_h3g($eNodeB,$sectorId){
	// Allow for 3's small cells
	if ($eNodeB >= 50000 && $eNodeB < 50500){
		$allowedSectors = array(16);
		if (!in_array($sectorId,$allowedSectors)){
			return false;
		}
		return true;
	}
	
	// Discard eNB IDs that are larger than 20,000
	if ($eNodeB > 20000) return false;
	
	$allowedSectors = array(0,1,2,3,4,5,6,7,8,71,72,73,74,75,76);
	if (!in_array($sectorId,$allowedSectors)){
		return false;
	}
	
	return true;
}

function filter_uk_o2($eNodeB,$sectorId){
	// Discard eNB IDs between 20,000 - 500,000 and above 560,000
	if ($eNodeB > 18000 && $eNodeB <= 100000) return false;
	if ($eNodeB > 150000 && $eNodeB < 500000) return false;
	if ($eNodeB > 560000 && $eNodeB < 1000000) return false;
	if ($eNodeB > 1100000) return false;
	
	// Discard sector IDs below 110 and above 170
	if ($sectorId < 110 || $sectorId > 170) return false;
	
	$disallowedSectors = array(1,3,8,9);
	if (in_array(substr($sectorId, -1),$disallowedSectors)){
		return false;
	}
	
	return true;
}

function filter_uk_vf($eNodeB,$sectorId){
	// Discard eNB IDs between 20,000 - 500,000 and above 550,000
	if ($eNodeB > 18000 && $eNodeB < 500000) return false;
	if ($eNodeB > 560000) return false;
	
	// Discard sector IDs above 70
	if ($sectorId < 10 && $sectorId > 70) return false;
	
	$disallowedSectors = array(1,3);
	if (in_array(substr($sectorId, -1),$disallowedSectors)){
		return false;
	}
	
	return true;
}

$uk_filter_map = array(
	10=>'filter_uk_o2',
	15=>'filter_uk_vf',
	20=>'filter_uk_h3g',
	30=>'filter_uk_ee'
);
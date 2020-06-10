<?php

require("User.class.php");
require("db.php");
require("functions.php");

DEFINE("BASE_SQL","SELECT COUNT(DISTINCT(enodeb_id)) AS enbcount, COUNT(sector_id) AS sectorcount FROM " . DB_SECTORS . " WHERE ");
DEFINE("NETWORK_QUERIES",array(
	"10"=>array(
		"Total Sites"=>"1=1",
		"Vodafone Host L08"=>"sector_id IN (110,120,130) AND enodeb_id < 150000",
		"O2 Host L08"=>"sector_id IN (110,120,130) AND enodeb_id > 500000",
		"Vodafone Host L09"=>"sector_id IN (112,122,132) AND enodeb_id < 150000",
		"O2 Host L09"=>"sector_id IN (112,122,132) AND enodeb_id > 500000",
		"Vodafone Host L18"=>"sector_id IN (116,126,136) AND enodeb_id < 150000",
		"O2 Host L18"=>"sector_id IN (114,124,134) AND enodeb_id > 500000",
		"Vodafone Host L21"=>"sector_id IN (114,124,134) AND enodeb_id < 150000",
		"O2 Host L21"=>"sector_id IN (115,125,135) AND enodeb_id > 500000",
		"Vodafone Host L23-C1"=>"sector_id IN (115,125,135) AND enodeb_id < 150000",
		"O2 Host L23-C1"=>"sector_id IN (116,126,136) AND enodeb_id > 500000",
		"Vodafone Host L23-C2"=>"sector_id IN (117,127,137) AND enodeb_id < 150000",
		"O2 Host L23-C2"=>"sector_id IN (117,127,137) AND enodeb_id > 500000"
	),
	"15"=>array(
		"Total Sites"=>"1=1",
		"Vodafone Host L08"=>"sector_id IN (10,20,30) AND enodeb_id < 150000",
		"O2 Host L08"=>"sector_id IN (10,20,30) AND enodeb_id > 500000",
		"Vodafone Host L09"=>"sector_id IN (12,22,32) AND enodeb_id < 150000",
		"O2 Host L09"=>"sector_id IN (12,22,32) AND enodeb_id > 500000",
		"Vodafone Host L18"=>"sector_id IN (16,26,36) AND enodeb_id < 150000",
		"O2 Host L18"=>"sector_id IN (14,24,34) AND enodeb_id > 500000",
		"Vodafone Host L21"=>"sector_id IN (14,24,34) AND enodeb_id < 150000",
		"O2 Host L21"=>"sector_id IN (15,25,35) AND enodeb_id > 500000",
		"Vodafone Host L26"=>"sector_id IN (18,28,38) AND enodeb_id < 150000",
		"O2 Host L26"=>"sector_id IN (18,28,38) AND enodeb_id > 500000",
		"Vodafone Host L26T"=>"sector_id IN (19,29,39) AND enodeb_id < 150000"
	),
	"20"=>array(
		"Total Sites"=>"1=1",
		"L08 (6,7,8)"=>"sector_id IN (6,7,8)",
		"L18 (0,1,2)"=>"sector_id IN (0,1,2)",
		"L18-6S (3,4,5)"=>"sector_id IN (3,4,5)",
		"L18 Small Cells (16)"=>"sector_id IN (16) AND enodeb_id >= 50000",
		"L21 (71,72,73)"=>"sector_id IN (71,72,73)",
		"L21-6S (74,75,76)"=>"sector_id IN (74,75,76)",
	),
	"30"=>array(
		"Total Sites"=>"1=1",
		"L08 (12,13,14)"=>"sector_id IN (12,13,14)",
		"L18-C1 (0,1,2)"=>"sector_id IN (0,1,2)",
		"L18-C2 (3,4,5)"=>"sector_id IN (3,4,5)",
		"L21 (18,19,20)"=>"sector_id IN (18,19,20)",
		"L26-C1 (6,7,8)"=>"sector_id IN (6,7,8)",
		"L26-C2 (9,10,11)"=>"sector_id IN (9,10,11)",
		"L26-C3 (15,16,17)"=>"sector_id IN (15,16,17)"
	),
	"55"=>array(
		"Total Sites"=>"1=1"
	),
	"58"=>array(
		"Total Sites"=>"1=1"
	)
));

DEFINE("API_LIMIT",15000);
DEFINE("DEBUG",!empty($_GET["debug"]));

session_name("mappr");
session_set_cookie_params(86400 * 365, null, null, false, true);
@session_start();

$isLoggedIn = false;
if (isset($_SESSION["user"]) && $_SESSION["user"] instanceof User) {
	$isLoggedIn = true;
}

if (!isset($_SESSION["token"])) {
	$_SESSION["token"] = get_random_str(16);
}

$fv = time();

$allowed_pages = array(
	"map",
	"stats",
	"logout"
);
$allowed_apis = array(
	"account-handler",
	"get-mnc",
	"get-nodes",
	"get-pins",
	"lookup-node",
	"stats",
	"update-node",
	"user-info"
);
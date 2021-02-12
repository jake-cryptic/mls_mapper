<?php

// Database logins
if ($_SERVER["SERVER_NAME"] === "localhost") {
	DEFINE("DB_USERNAME","root");
	DEFINE("DB_DATABASE","pycellsort");
	DEFINE("DB_PASSWORD","");
	DEFINE("DB_HOSTNAME","localhost");
} else {
	DEFINE("DB_USERNAME","u953270795_mappr");
	DEFINE("DB_DATABASE","u953270795_mappr");
	DEFINE("DB_PASSWORD","phErYip03fnvu!!dscsASDPP");
	DEFINE("DB_HOSTNAME","mysql.hostinger.co.uk");
}

DEFINE("DB_USERS","users");
DEFINE("DB_BOOKMARKS","bookmarks");
DEFINE("DB_MASTS","nodes");
DEFINE("DB_LOCATIONS","mast_locations");
DEFINE("DB_SECTORS","sectors");

$db_connection = @new mysqli(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD,DB_DATABASE,3306);

// Check connection
if ($db_connection->connect_error) {
    unset($db_connection);
    die("Database connection error.");
}

function clean($var){
    global $db_connection;

    return $db_connection->real_escape_string($var);
}
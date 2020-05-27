<?php

// Database logins
DEFINE("DB_USERNAME","root");
DEFINE("DB_DATABASE","lte_jcellsort");
DEFINE("DB_PASSWORD","");
DEFINE("DB_HOSTNAME","localhost");

DEFINE("DB_USERS","users");
DEFINE("DB_BOOKMARKS","bookmarks");
DEFINE("DB_MASTS","masts_adv");
DEFINE("DB_LOCATIONS","locations");
DEFINE("DB_SECTORS","sectors_adv");

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
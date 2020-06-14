<?php

if (!isset($api_auth) || !$api_auth) die();

$get_users = $db_connection->prepare("SELECT user_id, name FROM ".DB_USERS);

if (!$get_users->execute()){
	printf("Database Error: %s\n",$get_sectors->error);
	die();
}

$get_users->store_result();
$get_users->bind_result($resultId, $resultName);

$user_list = array();
while($get_users->fetch()){
	$user_list[$resultId] = $resultName;
}

die(json_encode($user_list, JSON_PRETTY_PRINT));
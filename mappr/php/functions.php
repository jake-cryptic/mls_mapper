<?php

function get_random_str($bytes = 10) {
	return bin2hex(random_bytes($bytes));
}

function intArray($arr){
	$arr = (array)$arr;

	$arr = array_filter($arr, function($v){
		return is_numeric($v);
	});

	$arr = array_map(function($v){
		return intval($v);
	},$arr);

	return $arr;
}

function output() {
	global $output;
	die(json_encode($output, JSON_PRETTY_PRINT));
}
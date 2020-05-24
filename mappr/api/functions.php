<?php

function get_random_str($bytes = 10) {
	return bin2hex(random_bytes($bytes));
}
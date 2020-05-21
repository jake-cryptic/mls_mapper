<?php

require("api/db.php");

$sql = <<<SQ
SELECT       `created`,
             COUNT(`created`) AS `value_occurrence` 
    FROM     `sectors`
    GROUP BY `created`
    ORDER BY `value_occurrence` DESC
    LIMIT    250
SQ;

$r = $db_connection->query($sql);

while($d = $r->fetch_object()) {
	print($d->created . " " . gmdate("Y-m-d\TH:i:s\Z", $d->created) . "<br />\n");
}
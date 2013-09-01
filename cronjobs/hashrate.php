#!/usr/bin/php
<?php

$includeDirectory = "[path to includes]/includes/";

include($includeDirectory."requiredFunctions.php");


/*****
VARIABLIZE:
-time per round
-share windows
-user hashrate archive window

******/


//Hashrate by worker
$sql =  "SELECT COALESCE(sum(a.id),0) as id, p.username FROM pool_worker p LEFT JOIN ".
			"((SELECT count(id) as id, username ".
			"FROM shares ".
			"WHERE time > (now() - INTERVAL '2.5 MINUTE') ".
			"GROUP BY username) ".
		"UNION ".
			"(SELECT count(id) as id, username ".
			"FROM shares_history ".
			"WHERE time > (now() - INTERVAL '2.5 MINUTE') ".
			"GROUP BY username)) a ".
		"ON p.username=a.username ".
		"GROUP BY p.username";
$result = db_query($sql);
while ($resultrow = db_fetch_object($result)) {
	$hashrate = $resultrow->id;
	$hashrate = round((($hashrate*4294967296)/600)/1000000, 0);
	db_query("UPDATE pool_worker SET hashrate = $hashrate WHERE username = '$resultrow->username'");
}

//Total Hashrate (more exact than adding)
$sql =  "SELECT sum(a.id) as id FROM ".
			"((SELECT count(id) as id FROM shares WHERE time > (now() - INTERVAL '2.5 MINUTE')) ".
		"UNION ".
			"(SELECT count(id) as id FROM shares_history WHERE time > (now() - INTERVAL '2.5 MINUTE')) ".
			") a ";
$result = db_query($sql);
if ($resultrow = db_fetch_object($result)) {
	$hashrate = $resultrow->id;
	$hashrate = round((($hashrate*4294967296)/600)/1000000, 0);
	db_query("UPDATE settings SET value = '$hashrate' WHERE setting='currenthashrate'");
}

//Hashrate by user
$sql = "SELECT u.id, COALESCE(sum(p.hashrate),0) as hashrate ".
		"FROM webUsers u LEFT JOIN pool_worker p ".
		"ON p.associateduserid = u.id ".
		"GROUP BY u.id";
$result = db_query($sql);
while ($resultrow = db_fetch_object($result)) {
	db_query("UPDATE webusers SET hashrate = $resultrow->hashrate WHERE id = $resultrow->id");

	// Enable this for lots of stats for graphing
	if ($resultrow->hashrate > 0) {
		db_query("INSERT INTO userhashrates (userid, hashrate) VALUES ($resultrow->id, $resultrow->hashrate)"); // active users hashrate
	}
}

db_query("INSERT INTO userhashrates (userid, hashrate) VALUES (0, $hashrate)"); // the pool total hashrate

$currentTime = time();
db_query("update settings set value='$currentTime' where setting='statstime'");

// Clean up the userHashrate table (anything older than 4 days)
db_query("DELETE FROM userhashrates WHERE timestamp < (now() - INTERVAL '96 HOUR')");

?>

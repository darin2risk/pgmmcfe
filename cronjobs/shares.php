<?php

$includeDirectory = "[path to includes]/includes/";

include($includeDirectory."requiredFunctions.php");

////Update share counts

//Update past shares
echo("past shares...\n");
try {
        //$pastSharesQ = mysql_query("SELECT DISTINCT userid, sum(count) AS valid, sum(invalid) AS invalid, id FROM shares_counted GROUP BY userId");
        $sql="SELECT userid,sum(valid) as val,sum(invalid) as inv FROM ".
			"(SELECT userid, sum(count) AS valid, sum(invalid) AS invalid, id ".
			"FROM shares_counted GROUP BY userid, id)a GROUP BY userid";
		$pastSharesQ = pg_query($sql);
        while ($pastSharesR = pg_fetch_object($pastSharesQ)) {
                echo($pastSharesR->userid.", ".$pastSharesR->val.", ".$pastSharesR->inv."\n");
				pg_query("UPDATE webusers SET share_count = ".$pastSharesR->val.", stale_share_count = ".$pastSharesR->inv." WHERE id = ".$pastSharesR->userid);
        }
} catch (Exception $ex)  {}

///// Update current round shares

// reset counters
echo("reset counters...\n");
pg_query("UPDATE webusers SET shares_this_round=0");

try {
        $sql =	"SELECT SUM( id ) AS id, a.associateduserid ".
		"FROM ( ".
		 "SELECT COUNT( s.id ) AS id, p.associateduserid ".
		  "FROM shares s, pool_worker p ".
		  "WHERE p.username = s.username ".
		  "AND s.our_result =  'Y' ".
		  "GROUP BY p.associateduserid ".
		 "UNION SELECT COUNT( s.id ) AS id, p.associateduserid ".
		  "FROM shares_history s, pool_worker p ".
		  "WHERE p.username = s.username ".
		  "AND s.our_result =  'Y' ".
		  "AND s.counted =  0 ".
		  "GROUP BY p.associateduserid ".
		")a ".
		"GROUP BY associateduserid";
		echo($sql."\n");
        $result = pg_query($sql);
        $totalsharesthisround = 0;
        while ($row = pg_fetch_object($result)) {
                pg_query("UPDATE webusers SET shares_this_round = ".$row->id." WHERE id = ".$row->associateduserid);
                $totalsharesthisround += $row->id;
        }

        
		$sql="SELECT userid, sum(valid) as val, sum(invalid) as inv FROM ".
			"(SELECT userid, sum(count) AS valid, sum(invalid) AS invalid, id ".
			"FROM shares_uncounted GROUP BY userid, id)a GROUP BY userid";
			
		$currentSharesQ = pg_query($sql);
        while ($currentSharesR = pg_fetch_object($currentSharesQ)) {
                pg_query("UPDATE webusers SET shares_this_round = (shares_this_round + ".$currentSharesR->val.") ".
			      "WHERE id = ".$currentSharesR->userid);
              $totalsharesthisround += $currentSharesR->val;
        }

        pg_query("UPDATE settings SET value = '$totalsharesthisround' WHERE setting='currentroundshares'");
} catch (Exception $ex)  {}

?>

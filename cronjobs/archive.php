<?php
$install_base="";
//Set page starter variables//
$includeDirectory = "$install_base/[path to includes]/includes/";

//Include site functions
require_once($includeDirectory."requiredFunctions.php");

// get current block num from litecoind - $num_blocks_old so we can leave some data in shares_history for hashrates
echo("Connecting to : $rpcHost.\n");
echo("         Type : $rpcType.\n");
$litecoinController = new LitecoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);
$currentBlockNumber = $litecoinController->query("getblockcount");
$num_blocks_old = ($currentBlockNumber - 30);
echo("CurrentBlock : $currentBlockNumber\n");
echo("OldBlockLimit: $num_blocks_old\n");
if (!$num_blocks_old) { die($num_blocks_old); }

// get all shares by user id from shares_history and move to shares_uncounted

$sql = 	"SELECT DISTINCT p.associateduserid, blocknumber, sum(s.valid) as valid, COALESCE(sum(si.invalid),0) as invalid, max(maxid) as maxid FROM ".
		"(SELECT DISTINCT username, max(blocknumber) as blocknumber, count(id) as valid, max(id) as maxid FROM shares_history ".
		  "WHERE counted=0 AND our_result='Y' AND blocknumber <= " .$num_blocks_old. " GROUP BY username,blocknumber) s ".
		"LEFT JOIN ".
		"(SELECT DISTINCT username, count(id) as invalid FROM shares_history ".
		  "WHERE counted=0 AND our_result='N' AND blocknumber <= " .$num_blocks_old. " GROUP BY username,blocknumber) si ".
		"ON s.username=si.username ".
		"INNER JOIN pool_worker p ON p.username = s.username ".
		"GROUP BY p.associateduserid, blocknumber ORDER BY blocknumber";


echo("TRACE:\n ".$sql."\n\n");
$sharesQ = db_query($sql);
$i = 0;
$maxId = 0;
$shareInputSql = "";

while ($sharesR = db_fetch_object($sharesQ)) {
	if ($sharesR->maxid > $maxId)
		$maxId = $sharesR->maxid;
	if ($i == 0) {
		$shareInputSql = "INSERT INTO shares_uncounted (blocknumber, userid, count, invalid, counted, score) VALUES ";
	}
	if ($i > 0) {
		$shareInputSql .= ",";
	}
	$i++;
	$shareInputSql .= "($sharesR->blocknumber,$sharesR->associateduserid,$sharesR->valid,$sharesR->invalid,0,0)";
	echo($i.", ".$shareInputSql."\n");
	if ($i > 20)
	{
		db_query($shareInputSql);
		$shareInputSql = "";
		$i = 0;
	}
}
if (strlen($shareInputSql) > 0){
	echo("TRACE: \n$shareInputSql\n");
	db_query($shareInputSql);
}

//Remove counted shares from shares_history
echo("TRACE:\nDELETE FROM shares_history WHERE counted = 0 AND id <= $maxId AND blocknumber <= " .$num_blocks_old."\n\n");
db_query("DELETE FROM shares_history WHERE counted = 0 AND id <= $maxId AND blocknumber <= " .$num_blocks_old);

?>
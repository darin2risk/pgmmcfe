<?php

//Set page starter variables//
$includeDirectory = "[path to includes]/includes/";

//Include site functions
include($includeDirectory."requiredFunctions.php");

//Open a litecoind connection
$litecoinController = new LitecoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);

/******
Things that can be 'variablized'
-block reward (LTC=50, SXC=100,etc)
-number of confirms
-N shares window ($lastNshares)
-
******/

$block_reward = 100;	// coins per mined block
$lastNshares = 40000;  // share window
$nConfirms = 60;		// number of confirms needed to not be 'immature'

$difficulty = $litecoinController->query("getdifficulty");
$currentBlockNumber = $litecoinController->query("getblockcount");

$lastNshares = $settings->getsetting("share_window");
$nConfirms = $settings->getsetting("num_confirms");
$block_reward = $settings->getsetting("block_reward");

echo("BlockNum: $currentBlockNumber\n");
echo("Difficulty: $difficulty\n");
echo("Block Reward: $block_reward\n");
echo("Share Window: $lastNshares\n");

//Get site percentage
$sitePercent = 0;
$sitePercentQ = pg_query("SELECT value FROM settings WHERE setting='sitepercent'");
if ($sitePercentR = pg_fetch_object($sitePercentQ)) 
    $sitePercent = $sitePercentR->value;

//Setup score variables
$c = .00001;
$f=1;
$f = $sitePercent / 100;
$p = 1.0/$difficulty;
$r = log(1.0-$p+$p/$c);
$B = $block_reward;
$los = log(1/(exp($r)-1));

//Query sexcoind for list of transactions
//Get current block number & difficulty




$transactions = $litecoinController->query('listtransactions', '*', '240');
$numAccounts = count($transactions);


echo("DEBUG: listtransaction = $numAccounts\n");
// echo("DEBUG: Transaction List \n");
// var_dump($transactions);
// echo(":DONE \n");

for($i = 0; $i < $numAccounts; $i++){
    
	// Check for 50LTC in each transaction (even when immature so we can start tracking confirms)
	/*
	if($transactions[$i]["amount"] >= $block_reward && ($transactions[$i]["category"] == "immature" || $transactions[$i]["category"] == "immature"))
		echo("TRACE: $i - true, ".$transactions[$i]['category']."\n");
	else
		echo("TRACE: $i - false, ".$transactions[$i]['category']."\n");
	*/	
	if($transactions[$i]["amount"] >= $block_reward && ($transactions[$i]["category"] == "immature" || $transactions[$i]["category"] == "immature")) {
		echo("TRACE: $transactions[$i]['category']\n");
		
		// At this point we may have found a block, Check to see if this accountAddres is already added to `networkBlocks`
		$accountExistsQ = pg_query("SELECT id FROM networkblocks WHERE accountaddress = '".$transactions[$i]["txid"]."' ORDER BY blocknumber DESC LIMIT 1")or die(pg_last_error());
		$accountExists = pg_num_rows($accountExistsQ);

		// We have a new immature transaction for 100 SXC or more - make an entry in `networkBlocks` so we can start tracking the confirms
		if(!$accountExists){
			$assoc_block = ($currentBlockNumber + 1) - $transactions[$i]["confirmations"];
			$assoc_timestamp = $transactions[$i]["time"];
			$assoc_reward = $transactions[$i]["amount"]; // get the amount from the transaction, so we can record it
			$finder = pg_fetch_object(pg_query("SELECT DISTINCT id, username FROM shares where upstream_result = 'Y'"));

			// Save the winning share and username (if we know it) --($finder) 
			if ($finder) {
				$last_winning_share = $finder->id;
				$username = $finder->username;
				pg_query("INSERT INTO winning_shares (blocknumber, username) VALUES (" .$assoc_block. ", '" .$username. "')");
			} else {
				pg_query("INSERT INTO winning_shares (blocknumber, username) VALUES (" .$assoc_block. ", 'lavajumper.miner')"); //switch this back to 'unknown' for production
			}
			
			//Debug
			echo("DEBUG: In 'found new block' mode...\n");
			//var_dump($assoc_block);
			//echo("== Debug Done ======\n");
			// save the block info so we can track confirms
			pg_query("INSERT INTO networkblocks (blocknumber, timestamp, accountaddress, confirms, difficulty, reward_amount) ".
				"VALUES ($assoc_block, '$assoc_timestamp', '" .$transactions[$i]["txid"]. "', '" .$transactions[$i]["confirmations"]. "', $difficulty, $assoc_reward)");

			// score and move shares from this block to shares_history
			$shareInputQ = "";
			$i=0;
			$lastId = 0;
			$lastScore = 0;

			if ($finder) {
			        $getAllShares = pg_query("SELECT id, rem_host, username, our_result, upstream_result, reason, solution, time FROM shares WHERE id <=" .$last_winning_share. " ORDER BY id ASC");
			} else {
			        $getAllShares = pg_query("SELECT id, rem_host, username, our_result, upstream_result, reason, solution, time FROM shares ORDER BY id ASC");
			}

		        while($share = pg_fetch_array($getAllShares)){
		                if ($i==0)
		                        $shareInputQ = "INSERT INTO shares_history (counted, blocknumber, rem_host, username, our_result, upstream_result, reason, solution, time, score) VALUES ";
		                $i++;
		                if($i > 1){
		                        $shareInputQ .= ",";
		                }
		                $score = $lastScore + $r;
		                $shareInputQ .="(0,".$assoc_block.",".
											"'".$share["rem_host"]."',".
											"'".$share["username"]."',".
											"'".$share["our_result"]."',".
											"'".$share["upstream_result"]."',".
											"'".$share["reason"]."',".
											"'".db_real_escape_string($share["solution"])."',".
											"'".$share["time"]."',".
											"$score)";
		                $lastId = $share["id"];
		                $lastScore = $score;
						//echo("DEBUG: $shareInputQ\n");
						//echo("\$i=$i\n");
		                if ($i > 5) {
		                        //Add to `shares_history`
		                        $shareHistoryQ = pg_query($shareInputQ);

		                        //If the add to shares_history was successful, lets clean up `shares` table
		                        if($shareHistoryQ){
		                                //Delete all from shares whoms "id" is less then $lastId (keep everything that didnt get moved.  Its probably from the new round.
		                                pg_query("DELETE FROM shares WHERE id <= ".$lastId);
		                        }
		                        $i = 0;
		                }
		        }
			// less than five share entries? still do the same as above.
			$shareHistoryQ = pg_query($shareInputQ);
        		if($shareHistoryQ){
                		//Delete all from shares whoms "id" is less then $lastId to prevent new "hard-earned" shares to be deleted
                		pg_query("DELETE FROM shares WHERE id <= ".$lastId);
        		}
			// Count number of shares we needed to solve this block

			// get last block number we found
			$last_winning_blockQ = pg_query("SELECT DISTINCT blocknumber FROM winning_shares ORDER BY blocknumber DESC LIMIT 1 OFFSET 1");
			$last_winning_blockObj = pg_fetch_object($last_winning_blockQ);
			$last_winning_block = $last_winning_blockObj->blocknumber;

			$block_share_countQ = db_query("SELECT sum(su_count) as total FROM (".
							   "SELECT sum(count) as su_count FROM shares_uncounted where blocknumber > " .$last_winning_block. " ".
							   "and blocknumber <= " .$assoc_block. " ".
							    "UNION SELECT count(id) as sh_count from shares_history where blocknumber <= " .$assoc_block. " AND blocknumber > " .$last_winning_block. " AND our_result != 'N' ".
							   ") a");
			$block_share_countObj = db_fetch_object($block_share_countQ);

			if($block_share_countObj) {
				db_query("UPDATE winning_shares SET sharecount = " .$block_share_countObj->total. " WHERE blocknumber = " .$assoc_block);
			}
		}
	}
}


///// Update confirms /////

// run thru list of transactions we got from litecoind and update their confirms (when immature)
for($i = 0; $i < $numAccounts; $i++){
	//if ($transactions[$i]["category"] = "receive")
	if ($transactions[$i]["category"] = "immature"){
		//Check to see if this account was one of the winning accounts from `networkBlocks`
		$arrayAddress = $transactions[$i]["txid"];
		$winningAccountQ = db_query("SELECT id FROM networkblocks WHERE accountaddress = '".$arrayAddress."' LIMIT 1");
		$winningAccount = db_num_rows($winningAccountQ);

		if($winningAccount > 0){
			//This is a winning account
			$winningAccountObj = db_fetch_object($winningAccountQ);
			$winningId = $winningAccountObj->id;
			$confirms = $transactions[$i]["confirmations"];

			//Update X amount of confirms
			db_query("UPDATE networkblocks SET confirms = ".$confirms." WHERE id = ".$winningId);
		}
	}
}




///// Check for new network block and score and move shares to shares_history if true ///

// refresh the current block number data
$currentBlockNumber = $litecoinController->getblockcount();

// make sure all blocks get entered, especially 'short-time' blocks
/*
$lastblockInDB=db_fetch_object(db_query("SELECT MAX(blocknumber) as blocknumber from networkblocks"));
if($lastblockInDB->blocknumber < $currentBlockNumber){
	for($i=$lastblockInDB;i<=$currentBlockNumber){
		db_query("INSERT INTO networkblock(blocknumber, timestamp,confirms,difficulty) VALUES ($i , '$currentTime', 0, $difficulty)");
	}
}
*/

// check if we have it in the database (if so we exit because we already did this and we were the block finder)
$inDatabaseQ = db_query("SELECT id FROM networkblocks WHERE blocknumber = $currentBlockNumber LIMIT 1");
$inDatabase = db_num_rows($inDatabaseQ);
$finder = db_fetch_object(db_query("SELECT DISTINCT id, username FROM shares where upstream_result = 'Y'"));

if(!$inDatabase){
	// make an entry in the DB for this new block
        $currentTime = time();
        db_query("INSERT INTO networkblocks (blocknumber, timestamp, confirms, difficulty) VALUES ($currentBlockNumber, '$currentTime', 0, $difficulty)");

	// score and move shares from this block to shares_history
        $shareInputQ = "";
        $i=0;
        $lastId = 0;
        $lastScore = 0;

        $getAllShares = db_query("SELECT id, rem_host, username, our_result, upstream_result, reason, solution, time FROM shares ORDER BY id ASC");

        while($share = db_fetch_array($getAllShares)){
                if ($i==0)
                        $shareInputQ = "INSERT INTO shares_history (counted, blocknumber, rem_host, username, our_result, upstream_result, reason, solution, time, score) VALUES ";
                $i++;
                if($i > 1){
                        $shareInputQ .= ",";
                }
                $score = $lastScore + $r;
                $shareInputQ .="(0,".$currentBlockNumber.",
	                              '".$share["rem_host"]."',
                                      '".$share["username"]."',
                                      '".$share["our_result"]."',
                                      '".$share["upstream_result"]."',
                                      '".$share["reason"]."',
                                      '".db_real_escape_string($share["solution"])."',
                                      '".$share["time"]."',
                                       ".$score.")";
                $lastId = $share["id"];
                $lastScore = $score;
                if ($i > 5) {
                        //Add to `shares_history`
                        $shareHistoryQ = db_query($shareInputQ);

                        //If the add to shares_history was successful, lets clean up `shares` table
                        if($shareHistoryQ){
                                //Delete all from shares whoms "id" is less then $lastId (keep everything that didnt get moved.  Its probably from the new round.
                                db_query("DELETE FROM shares WHERE id <= ".$lastId);
                        }
                        $i = 0;
                }
        }
	// less than five share entries? still do the same as above.
	$shareHistoryQ = db_query($shareInputQ);
      		if($shareHistoryQ) {
              		//Delete all from shares whoms "id" is less then $lastId to prevent new "hard-earned" shares to be deleted
              		db_query("DELETE FROM shares WHERE id <= ".$lastId);
			//exec("cd /sites/mmc/cronjobs/; /usr/bin/php archive.php");
		}
}




///// Proportional Payout Method /////

// Get uncounted share total
$overallReward = 0;
$blocksQ = db_query("SELECT DISTINCT s.blocknumber, n.reward_amount FROM shares_uncounted s, networkblocks n WHERE s.blocknumber = n.blocknumber AND s.counted=0 AND n.confirms > ($nConfirms-1) ORDER BY s.blocknumber ASC");

while ($blocks = db_fetch_object($blocksQ)) {
	$block = $blocks->blocknumber;
	$block_reward = intval($blocks->reward_amount); // variable block reward fix--Lava
	
	//comment out this below when we know its stable...
	if($block_reward == "" || $block_reward == null || $block_reward < 100 || !is_numeric($block_reward) )
		$block_reward=100;
	
	// LastNshares - mark all shares below the $lastNshares threshold counted
	$l_bound = 0;
	$total = 0;
	//$lastNshares -- defined above

	$sql = db_query("SELECT blocknumber, count FROM ( ".
				"SELECT blocknumber, count FROM shares_uncounted WHERE blocknumber <= " .$block. " ".
				"UNION SELECT blocknumber, count FROM shares_counted WHERE blocknumber <= " .$block. " AND blocknumber > ".($block - 1000)." ".
			   ")a ORDER BY blocknumber DESC");
	
	echo("TRACE:\n$sql\n\n");
	while ($result = db_fetch_object($sql)) {

		// increment $total with each row returned
		$total = $total + $result->count;

		// if $lastNshares criteria is met, and $l_bound is not our whole count, set everything below $l_bound as counted = 1
		if ($total >= $lastNshares) {
			$l_bound = $result->blocknumber;

			if ($l_bound < $block) {
				db_query("UPDATE shares_uncounted SET counted = 1 WHERE blocknumber < ".$l_bound);
			}
			break;
		}
	}

	
	// ?Update balances for confirmed blocks?--JSC
	echo("l_bound = $l_bound\nblock = $block\nlastNshares = $lastNshares\n");
	$totalRoundSharesQ = db_query("SELECT sum(id) as id FROM ( ".
					  "SELECT sum(count) as id FROM shares_uncounted WHERE blocknumber <= ".$block." AND blocknumber >= ".$l_bound." ".
					  "UNION SELECT sum(count) as id FROM shares_counted WHERE blocknumber <= " .$block. " AND blocknumber >= ".$l_bound."".
					 " )a");

	if ($totalRoundSharesR = db_fetch_object($totalRoundSharesQ)) {
		$totalRoundShares = $totalRoundSharesR->id;

		$userListCountQ = db_query("SELECT userid, sum(id) as id FROM ( ".
						  "SELECT DISTINCT userid, sum(count) as id FROM shares_uncounted WHERE blocknumber <= ".$block." AND blocknumber >= ".$l_bound." GROUP BY userid ".
						  "UNION DISTINCT SELECT userid, sum(count) as id FROM shares_counted WHERE blocknumber <= " .$block. " AND blocknumber >= ".$l_bound." GROUP BY userid ".
						 " )a GROUP BY userid");
		echo($sql."\n");
		while ($userListCountR = db_fetch_object($userListCountQ)) {
			$userInfoR = db_fetch_object(db_query("SELECT DISTINCT username, donate_percent FROM webusers WHERE id = " .$userListCountR->userid));

			$username = $userInfoR->username;
			$uncountedShares = $userListCountR->id;
			$shareRatio = $uncountedShares/$totalRoundShares;
			$ownerId = $userListCountR->userid;
			$donatePercent = $userInfoR->donate_percent;

			//Take out site percent unless user is of early adopter account type
            $account_type = account_type($ownerId);
            if ($account_type == 0) {
				// is normal account
				$predonateAmount = (1-$f)*($block_reward*$shareRatio);
				$predonateAmount = rtrim(sprintf("%f",$predonateAmount ),"0");
				$totalReward = $predonateAmount - ($predonateAmount * ($sitePercent/100));
			} else {
				// is early adopter round 1 0% lifetime fees
				$predonateAmount = 0.9999*($block_reward*$shareRatio);
				$predonateAmount = rtrim(sprintf("%f",$predonateAmount ),"0");
				$totalReward = $predonateAmount;
			}

			if ($predonateAmount > 0.00000001)	{

				//Take out donation
				$totalReward = $totalReward - ($totalReward * ($donatePercent/100));

				//Round Down to 8 digits
				$totalReward = $totalReward * 100000000;
				$totalReward = floor($totalReward);
				$totalReward = $totalReward/100000000;

				//Get total site reward
				$donateAmount = round(($predonateAmount - $totalReward), 8);

				$overallReward += $totalReward;

				//Update account balance & site ledger
				db_query("UPDATE accountbalance SET balance = balance + ".$totalReward." WHERE userid = ".$ownerId);

				db_query("INSERT INTO ledger (userid, transtype, amount, feeamount, assocblock) ".
					    " VALUES ".
					    "($ownerId, 'Credit', $totalReward, $donateAmount, $block)");
			}
			db_query("UPDATE shares_uncounted SET counted = 1 WHERE userid='".$ownerId."' AND blocknumber <= ".$block);
		}
		// update site wallet with our reward from this block
		if (isset($B)) {
		 $poolReward = $B -$overallReward;
		}
		//mysql_query("UPDATE settings SET value = value +".$poolReward." WHERE setting='sitebalance'");
		mv_uncountedToCounted();
	}
}

function mv_uncountedToCounted() {
	// clean counted shares_uncounted and move to shares_counted
	$sql = "SELECT DISTINCT * FROM shares_uncounted WHERE counted=1";

	$sharesQ = db_query($sql);
	$i = 0;
	//$maxId = 0;
	$shareInputSql = "";

	while ($sharesR = db_fetch_object($sharesQ)) {
		//if ($sharesR->maxId > $maxId)
		//	$maxId = $sharesR->maxId;
		if ($i == 0) {
			$shareInputSql = "INSERT INTO shares_counted (blocknumber, userid, count, invalid, counted, score) VALUES ";
		}
		if ($i > 0) {
			$shareInputSql .= ",";
		}
		$i++;
		$shareInputSql .= "($sharesR->blocknumber,$sharesR->userid,$sharesR->count,$sharesR->invalid,$sharesR->counted,$sharesR->score)";
		if ($i > 20)
		{
			db_query($shareInputSql);
			$shareInputSql = "";
			$i = 0;
		}
	}

	// if not empty, Insert
	echo($shareInputSql."\n");
	if (strlen($shareInputSql) > 0)
		db_query($shareInputSql);

	//Remove counted shares from shares_uncounted (this should empty it completely or something went wrong.
	db_query("DELETE FROM shares_uncounted WHERE counted=1");
}

?>

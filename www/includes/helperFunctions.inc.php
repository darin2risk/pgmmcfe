<?php
require_once("settings.php");
function estimate_user_rewards($block = NULL, $uid = NULL) {
   global $userReward, $donation, $block_reward,$settings;
   
    $block_reward=$settings->getsetting('block_reward');//100;
	//$lastNshares=400000;
	$lastNshares=$settings->getsetting('share_window');
	//Get site percentage
	$sitePercent = 0;
	$sitePercentQ = db_query("SELECT value FROM settings WHERE setting='sitepercent'");
	if ($sitePercentR = db_fetch_object($sitePercentQ)) $sitePercent = $sitePercentR->value;

	$overallReward = 0;

	// score vars
	$f = 1;
	$f = $sitePercent / 100;

	// LastNshares - determine block number lower boundary where N shares is met
	$l_bound = 0;
	$total = 0;
	if(!isset($lastNshares)) { $lastNshares = 1000000; }

	$sql = db_query("SELECT blocknumber, count FROM ( ".
				"SELECT blocknumber, count FROM shares_uncounted WHERE blocknumber <= " .$block. " ".
				"UNION SELECT blocknumber, count FROM shares_counted WHERE blocknumber <= " .$block. " AND blocknumber > ".($block - 1000)." ".
			   ")a ORDER BY blocknumber DESC");

	while ($result = db_fetch_object($sql)) {

		// increment $total with each row returned
		$total = $total + $result->count;
		
		// if $lastNshares criteria is met, and $l_bound is not our whole count, set everything below $l_bound as counted = 1
		if ($total >= $lastNshares) {
			$l_bound = $result->blocknumber;

			if ($l_bound < $block) {
				// db_query("UPDATE shares_uncounted SET counted = 1 WHERE blockNumber < ".$l_bound);
			}
			break;
		}else{
			$l_bound = $result->blocknumber;
		}
		
	}

	$totalRoundSharesQ = db_query("SELECT sum(id) as id FROM ( ".
					  "SELECT sum(count) as id FROM shares_uncounted WHERE blocknumber <= ".$block." AND blocknumber >= ".$l_bound." ".
					  "UNION SELECT sum(count) as id FROM shares_counted WHERE blocknumber <= " .$block. " AND blocknumber >= ".$l_bound."".
					 " )a");

	if ($totalRoundSharesR = db_fetch_object($totalRoundSharesQ)) {
		$totalRoundShares = $totalRoundSharesR->id;

		$userListCountQ = db_query("SELECT userid, sum(id) as id FROM ( ".
						  "SELECT DISTINCT userid, sum(count) as id FROM shares_uncounted WHERE blocknumber <= ".$block." AND blocknumber >= ".$l_bound." AND userid = ".$uid." GROUP BY userid ".
						  "UNION DISTINCT SELECT userid, sum(count) as id FROM shares_counted WHERE blocknumber <= " .$block. " AND blocknumber >= ".$l_bound." AND userid = ".$uid." GROUP BY userid ".
						 " )a GROUP BY userid");

		while ($userListCountR = db_fetch_object($userListCountQ)) {
			$userInfoR = db_fetch_object(db_query("SELECT DISTINCT username, donate_percent FROM webusers WHERE id = '" .$userListCountR->userid. "'"));

			$username = $userInfoR->username;
			$uncountedShares = $userListCountR->id;
			$shareRatio = $uncountedShares/$totalRoundShares;
			$ownerId = $userListCountR->userid;
			$donatePercent = $userInfoR->donate_percent;

			//Take out site percent unless user is of early adopter account type
			$account_type = account_type($ownerId);
			if ($account_type == 0) {
				// is normal account
				$predonateAmount = (1-$f)*( $block_reward * $shareRatio);
				$predonateAmount = rtrim(sprintf("%f",$predonateAmount ),"0");
				$totalReward = $predonateAmount - ($predonateAmount * ($sitePercent/100));
			} else {
				// is early adopter round 1 0% lifetime fees
				$predonateAmount = 0.9999*( $block_reward * $shareRatio);
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
				$userReward = number_format($totalReward, 8, '.', '');
				$donation = number_format($donateAmount, 8, '.', '');

				if($uid != NULL){
					if($uid==$ownerId)
						echo "<tr><td>" .$block. "</td><td>" .$userReward. "</td><td>" .$uncountedShares. "</td><td>" .$donation. "</td><td>$l_bound</td></tr>";
				}else{
					echo "<tr><td>" .$block. "</td><td>" .$userReward. "</td><td>" .$uncountedShares. "</td><td>" .$donation. "</td><td>$l_bound</td></tr>";
				}
			}
		}
	}
}

?>

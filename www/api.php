<?php
//if (!isset($_GET["api_key"]))
//	exit;

//if ($_SERVER["REMOTE_ADDR"] == "1.1.1.1") { die("access blocked because you are requesting this data too often.  Please request this data only once per minute and contact annihilat@papa.mainframe.nl when you have fixed it. ."); }

$includeDirectory = "<set your include directory>/includes/";

include($includeDirectory."requiredFunctions.php");
$nConfirms=$settings->getsetting('num_confirms');
	class User {
		var $username = null;
		var $confirmed_rewards = null;
		var $round_estimate = null;
		var $total_hashrate = null;
		var $payout_history = null;
		var $round_shares = null;
		var $workers = array();
	}

	class Worker {
		var $alive = null;
		var $hashrate = null;
		var $last_share_timestamp = null;
	}

	class Server {
		var $pool_name = null;
		var $hashrate = null;
		var $workers = null;
		var $shares_this_round = null;
		var $last_block = null;
	}

	$userid = NULL;

connectToDb();

if (!empty($_GET["api_key"])) {

	$apikey = db_real_escape_string($_GET["api_key"]);
	$user = new User();

	$resultU = db_query("SELECT u.id, u.username, u.hashrate, u.round_estimate, u.shares_this_round, b.balance, b.paid from webusers u, accountbalance b ".
			       "WHERE u.id = b.userid AND u.api_key='".$apikey."'");
	if ($userobj = db_fetch_object($resultU)){
		$userid = $userobj->id;
		$user->username = $userobj->username;
		$user->round_estimate = $userobj->round_estimate;
		$user->confirmed_rewards = $userobj->balance;
		$user->total_hashrate = $userobj->hashrate;
		$user->round_shares = $userobj->shares_this_round;
		$user->payout_history = $userobj->paid;
	}

	$resultW = db_query("SELECT username, hashrate, active FROM pool_worker WHERE associateduserid=".$userid);

	if (!$resultW) { die("Invalid Key."); }

	while ($workerobj = db_fetch_object($resultW)) {
		$worker = new Worker();
		$worker->alive = $workerobj->active;
		$worker->hashrate = $workerobj->hashrate;

		// if worker is active return the timestamp of last submitted share.
		$result_ls = db_fetch_object(db_query("SELECT unix_timestamp(time) as last_share_timestamp FROM shares WHERE username = '" .$workerobj->username. "' ORDER BY time DESC LIMIT 1"));
		if (!$result_ls) {
			unset($worker->last_share_timestamp);
		} else {
			$worker->last_share_timestamp = $result_ls->last_share_timestamp;
		}

		$user->workers[$workerobj->username] = $worker;
	}

	echo json_encode($user);

	// Debug
	//echo json_encode($workers);

} else {
	// Give out server stats
	$server = new Server();
	$server->pool_name = $settings->getsetting("websitename");
	$server->hashrate = $settings->getsetting("currenthashrate");
	$server->workers = $settings->getsetting("currentworkers");

	// calculate number of shares towards next block (traditional round shares calculation)
	$pending_sharesQ = db_fetch_object(db_query("SELECT sum(sharecount) as count FROM winning_shares WHERE blocknumber >= (".
							  "SELECT blocknumber FROM networkblocks WHERE confirms != 0 AND confirms < ".$nConfirms .
							   "ORDER BY blocknumber ASC LIMIT 1)"));
	$pending_shares = $pending_sharesQ->count;
	if ($pending_shares) {
		$server->shares_this_round = ($settings->getsetting("currentroundshares") - $pending_shares);
	} else {
		$server->shares_this_round = $settings->getsetting("currentroundshares");
	}

	// last winning block
	$lastBlockQ = db_fetch_object(db_query("SELECT blocknumber FROM winning_shares ORDER BY blocknumber DESC LIMIT 1"));
	$lastBlock = $lastBlockQ->blocknumber;
	$server->last_block = $lastBlock;
	//echo $lastBlock;
	echo json_encode($server);
}

?>

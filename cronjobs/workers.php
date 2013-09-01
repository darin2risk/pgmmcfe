<?php

$includeDirectory = "[path to includes]/includes/";
include($includeDirectory."requiredFunctions.php");

$block_reward = $settings->getsetting('block_reward');
//Update workers
$litecoinController = new LitecoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);

//Get difficulty
$difficulty = $litecoinController->query("getdifficulty");
//$difficulty = '1';

//Get site percentage fee
$sitePercent = 0;
$sitePercentQ = db_query("SELECT value FROM settings WHERE setting='sitepercent'");
if ($sitePercentR = db_fetch_object($sitePercentQ)) $sitePercent = $sitePercentR->value;

// set up some scoring variables
$c = .00000001;
$f = $sitePercent / 100;
$p = 1.0/$difficulty;
$r = log(1.0-$p+$p/$c);
$B = $block_reward;
$los = log(1/(exp($r)-1));

// Check for if worker is active (submitted shares in the last 10 mins)
$currentWorkers = 0;
try {
	$sql ="SELECT sum(a.id) IS NOT NULL AS active, p.username, p.was_active, p.notify_down, p.email ".
			"FROM pool_worker p LEFT JOIN ".
			"(SELECT count(id) AS id, username, '' ,'' ,'' FROM shares WHERE time > (now() -  INTERVAL '1.0 MINUTE') group by username ".
			"UNION ".
			"SELECT count(id) AS id, username,'','','' FROM shares_history WHERE time > (now() - INTERVAL '1.0 MINUTE') group by username) a ON p.username=a.username group by p.username,p.was_active,p.email,p.notify_down";
	$result = db_query($sql);
	while ($resultObj = db_fetch_object($result)) {
	    echo("DEBUG: \$resultObj->active == ".$resultObj->active."\n");
		if ($resultObj->active == "t"){
		    echo("DEBUG: Adding a worker...\n");
			$currentWorkers += 1;
        }
		if($resultObj->was_active == "t"){ $wasactive = "true";}else{ $wasactive = "false";}
        if($resultObj->active == "t")	 { $isactive = "true"; }else{ $isactive = "false";}
		if($resultObj->notify_down =="t"){ $notify = "true";   }else{ $notify = "false";} 
        if($isactive == "true" ){ echo("Active: true\n"); }else{ echo("Active: false\n"); }
		if($isactive == "false" && $wasactive == "true" && $notify == "true"){
			echo("Downed worker Detected....");
			// trigger notification
			$email=$resultObj->email;
			$worker=$resultObj->username;
			notify_down($email,$worker);
			// update was_active flag to false
			$sql="UPDATE pool_worker p SET active = false, was_active = false WHERE username='".$resultObj->username."'";
			db_query($sql);
		}else if($isactive == "false" && $wasactive == "true"){
			db_query("UPDATE pool_worker p SET active = false, was_active = false WHERE username='".$resultObj->username."'");
		}
		
		if($isactive == "true"){
			db_query("UPDATE pool_worker p SET active= true, was_active = true WHERE username='".$resultObj->username."'");
		}
		
	}

	// Update number of workers in our pool status
	$settings->setsetting('currentworkers', $currentWorkers);

} catch (Exception $e) {}


// Calculate estimated round earnings for each user

//Proportional estimate
$totalRoundShares = $settings->getsetting("currentroundshares");
if($totalRoundShares <= 0 ){ $totalRoundShares = 1; }

//if ($totalRoundShares < $difficulty) $totalRoundShares = $difficulty;
$q="UPDATE webusers SET round_estimate = round((1-".$f.")*".$block_reward."*((shares_this_round::numeric)/".$totalRoundShares.")*(1-((donate_percent::numeric)/100)), 8)";
echo("DEBUG: ".$q."\n");
db_query($q);

// comment the one line below out if you want to disable 0% fees for first 35 users
db_query("UPDATE webusers SET round_estimate = round(0.9999*".$block_reward."*((shares_this_round::numeric)/".$totalRoundShares.")*(1-((donate_percent::numeric)/100)), 8) WHERE account_type = '9'");


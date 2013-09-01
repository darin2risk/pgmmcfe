<?php

include ("includes/templates/header.php");

$goodMessage = "";
$returnError = "";
$userTable = "";
$nConfirm=$settings->getsetting('num_confirms');
$block_reward=$settings->getsetting('block_reward');

//Since this is the Admin panel we'll make sure the user is logged in and "isAdmin" enabled boolean; If this is not a logged in
// user that is enabled as admin, redirect to a 404 error page
if(!$cookieValid || $isAdmin != 1) {
	header('Location: /');
	exit;
}

if (isset($_POST["act"]))
{
	$act = db_real_escape_string($_POST["act"]);
	if (isset($_POST["authPin"])) { $inputAuthPin = db_real_escape_string(hash("sha256", $_POST["authPin"].$salt)); } else { $inputAuthPin = ""; }
	//Make sure an authPin is set and valid when $act is active
	if($act) {
		// Site Admin General Settings (pin needed)
		if($act == "UpdateMainPageSettings" && $authPin == $inputAuthPin) {
			try {
				$settings->setsetting("sitepayoutaddress", db_real_escape_string($_POST["paymentAddress"]));
				$settings->setsetting("sitepercent", db_real_escape_string($_POST["percentageFee"]));
				$settings->setsetting("websitename", db_real_escape_string($_POST["headerTitle"]));
				$settings->setsetting("pagetitle", db_real_escape_string($_POST["pageTitle"]));
				$settings->setsetting("slogan", db_real_escape_string($_POST["headerSlogan"]));
				$settings->setsetting("siterewardtype", db_real_escape_string($_POST["rewardType"]));
				$settings->setsetting("transactionfee", db_real_escape_string($_POST["transactionFee"]));
				$settings->setsetting("autopaythreshold", db_real_escape_string($_POST["autopayThreshold"]));
				$settings->setsetting("share_window", db_real_escape_string($_POST["share_window"]));
				$settings->loadsettings(); //refresh settings
				$goodMessage = "Successfully updated general settings";
			} catch (Exception $e) {
				$returnError = "Database Failed - General settings was not updated";
			}
		} else {
			if($act == "UpdateMainPageSettings" && $authPin != $inputAuthPin) {
				$returnError = "Authorization Pin Invalid or not entered.";
			}
		}

		// User Control (no pin needed)
		if(($act == "userControl") && (empty($_POST["searchStr"]))) {

			$returnError = "No search string specified.";
			$num_results = "0";

		} else if(($act == "userControl") && (!empty($_POST["searchStr"]))) {

			if (isset($_POST["searchStr"])) { $searchStr = db_real_escape_string($_POST["searchStr"]); }

			if (is_numeric($searchStr)) {
				$search_resultsQ = db_query("SELECT * FROM webusers WHERE id = " .$searchStr. " ORDER BY round_estimate DESC LIMIT 1");
			} else {
				$search_resultsQ = db_query("SELECT * FROM webusers WHERE username LIKE '%" .$searchStr. "%' OR loggedip LIKE '%" .$searchStr. "%'".
							       " ORDER BY round_estimate DESC");
			}

			$search_results = "";

			$count = 1;
			$search_id = NULL;
			while ($row = db_fetch_array($search_resultsQ, PGSQL_ASSOC)) {

				$search_results .= "<tr style='background-color:#fff;'><td>" .$row['id']. "</td><td>" .$row['username']. "</td><td>" .round($row['round_estimate'], 4).
						   "</td><td class='row_email' style='display:none;'>" .$row['loggedip']."</td><td class='row_email' style='display:none;'>" .$row['email']. "</td><td>" .$row['share_count'].
						   "</td><td>" .$row["stale_share_count"]. "</td><td>" .$row['shares_this_round'].
						   "</td><td>" .$row["donate_percent"]. "</td><td>" .round(($row["hashrate"] / 1024), 2). "</td>".
						   "<td>" .@round(($row['stale_share_count'] / $row['share_count'] * 100), 2). "</td></tr>";
				$count++;
				$search_id = $row["id"];
			}

			$num_results = ($count - 1);
		}
		
		if($act == "GenerateToken"){

			$token = db_real_escape_string($_POST['token']);
			$user = db_real_escape_string($_POST['user']);
			if($token == ""){
				$returnError = "You must provide a token.";
			}else{
				$sql="INSERT INTO invite_tokens(token, token_from, date_generated) VALUES ( '".$token."', '".$user."', now() )";
				$res=db_query($sql);
				if($res == false)
					$returnError="Unable to write token to Database.";
				else
					$goodMessage="Created token: $token from $user";

			}
			
		}
	}
}


?>

<div class="block withsidebar">

<?include("includes/templates/headerbar.php");?>

        <div class="block_content">

                <div class="sidebar">
                        <?php include ("includes/leftsidebar.php"); ?>
                </div>          <!-- .sidebar ends -->


                <div class="sidebar_content" id="sb1">


<?php
//Display Error and Good Messages(If Any)
if ($goodMessage) { echo "<div class=\"message success\"><p>".antiXss($goodMessage)."</p></div>"; }
if ($returnError) { echo "<div class=\"message errormsg\"><p>".antiXss($returnError)."</p></div>"; }
?>

<div id="AdminContainer">
                <div class="block" style="clear:none;">
                 <div class="block_head">
                  <div class="bheadl"></div>
                  <div class="bheadr"></div>
			<h1>Site Admin General Settings</h1>
                </div>

                <div class="block_content" style="padding:10px;">

		<!--Begin main page edits-->
		<form action="/adminPanel" method="post">
			<input type="hidden" name="act" value="UpdateMainPageSettings">
			<table>
			<tr><td>Page Title</td><td><input type="text" name="pageTitle" size="50" value="<?php echo antiXss($settings->getsetting("pagetitle"));?>"></td></tr>
			<tr><td>Header Title</td><td> <input type="text" name="headerTitle" size="50" value="<?php echo antiXss($settings->getsetting("websitename"));?>"></td></tr>
			<tr><td>Header Slogan</td><td> <input type="text" name="headerSlogan" size="50" value="<?php echo antiXss($settings->getsetting("slogan"));?>"></td></tr>
			<tr><td>Percentage Fee</td><td> <input type="text" name="percentageFee" size="5" maxlength="10" value="<?php echo antiXss($settings->getsetting("sitepercent")); ?>">%</td></tr>
			<tr><td>Fee Address</td><td> <input type="text" name="paymentAddress" size="50" value="<?php echo antiXss($settings->getsetting("sitepayoutaddress")); ?>"></td></tr>
			<tr><td>Transaction Fee*</td><td> <input type="text" name="transactionFee" size="10" value=<?php echo antiXss($settings->getsetting("transactionfee")); ?>"></td></tr>
			<tr><td>Autopay Threshold</td><td> <input type="text" name="autopayThreshold" size="10" value="<?php echo antiXss($settings->getsetting("autopaythreshold")); ?>"></td></tr>
			<tr><td>Default Reward Type</td><td> <select name="rewardType" size="1">
				<option value="1" <?php if ($settings->getsetting("siterewardtype") == 1) echo "selected"; ?>>PPLNS</option></td></tr>
			</select>
			<tr><td>PPLNS Window</td><td><input type="text" name="share_window" size="10" value="<?php echo antiXss($settings->getsetting("share_window")); ?>"></td></tr>
			<tr><td>Authorization Pin</td><td><input type="password" size="4" maxlength="4" name="authPin"></td></tr>
			</table>
			<input type="submit" class="" value=" Update Site Settings ">
			
		</form>
                </div>          <!-- nested block ends -->
                <div class="bendl"></div>
                <div class="bendr"></div>
			</div>
			<div class="block" style="clear:none;">
				<div class="block_head">
                  <div class="bheadl"></div>
                  <div class="bheadr"></div>
					<h1>Generate Token</h1>
				</div>

					<div class="block_content" style="padding:10px;">
						<form action="/adminPanel" method="POST">
							<input type="hidden" name="act" value="GenerateToken">
							<table>
								<tr><td>Token: </td><td><input type="text" size="15" name="token" value=""/></td></tr>
								<tr><td> From: </td><td><input type="text" size="15" name="user" value="lavajumper"/></td></tr>
							</table>
							<input type="submit" class="" value=" Generate This Token "/> 
						</form>
					</div>
				<div class="bendl"></div>
				<div class="bendr"></div>
				
			</div>



                <div class="block" style="clear:none;">
                 <div class="block_head">
                  <div class="bheadl"></div>
                  <div class="bheadr"></div>
			<h1>Financial Statistics</h1>
                </div>

                <div class="block_content" style="padding:10px;">
	<?php

	$litecoinController = new LitecoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);

	//$sitewallet = db_query("SELECT sum(balance) FROM `accountBalance` WHERE `balance` > 0") or sqlerr(__FILE__, __LINE__);
	$sitewallet = db_query("SELECT sum(balance) FROM accountbalance") or sqlerr(__FILE__, __LINE__);
	$sitewalletq = db_fetch_row($sitewallet);

	$unconf_blocksQ = db_query("SELECT DISTINCT confirms from networkblocks WHERE confirms < ".$nConfirms." AND confirms > 0");
	$sitePercentQ = db_query("SELECT value FROM settings WHERE setting='sitepercent'");

	if ($sitePercentR = db_fetch_object($sitePercentQ)) {
	$sitePercent = $sitePercentR->value;
	}

	$unconf_blocks = db_num_rows($unconf_blocksQ);
	$unconf_income = ($unconf_blocks * ($block_reward * ($sitePercent / 100)));
	//$unconf_income = $unconf_blocks * $block_reward ;
	$usersbalance = $sitewalletq[0] / 1;
	$user_reserve = ($unconf_blocks);
	$donation_reserve = $settings->getsetting("tobedonated");
	$balance = $litecoinController->query("getbalance");
	$subtotal = $balance - $usersbalance - $donation_reserve;


	echo "Current Block Number: ".$litecoinController->getblockcount()."<br>";
	echo "Current Difficulty: ".$litecoinController->query("getdifficulty")."<br>";

	// Enable this for pool efficiency stats to be shown (useless stat imo)
	
	//$results = db_query("SELECT (1 - (SUM(stale_share_count)/SUM(share_count))) * 100 AS efficiency FROM webusers") or sqlerr(__FILE__, __LINE__);
	//$row = db_fetch_object($results);
	//echo "Pool Efficiency: ". number_format($row->efficiency, 2) . "%";
	echo "<br><br>";

	echo "Wallet Balance: ".$balance."<br>";
	echo "Held for Users: ".$usersbalance."<br>";
	echo "Held for Donation: ".$donation_reserve."<br>";
	echo "Immature Blocks: " .$user_reserve. "<br>";
	echo "Pool Immature: " .$unconf_income. " :: ".$unconf_blocks. " :: " . $sitePercent. "<br>";
	echo "Block Reward: " .$block_reward. "<br>";
	echo "<br>";
	echo "Actual Liquid Assets: $subtotal<br>";
	//echo "Forecasted Assets: " .($subtotal + $unconf_income). "<br>";

	?>
        </div>          <!-- nested block ends -->

        <div class="bendl"></div>
        <div class="bendr"></div>
       </div>






                 <div class="block" style="clear:none;">
                 <div class="block_head">
                  <div class="bheadl"></div>
                  <div class="bheadr"></div>
			<h1>User Control</h1>
                 </div>

                 <div class="block_content" style="padding:10px;">

	<ul><li><font color="orange">Search by IP Address, Userame, or UserId</font></li></ul>
	<form action="/adminPanel" method="post">
		<input type="hidden" name="act" value="userControl">
		Search String &nbsp;
		<input type="text" name="searchStr" value="%">
		<input type="submit" value=" Search ">
	</form>
	<br>

	<?php if($num_results) { echo "<font size='1'>" .$num_results. " result(s)</font>"; }?>
	<?php //if($num_results) { echo $num_results. " result(s)"; }?>

	<div id="search_infobox">
		<div class="search_results">
			<table width="100%" border="0" style="font-size:13px;" class="sortable">
			<thead>
			<tr style='background-color:#B6DAFF; font-size:10px;'>
				<th onclick="$('.row_email').toggle();"><font color="blue">Show/Hide</font></th>
				<th>User</th>
				<th>Est.</th>
				<th class="row_email" style="display:none;">IP</th>
				<th class="row_email" style="display:none;" onclick="$('.row_email').toggle();">Email</th>
				<th>Tot.</th>
				<th>Stale</th>
				<th>Round</th>
				<th>Don. %</th>
				<th>Kh/s</th>
				<th>Stale %</th>
			</tr>
			</thead>

			<tbody>
			<?php
			if (!empty($search_results)) {
				print "<script>$('#search_infobox').show();</script>";
				print $search_results;
			}
			?>
			</tbody>
			</table>
		</div>

		<div id="item_results"><?php print $item_results; ?></div>

	</div>

	<!-- payment history / transaction log -->

	<div id="generic_infobo" class="tx_log">
	<?php
	if ((isset($search_id)) && (is_numeric($search_id))) {
		$search_id_to_userObj = db_fetch_object(db_query("SELECT username FROM webusers WHERE id = " . $search_id));
	}
	?>
	<br>
	<h1><?php if ($num_results == 1) { echo $search_id_to_userObj->username; } ?> (account mutations)</h1>

	<ul><li><font color="">(ATP = Auto Threshold Payment, MP = Manual Payment)</font></li></ul>

		<table cellpadding="1" cellspacing="1" width="100%" class="sortable">
		<thead style="font-size:13px;">
			<tr>
			<th>TX #</th>
			<th>Date</th>
			<th>TX Type</th>
			<th>Payment Address</th>
			<th>Block #</th>
			<th>Amount</th>
			</tr>
		</thead>

		<tbody>
	        <?php
	        if (($num_results == 1) && (!is_null($search_id))) {
        	        $sql = db_query("SELECT * FROM ledger where userid = " .$search_id. " ORDER BY timestamp DESC");

	                while ($obj = db_fetch_object($sql)) {

	                        $mutation = explode(".", $obj->amount);

	                        if ($obj->transtype == "Debit_ATP" || $obj->transtype == "Debit_MP") {
	                                $obj->amount = "<font color='red'>-".$obj->amount."</font>";
					//echo "<tr style='background-color:orange;'>";
	                        } else {
	                                $obj->amount = "&nbsp;<font color='green'>".$obj->amount."</font>";
					//echo "<tr style='background-color:#99EB99;'>";
	                        }
				if ($obj->assocblock == "0") { $obj->assocblock = ''; }

				$feeAmount = number_format($obj->feeamount, 8, '.', '');
				if ($feeAmount > 0.0000001) {
					printf("<td><font size='1'>%s</td><td><font size='1'>%s</td><td><font size='2'>Don_Fee</td><td><font size='1'>&nbsp;</font></td><td><font size='2'>%s</td><td align=''><font size='2' color='orange'>-%s</td></tr>".
	                                	"", 10000+$obj->id, $obj->timestamp, $obj->assocblock, $feeAmount);
				}

				printf("<td><font size='1'>%s</td><td><font size='1'>%s</td><td><font size='2'>%s</td><td><font size='1'>%s</font></td><td><font size='2'>%s</td><td align=''><font size='2'>%s</td></tr>".
	                                "", 10000+$obj->id, $obj->timestamp, $obj->transtype, $obj->sendaddress, $obj->assocblock, $obj->amount);
	                }

	        } else {
                	echo "<script>$('.tx_log').toggle();</script>";
	        }
		?>
		</tbody>
		</table>

	</div>
                </div>          <!-- nested block ends -->
                <div class="bendl"></div>
                <div class="bendr"></div>
                </div>


</div>
                </div>          <!-- .sidebar_content ends -->


        </div>          <!-- .block_content ends -->




        <div class="bendl"></div>
        <div class="bendr"></div>

</div>          <!-- .block ends -->

<?php include ("includes/templates/footer.php"); ?>

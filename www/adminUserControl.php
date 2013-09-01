<?php

include ("includes/templates/header.php");
//include("includes/userStatsAuth.inc.php");

// Interval in Hours for charts
$interval = "20";

$goodMessage = "";
$returnError = "";
$userTable = "";

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
						   "</td><td>" .$row["donate_percent"]. "</td><td>" .round(($row['hashrate'] / 1000), 2). "</td>".
						   "<td>" .@round(($row['stale_share_count'] / $row['share_count'] * 100), 2). "</td></tr>";
				$count++;
				$search_id = $row["id"];
			}

			$num_results = ($count - 1);
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

// check if $searchStr is numeric so we can look at users charts
if (is_numeric($searchStr)) { $this_userid = $searchStr; } else { $this_userid = 0; }
?>

<div id="AdminContainer">

                 <div class="block" style="clear:none;">
                 <div class="block_head">
                  <div class="bheadl"></div>
                  <div class="bheadr"></div>
			<h2>User Control</h2>
                 </div>

                 <div class="block_content" style="padding:10px;">

	<ul><li><font color="orange">Search by IP Address, Userame, or UserId</font></li></ul>
	<form action="/adminUserControl" method="post">
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
				<th>MH/s</th>
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
        	        $res = db_query("SELECT * FROM ledger where userid = " .$search_id. " ORDER BY timestamp DESC");

	                while ($obj = db_fetch_object($res)) {

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
				$id=$obj->id;
				$t=$obj->timestamp;
				$tt=$obj->transtype;
				$sa=$obj->sendaddress;
				$am=$obj->amount;
				$block=$obj->asssocblock;
				
				if ($feeAmount > 0.0000001) {
					echo("<td><font size='1'>$id--</td><td><font size='1'>$t</td><td><font size='2'>Don_Fee</td><td><font size='1'>&nbsp;</font></td><td><font size='2'>$block</td><td align=''><font size='2' color='orange'>-$feeAmount</td></tr>");
//	                                	"", 10000+$obj->id, $obj->timestamp, $obj->assocblock, $feeAmount);
				}

				echo("<td><font size='1'>$id**</td><td><font size='1'>$t</td><td><font size='2'>$tt</td><td><font size='1'>$sa</font></td><td><font size='2'>$block</td><td align=''><font size='2'>$am</td></tr>");
	            //                    "", 10000+$obj->id, $obj->timestamp, $obj->transtype, $obj->sendaddress, $obj->assocblock, $obj->amount);
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

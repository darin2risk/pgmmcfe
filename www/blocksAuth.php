<?php

	include("includes/templates/header.php");
	include("includes/blocksAuth.inc.php");

	// number of graph items to show
	$count = 75;

	// check if logged in
	if( !$cookieValid ){
	        header("Location: /stats");
	        exit();
	}

$last_no_blocks_found = 400;
//$nConfirms = 90;
$nConfirms = $settings->getsetting("num_confirms");
?>

<div class="block withsidebar">

<?include("includes/templates/headerbar.php");?>

        <div class="block_content">

                <div class="sidebar">

                        <?php include ("includes/leftsidebar.php"); ?>

                </div>          <!-- .sidebar ends -->

                <div class="sidebar_content">

	                <div class="block" style="clear:none;">

	<!-- POOL LUCK BLOCK -->
	                <div class="block_head">
	                	<div class="bheadl"></div>
	                	<div class="bheadr"></div>
	                	<h2>Pool Luck <font size="1">over last <?php print $count; ?> blocks</font></h2>

	                </div>	<!-- .block_head ends -->

		                <div id="" class="block_content" style="">

					<div id="chart" style="">
						<?php shares_per_block_new($count); ?>
					</div>

					<center>
					<p style="padding-left:30px; padding-right:30px; font-size:10px;">
					The graph above illustrates N shares to find a block vs. N Shares expected to find a block based on
					difficulty assuming a zero variance scenario. Additionally our average number of shares per block is displayed (effective difficulty/pool luck).
					</p></center>

		                </div>          <!-- nested block content ends -->

	                <div class="bendl"></div>
	                <div class="bendr"></div>
	                </div>

	<!-- LAST N BLOCKS FOUND STATS BLOCK -->

	                <div class="block" style="clear:none;">
	                <div class="block_head">
	                <div class="bheadl"></div>
	                <div class="bheadr"></div>
				<h2>Last <?php echo $last_no_blocks_found. " Blocks Found"; ?></h2>
	                </div>

	              	<div class="block_content" style="padding-left:5px;padding-right:5px;">
			<p>
			<?php
			// SHOW LAST (=$last_no_blocks_found) BLOCKS
			echo "<center><table class='sortable' width='100%' style='font-size:13px;'>";
			echo "<thead>";
			echo "<tr style='background-color:#B6DAFF;'><th scope=\"col\" align='left'>Block</th><th scope=\"col\" align='left'>Validity</th>".
			     "<th scope=\"col\" align='left'>Finder</th><th scope=\"col\" align='left'>Time</th> <th scope=\"col\" align='left'>Shares</th><th>Amount</th></tr>";
			echo "</thead>";

			//$result = db_query("SELECT DISTINCT n.blocknumber, n.confirms, n.timestamp FROM winning_shares w, networkblocks n WHERE w.blocknumber = n.blocknumber ORDER BY w.blocknumber DESC LIMIT " . $last_no_blocks_found);
			$result = db_query("SELECT n.blocknumber, n.confirms, n.timestamp, n.reward_amount FROM winning_shares w, networkblocks n WHERE w.blocknumber = n.blocknumber ORDER BY w.blocknumber DESC LIMIT " . $last_no_blocks_found);
			echo "<tbody>";

			while($resultrow = db_fetch_object($result)) {
				echo "<tr>";
				$resdss = db_query("SELECT username, sharecount FROM winning_shares WHERE blocknumber = $resultrow->blocknumber");
				$resdss = db_fetch_object($resdss);

				$splitUsername = explode(".", $resdss->username);
				$realUsername = $splitUsername[0];
				$shareCount = number_format($resdss->sharecount);
				$confirms = $resultrow->confirms;
				$reward = $resultrow->reward_amount;
				if($reward >= 500){ $reward = "<font color = red>$reward**</font>"; }

				if ((is_numeric($confirms)) && ($confirms !== "0")) {
					if ($confirms > $nConfirms-1) {
						$confirms = "<font color='green'>Confirmed!</font>";
					} else {
						$confirms = "<font color='grey'>".($nConfirms - $confirms)." left</font>";
					}
				} else {
					continue(0);
				}

				$block_no = $resultrow->blocknumber;

				echo "<td><a href=\"".$settings->getsetting('block_explorer_url')."/search?q=$block_no\">" . number_format($block_no) . "</a></td>";
				echo "<td>" . $confirms . "</td>";
				echo "<td>$realUsername</td>";
				echo "<td>".strftime("%F %r",$resultrow->timestamp)."</td>";

				if ($shareCount <= 0) { $shareCount = "Updating..."; }
				echo "<td>$shareCount</td>";
				echo "<td>$reward</td>";
				echo "</tr>";
			}

			echo "</tbody>";
			echo "</table>";

			echo "</center><ul><li>Note: <font color='orange'>Round Earnings are not credited until $nConfirms confirms.</font></li></ul>";
			?>

			</p>
	                </div>          <!-- nested block ends -->
	                <div class="bendl"></div>
	                <div class="bendr"></div>
	                </div>

                </div>          <!-- .sidebar_content ends -->

        </div>          <!-- .block_content ends -->

        <div class="bendl"></div>
        <div class="bendr"></div>

</div>          <!-- .block ends -->


<?php include("includes/templates/footer.php"); ?>

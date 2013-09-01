<div class="block withsidebar">

	<div class="block_head">
		<div class="bheadl"></div>
		<div class="bheadr"></div>

		<h2>Welcome,
		<?php
		if($cookieValid) {

			echo $userInfo->username . " ";

                        $account_type = 0;
                        $account_type = account_type($userInfo->id);

                        if ($account_type == 9) {
                                $account_type = "<b>Early-Adopter</b>: <b>0%</b> Pool Fee";
                        } else {
                                $account_type = "<b>Active Account</b>: <b>" .$settings->getsetting("sitepercent"). "%</b> Pool Fee";
                        }

			echo "<font size='1px'>" .$account_type."</font> ";
			echo "<font size='1px'><i>(You are <a href='/osList'>donating</a> <b></i>" .antiXss($donatePercent)."%</b> <i>of your earnings)</i></font>";
		} else {
			echo "Guest";
		}
		?>
		</h2>
	</div>		<!-- .block_head ends -->




	<div class="block_content">

		<div class="sidebar">

			<?php include ("includes/leftsidebar.php"); ?>

		</div>		<!-- .sidebar ends -->




		<div class="sidebar_content" id="sb1">

                <div class="block" style="clear:none;">
                 <div class="block_head">
                  <div class="bheadl"></div>
                  <div class="bheadr"></div>
                  <h2><?php echo $settings->getsetting("websitename"); ?></h2>
                 </div>
                 <div class="block_content" style="padding:10px;">

<img src="/images/litecoin.png"/><h3>Lavajumper's Mining Pool Frontend</h3>
<br/>
Welcome to PGmmcFE.<br/>

You probably want to edit this.<br/><br/>

<ul>
	<li>Downed Worker Notification</li>
	<li>Time Zone Support</li>
	<li>Uptime Graphs, Individual and Pool</li>
	<li>Auto or Manual Payouts</li>
	<li>PostgreSQL Database</li>
	<li>Accounting Grade Cross-Method Database Block tracking</li>
	<li>Idempotent Backend Jobs</li>
	<li>Variable Reward Support</li>
	<li>Adjustable PPLNS Window Support</li>
	<li>Variable "Confirm Count" Support</li>
	<li>Variable "Transaction Fee" Support</li>
	<li>Easily add new "Tickers"</li>
	<li>Adjustable Job Scheduling</li>
</ul> 

If you have any questions, complaints, suggestions or beer, you may contact me on bitcointalk.org. I'm lavajumper.<br/>

                </div>          <!-- nested block ends -->
                <div class="bendl"></div>
                <div class="bendr"></div>
                </div>

		</div>		<!-- .sidebar_content ends -->


	</div>		<!-- .block_content ends -->




	<div class="bendl"></div>
	<div class="bendr"></div>

</div>		<!-- .block ends -->

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
				$litecoinController=new LitecoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);
				$nethash = ($litecoinController->getnetworkhashps())/1000000;
				$difficulty = $litecoinController->getdifficulty();
                ?><!--
                </h2>
				<div class="bar_notifier" id="bar_notifier-diff">Difficulty: <?//=$difficulty?></div>
				<div class="bar_notifier" id="bar_notifier-nh">Network Hashrate: <?//=$nethash?> MH/s</div>
				-->
        </div>          <!-- .block_head ends -->
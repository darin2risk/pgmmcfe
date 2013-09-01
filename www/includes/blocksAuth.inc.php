<script type="text/javascript" src="js/highcharts.src.js"></script>
<script type="text/javascript" src="js/themes/mmcfe.js"></script>
<?php

$litecoinController = new LitecoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);
$difficulty = round($litecoinController->query("getdifficulty"));

function shares_per_block_new($count = 10) {
	global $difficulty, $settings;

        $numwonblocksQ = db_query("SELECT count(id) as count FROM winning_shares");
        $numwonblocksObj = db_fetch_object($numwonblocksQ);
        $avgsharesQ = db_query("SELECT sum(sharecount) / ".$numwonblocksObj->count." as avg FROM winning_shares");
        $avgsharesObj = db_fetch_object($avgsharesQ);
        $avgshares = $avgsharesObj->avg;

        $wonblocksQ = db_query("SELECT * FROM (SELECT blocknumber FROM winning_shares ORDER BY blocknumber DESC LIMIT ".$count.")b ORDER BY blocknumber ASC");
        $wonsharecountQ = db_query("SELECT * FROM (SELECT blocknumber, sharecount FROM winning_shares ORDER BY blocknumber DESC LIMIT ".$count.")s ORDER BY blocknumber ASC");
        $difficultyQ = db_query("SELECT * FROM (SELECT blocknumber, difficulty::numeric AS difficulty FROM networkblocks WHERE accountaddress != '' ORDER BY blocknumber DESC LIMIT ".$count.")nb ORDER BY blocknumber ASC");
	?>
		<script type="text/javascript">

			var chart;
			$(document).ready(function() {
				chart = new Highcharts.Chart({
					chart: {
						renderTo: 'chart',
						//defaultSeriesType: 'areaspline'
						defaultSeriesType: 'line',
						zoomType: 'y',
						height: 400,
						spacingRight: 35
					},
					title: {
						x: 32.5,
						text: 'Shares Per Block (actual, expected, average)'
					},
					subtitle: {
						x: 32.5,
						text: 'A visual representation'
					},
					xAxis: {
						categories: [

						<?php
					        while ($row = db_fetch_array($wonblocksQ, PGSQL_ASSOC)) {

							echo "'" .$row["blocknumber"]. "', ";

					        }
						?>

						],
						tickmarkPlacement: 'on',
						title: {
							enabled: false,
							text: 'Found Blocks'
						},
						labels: {
							enabled: 'false',
							rotation: -45,
							align: 'right',
							style: {
								 font: 'normal 8px Verdana, sans-serif',
								 display: 'none'
							}
						}
					},
					yAxis: {
						min: 0,
						title: {
							text: 'Number of Shares'
						},
						labels: {
							formatter: function() {
								return this.value / 1000 + 'K';
							}
						}
					},
					tooltip: {
						formatter: function() {
							return 'Block '+
								 this.x +': '+ Highcharts.numberFormat(this.y, 0, ',') +' shares';
						}
					},
					legend: {
						x: 32.5,
						enabled: true
					},
					plotOptions: {
						area: {
							stacking: 'normal',
							lineColor: '#666666',
							lineWidth: 1,
							marker: {
								lineWidth: 1,
								lineColor: '#666666'
							}
						}
					},
					series: [{
						type: 'spline',
						name: 'Actual Shares per Block',
						data: [

						<?php
						$blk_count = 1;
						$avg_arr = array();
					        while ($row = db_fetch_array($wonsharecountQ, PGSQL_ASSOC)) {
							echo "" .$row["sharecount"]. ", ";
							$total_shares += $row["sharecount"];
							array_push($avg_arr, ($total_shares / $blk_count));
							$blk_count++;
					        }
						?>

						]
					}, {
						type: 'line',
						name: 'Actual Difficulty',
						data: [

						<?php
					        while ($row = db_fetch_array($difficultyQ, PGSQL_ASSOC)) {
							echo "" .($row["difficulty"]*10000 ). ", "; // adjust this multiplyer for scaling on Difficulty
					        }
						?>

						]
					}, {
						type: 'spline',
						name: 'Effective Difficulty',
						data: [

						<?php
						foreach ($avg_arr as $avg_spb) {
							echo "" .$avg_spb. ", ";
						}
						?>

						]
					}]
				});

			});
	</script>

	<?php
}

?>


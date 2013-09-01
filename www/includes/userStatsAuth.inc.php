<script type="text/javascript" src="js/highcharts.src.js"></script>
<script type="text/javascript" src="js/themes/mmcfe.js"></script>

<?php

function hashGraphs($graph, $userId = NULL, $interval = "48", $type = NULL) {
 global $userInfo;

	if($graph == "mine") {

		$user24hhrQ = db_query("SELECT extract(epoch from timestamp ) as time, hashrate FROM userhashrates WHERE userid = ".$userId." AND timestamp > (now() - INTERVAL '".$interval." HOUR') ORDER BY timestamp ASC");

		?>
		<script type="text/javascript">

			var chart;
			$(document).ready(function() {
				chart = new Highcharts.Chart({
					chart: {
						renderTo: 'hashstats',
						zoomType: 'x',
						height: 360,
						spacingRight: 55
					},
				    title: {
						x: 27.5,
						text: 'My Hash Rate'
					},
				    subtitle: {
						x: 27.5,
						text: document.ontouchstart === undefined ?
							'Click and drag over a time period to zoom in' :
							'Drag your finger over the plot to zoom in'
					},
					xAxis: {
						type: 'datetime',
						maxZoom: 1 * 17280, // 1 minute ?
						title: {
							text: null
						}
					},
					yAxis: {
						title: {
							text: 'Khash / Sec'
						},
						min: 0.0,
						startOnTick: false,
						showFirstLabel: false
					},
					tooltip: {
						shared: true
					},
					legend: {
						enabled: false
					},
					plotOptions: {
						area: {
							fillColor: {
								linearGradient: [0, 0, 0, 300],
								stops: [
									[0, Highcharts.getOptions().colors[0]],
									[1, 'rgba(2,0,0,0)']
								]
							},
							lineWidth: 1,
							marker: {
								enabled: false,
								states: {
									hover: {
										enabled: true,
										radius: 5
									}
								}
							},
							shadow: false,
							states: {
								hover: {
									lineWidth: 1
								}
							}
						}
					},

					series: [{
						type: 'area',
						name: 'KHash/s',
						//pointInterval: 2 * 86400,
						//pointStart: Date.UTC(2011, 8, 04),
						data: [
							<?php
					                while ($row = db_fetch_array($user24hhrQ, PGSQL_ASSOC)) {
					                        //echo "" .$row["hashrate"]. ", ";
								echo "[Date.UTC(" .date('Y, m-1, d, G, i', $row["time"]). "), " .$row["hashrate"]. " ],\n\r";
                					}
							?>
						]
					}]
				});
			});

		</script>
	<?php
	}

	if($graph == "pool") {

		$user24hhrQ = db_query("SELECT extract(epoch from timestamp ) as time, hashrate FROM userhashrates WHERE userid = 0 AND timestamp > (now() - INTERVAL '".$interval." HOUR') ORDER BY timestamp ASC");

		?>

		<script type="text/javascript">

			var chart;
			$(document).ready(function() {
				chart = new Highcharts.Chart({
					chart: {
						renderTo: 'hashstats',
						zoomType: 'x',
						height: 360,
						spacingRight: 55
					},
				    title: {
						x: 27.5,
						text: 'Pool Hash Rate'
					},
				    subtitle: {
						x: 27.5,
						text: document.ontouchstart === undefined ?
							'Click and drag over a time period to zoom in' :
							'Drag your finger over the plot to zoom in'
					},
					xAxis: {
						type: 'datetime',
						maxZoom: 1 * 17280, // 1 minute ?
						title: {
							text: null
						}
					},
					yAxis: {
						title: {
							text: 'Mhash / Sec'
						},
						min: 0.0,
						startOnTick: false,
						showFirstLabel: false
					},
					tooltip: {
						shared: true
					},
					legend: {
						enabled: false
					},
					plotOptions: {
						area: {
							fillColor: {
								linearGradient: [0, 0, 0, 300],
								stops: [
									[0, Highcharts.getOptions().colors[0]],
									[1, 'rgba(2,0,0,0)']
								]
							},
							lineWidth: 1,
							marker: {
								enabled: false,
								states: {
									hover: {
										enabled: true,
										radius: 5
									}
								}
							},
							shadow: false,
							states: {
								hover: {
									lineWidth: 1
								}
							}
						}
					},

					series: [{
						type: 'area',
						name: 'MHash/s',
						//pointInterval: 2 * 86400,
						//pointStart: Date.UTC(2011, 8, 04),
						data: [
							<?php
					                while ($row = db_fetch_array($user24hhrQ, PGSQL_ASSOC)) {
					                        //echo "" .$row["hashrate"]. ", ";
								echo "[Date.UTC(" .date('Y, m-1, d, G, i', $row["time"]). "), " .($row["hashrate"] / 1000). " ],\n\r";
                					}
							?>
						]
					}]
				});
			});

		</script>
	<?php
	}

	if ($graph == "both") {

		$my_last_hr = 0;
		$pool_last_hr = 1;
		$user24hhrQ = db_query("SELECT extract(epoch from timestamp ) as time, hashrate FROM userhashrates WHERE userid = ".$userId." AND timestamp > (now() -  INTERVAL '".$interval." HOUR') ORDER BY timestamp ASC");
		$pool24hhrQ = db_query("SELECT extract(epoch from timestamp ) as time, hashrate FROM userhashrates WHERE userid = 0 AND timestamp > (now() -  INTERVAL '".$interval." HOUR') ORDER BY timestamp ASC");

		?>

		<script type="text/javascript">

			var chart;
			$(document).ready(function() {
				chart = new Highcharts.Chart({
					chart: {
						renderTo: 'hashstats',
						zoomType: 'x',
						spacingRight: 55
					},
				    title: {
						x: 27.5,
						text: 'My Vs. Pool Hash Rate'
					},
				    subtitle: {
						x: 27.5,
						text: document.ontouchstart === undefined ?
							'Click and drag over a time period to zoom in' :
							'Drag your finger over the plot to zoom in'
					},
					xAxis: {
						type: 'datetime',
						maxZoom: 1 * 17280, // 1 minute ?
						title: {
							text: null
						}
					},
					yAxis: {
						title: {
							text: 'Mhash / Sec'
						},
						min: 0.0,
						startOnTick: false,
						showFirstLabel: false
					},
					tooltip: {
						shared: true
					},
					legend: {
						x: 27.5,
						enabled: true
					},
					plotOptions: {
						area: {
							fillColor: {
								linearGradient: [0, 0, 0, 300],
								stops: [
									[0, Highcharts.getOptions().colors[0]],
									[1, 'rgba(2,0,0,0)']
								]
							},
							lineWidth: 1,
							marker: {
								enabled: false,
								states: {
									hover: {
										enabled: true,
										radius: 5
									}
								}
							},
							shadow: false,
							states: {
								hover: {
									lineWidth: 1
								}
							}
						}
					},

					series: [{
						type: 'area',
						name: 'My Hashrate (MH/s)',
						//pointInterval: 2 * 86400,
						//pointStart: Date.UTC(2011, 8, 04),
						data: [
							<?php
					                while ($row = db_fetch_array($user24hhrQ, PGSQL_ASSOC)) {
					                        $my_last_hr = $row["hashrate"];
								echo "[Date.UTC(" .date('Y, m-1, d, G, i', $row["time"]). "), " .($row["hashrate"] / 1000). " ],\n\r";
                					}
							?>
						]

					}, {

						type: 'area',
						name: 'Pool Hashrate (MH/s)',
						//pointInterval: 2 * 86400,
						//pointStart: Date.UTC(2011, 8, 04),
						data: [
							<?php
					                while ($row = db_fetch_array($pool24hhrQ, PGSQL_ASSOC)) {
					                        $pool_last_hr = $row["hashrate"];
								echo "[Date.UTC(" .date('Y, m-1, d, G, i', $row["time"]). "), " .($row["hashrate"] / 1000). " ],\n\r";
                					}
							?>
						]

					}, {
						type: 'pie',
						name: 'none',
						data: [{
							name: 'Mine',
							y: <?php print $my_last_hr; ?>,
							color: '#058DC7'
						}, {
							name: 'Pool',
							y: <?php print ($pool_last_hr - $my_last_hr); ?>,
							color: '#50B432'
						}],
						center: [100, 0],
						size: 100,
						showInLegend: false,
						dataLabels: {
							enabled: false
						}
					}]
				});
			});

		</script>
	<?php
	}
}

function financialGraphs($graph = NULL, $userId = NULL, $interval = "180", $type = "area") {
 global $userInfo;

	if($graph == "mine") {

		$userCreditsQ = db_query("SELECT sum(round(amount::numeric, 8)) as earnings, extract(epoch from timestamp ) as time FROM ledger where userid = ".$userId." AND transtype = 'Credit' GROUP BY timestamp ORDER BY timestamp ASC");
		$userCreditsDetailQ = db_query("SELECT round(amount::numeric, 8) as earnings, extract(epoch from timestamp ) as time FROM ledger where userid = ".$userId." AND transtype = 'Credit' ORDER BY timestamp ASC");
		$userFeesQ = db_query("SELECT sum(round(feeamount::numeric, 8)) as earnings, extract(epoch from timestamp ) as time FROM ledger where userid = ".$userId." AND transtype = 'Credit' GROUP BY timestamp ORDER BY timestamp ASC");
		$userDebitsQ = db_query("SELECT sum(round(amount::numeric, 8)) as earnings, extract(epoch from timestamp ) as time FROM ledger where userid = ".$userId." AND transtype LIKE '%Debit%' GROUP BY timestamp ORDER BY timestamp ASC");

		include("includes/charts/userIncome.inc.js");
	}

}
?>


<?php

set_time_limit(120);

require("api/db.php");

DEFINE("BASE_SQL","SELECT COUNT(DISTINCT(enodeb_id)) AS enbcount, COUNT(sector_id) AS sectorcount FROM " . DB_SECTORS . " WHERE ");
DEFINE("NETWORK_QUERIES",array(
	"10"=>array(
		"Total Sites"=>"1=1",
		"Vodafone Host L08"=>"sector_id IN (110,120,130) AND enodeb_id < 150000",
		"O2 Host L08"=>"sector_id IN (110,120,130) AND enodeb_id > 500000",
		"Vodafone Host L09"=>"sector_id IN (112,122,132) AND enodeb_id < 150000",
		"O2 Host L09"=>"sector_id IN (112,122,132) AND enodeb_id > 500000",
		"Vodafone Host L18"=>"sector_id IN (116,126,136) AND enodeb_id < 150000",
		"O2 Host L18"=>"sector_id IN (114,124,134) AND enodeb_id > 500000",
		"Vodafone Host L21"=>"sector_id IN (114,124,134) AND enodeb_id < 150000",
		"O2 Host L21"=>"sector_id IN (115,125,135) AND enodeb_id > 500000",
		"Vodafone Host L23-C1"=>"sector_id IN (115,125,135) AND enodeb_id < 150000",
		"O2 Host L23-C1"=>"sector_id IN (116,126,136) AND enodeb_id > 500000",
		"Vodafone Host L23-C2"=>"sector_id IN (117,127,137) AND enodeb_id < 150000",
		"O2 Host L23-C2"=>"sector_id IN (117,127,137) AND enodeb_id > 500000"
	),
	"15"=>array(
		"Total Sites"=>"1=1",
		"Vodafone Host L08"=>"sector_id IN (10,20,30) AND enodeb_id < 150000",
		"O2 Host L08"=>"sector_id IN (10,20,30) AND enodeb_id > 500000",
		"Vodafone Host L09"=>"sector_id IN (12,22,32) AND enodeb_id < 150000",
		"O2 Host L09"=>"sector_id IN (12,22,32) AND enodeb_id > 500000",
		"Vodafone Host L18"=>"sector_id IN (16,26,36) AND enodeb_id < 150000",
		"O2 Host L18"=>"sector_id IN (14,24,34) AND enodeb_id > 500000",
		"Vodafone Host L21"=>"sector_id IN (14,24,34) AND enodeb_id < 150000",
		"O2 Host L21"=>"sector_id IN (15,25,35) AND enodeb_id > 500000",
		"Vodafone Host L26"=>"sector_id IN (18,28,38) AND enodeb_id < 150000",
		"O2 Host L26"=>"sector_id IN (18,28,38) AND enodeb_id > 500000",
		"Vodafone Host L26T"=>"sector_id IN (19,29,39) AND enodeb_id < 150000"
	),
	"20"=>array(
		"Total Sites"=>"1=1",
		"L08 (6,7,8)"=>"sector_id IN (6,7,8)",
		"L18 (0,1,2)"=>"sector_id IN (0,1,2)",
		"L18-6S (3,4,5)"=>"sector_id IN (3,4,5)",
		"L18 Small Cells (16)"=>"sector_id IN (16) AND enodeb_id >= 50000",
		"L21 (71,72,73)"=>"sector_id IN (71,72,73)",
		"L21-6S (74,75,76)"=>"sector_id IN (74,75,76)",
	),
	"30"=>array(
		"Total Sites"=>"1=1",
		"L08 (12,13,14)"=>"sector_id IN (12,13,14)",
		"L18-C1 (0,1,2)"=>"sector_id IN (0,1,2)",
		"L18-C2 (3,4,5)"=>"sector_id IN (3,4,5)",
		"L21 (18,19,20)"=>"sector_id IN (18,19,20)",
		"L26-C1 (6,7,8)"=>"sector_id IN (6,7,8)",
		"L26-C2 (9,10,11)"=>"sector_id IN (9,10,11)",
		"L26-C3 (15,16,17)"=>"sector_id IN (15,16,17)"
	),
	"55"=>array(
		"Total Sites"=>"1=1"
	),
	"58"=>array(
		"Total Sites"=>"1=1"
	)
));

if (!empty($_GET["mnc"]) && !empty($_GET["carrier"])) {
	$start = time() - (86400 * 365 * 7);
	$end = time();
	$int = 86400 * 7;
	$cumulative = false;
	$useupdated = false;

	if (!empty($_GET["start"]) && is_numeric($start)) $start = (int) $_GET["start"];
	if (!empty($_GET["end"]) && is_numeric($start)) $end = (int) $_GET["end"];
	if (!empty($_GET["int"]) && is_numeric($start)) $int = (int) $_GET["int"];

	if (!empty($_GET["cumulative"])) $cumulative = true;
	if (!empty($_GET["useupdated"])) $useupdated = true;

	get_carrier_trend(clean($_GET["mnc"]), $_GET["carrier"], $start, $end, $int, $cumulative, $useupdated);
}

function ret($out) {
	die(json_encode($out,JSON_PRETTY_PRINT));
}

function get_carrier_trend($mnc, $carrier, $time_start, $time_end, $time_interval, $showCumulative, $useUpdated) {
	global $db_connection;
	
	$searchField = $useUpdated ? "updated" : "created";
	
	$out = array(
		"min"=>time(),
		"max"=>time(),
		"results"=>array()
	);
	
	$getBounds = "SELECT MIN({$searchField}) as min, MAX({$searchField}) as max FROM " . DB_SECTORS . " WHERE 
		mnc = {$mnc} AND " . NETWORK_QUERIES[$mnc][$carrier];
	$r = $db_connection->query($getBounds);
	if (!$r) ret($out);
	$out["min"] = $r->fetch_object()->min;
	$out["max"] = $r->fetch_object()->max;
	if ($out["min"] === null || $out["max"] === null) ret($out);
	
	$time_start = $out["min"];

	$getDataQuery = "SELECT COUNT(DISTINCT(enodeb_id)) AS total FROM " . DB_SECTORS . " WHERE 
		mnc = {$mnc} AND {$searchField} > ? AND {$searchField} < ? AND " . NETWORK_QUERIES[$mnc][$carrier];
	$get_sectors = $db_connection->prepare($getDataQuery);
	$get_sectors->bind_param("ii",$thisMin, $thisMax);

	$returnData = array();
	$count = 0;

	for ($i = $time_start; $i < $time_end; $i += $time_interval) {
		if ($showCumulative) {
			$thisMin = $time_start;
			$thisMax = $i;
		} else {
			$thisMin = $i;
			$thisMax = $i + $time_interval;
		}

		if (!$get_sectors->execute()){
			printf("Database Error: %s\n",$get_sectors->error);
			die();
		}

		$get_sectors->store_result();
		$get_sectors->bind_result($count);
		$get_sectors->fetch();

		$returnData[$i] = $count;
	}
	
	$out["results"] = $returnData;

	ret($out);
}

function get_info_for($mnc) {
	global $db_connection;
	echo <<<HTMLTABLE
		<table class='table table-striped table-hover table-sm'>
			<thead>
				<tr>
					<th>Carrier</th>
					<th>Unique eNBs</th>
					<th>Sectors seen</th>
					<th>Options</th>
				</tr>
			</thead>
			<tbody>
HTMLTABLE;
	foreach (NETWORK_QUERIES[$mnc] as $queryName=>$sqlLimit) {
		$r = $db_connection->query(BASE_SQL . "mnc={$mnc} AND " . $sqlLimit);
		if (!$r) {
			echo "<tr><td colspan='2'>Query Err</td></tr>";
		} else {
			$d = $r->fetch_object();
			echo "<tr title='{$sqlLimit}'>
				<td>{$queryName}</td>
				<td>{$d->enbcount}</td>
				<td>{$d->sectorcount}</td>
				<td><button class='add_chart' data-query='{$queryName}' data-mnc='{$mnc}'>Add to chart</button></td>
			</tr>";
		}
	}
	echo "</tbody></table>";
}

?>
<!DOCTYPE HTML>
<html lang="en">
	<head>

		<title>Map Info</title>
		<meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
		<meta content="IE=edge" http-equiv="X-UA-Compatible">
		<meta content="#1a1a1a" name="theme-color">

		<link rel="stylesheet" type="text/css" href="assets/styles.css?sect21" />

		<!-- jQuery -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

		<!-- Chart Library -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" integrity="sha256-R4pqcOYV8lt7snxMQO/HSbVCFRPMdrhAFMH+vr9giYI=" crossorigin="anonymous"></script>

		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

	</head>
	<body>
		<div class="container">
			<canvas id="visu_chart" style="width:100%; height:75vh"></canvas>
			<label for="cumulative">Show Cumulative</label>
			<input type="checkbox" checked="checked" id="cumulative" name="cumulative" />
			<label for="useupdated">Use 'Updated' field</label>
			<input type="checkbox" id="useupdated" name="useupdated" />
			<br />
			<label for="interval">Graph Interval</label>
			<select id="interval" name="interval">
				<option value="3">3 Days</option>
				<option value="5">5 Days</option>
				<option value="7" selected>1 Week</option>
				<option value="14">1 Fortnight</option>
				<option value="30">1 Month</option>
				<option value="60">2 Months</option>
				<option value="90">3 Months</option>
			</select>
			<button id="clear_graph">Clear Graph</button>
		</div>
		<div class="container-fluid">
			<div class="row">
				<div class="col-sm">
					<h1>O2-UK</h1>
					<?php get_info_for("10"); ?>
				</div>
				<div class="col-sm">
					<h1>Vodafone UK</h1>
					<?php get_info_for("15"); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm">
					<h1>Three UK</h1>
					<?php get_info_for("20"); ?>
				</div>
				<div class="col-sm">
					<h1>EE</h1>
					<?php get_info_for("30"); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm">
					<h1>Sure</h1>
					<?php get_info_for("55"); ?>
				</div>
				<div class="col-sm">
					<h1>Manx Telecom</h1>
					<?php get_info_for("58"); ?>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			function getDateString(uts) {
				let d = uts ? new Date(uts) : new Date();
				return d.getDate() + "-" + d.getMonth() + "-" + d.getFullYear();
			}
			let base = "info.php";
			let currentMin = getDateString();
			let mncColors = {
				"58":"#000",
				"55":"#11a",
				"30":"#007b85",
				"20":"#000",
				"15":"#e60000",
				"10":"#0a7cbb"
			};
			let ctx = document.getElementById('visu_chart').getContext('2d');
			let config = {
				type: 'line',
				data: {
					labels:[],
					datasets:[]
				},
				options: {
					responsive: true,
					title: {
						display: true,
						text: 'Carrier Comparison'
					},
					tooltips: {
						mode: 'index',
						intersect: false,
					},
					hover: {
						mode: 'nearest',
						intersect: true
					},
					scales: {
						xAxes: [{
							display: true,
							scaleLabel: {
								display: true,
								labelString: 'Time'
							}
						}],
						yAxes: [{
							display: true,
							scaleLabel: {
								display: true,
								labelString: 'Value'
							},
							beginAtZero:true
						}]
					}
				}
			};
			window.chart = new Chart(ctx, config);

			function updateChart(resp, mnc, title) {
				let data = resp.results;
				let keys = Object.keys(data);
				let newLine = {
					label: title,
					backgroundColor: mncColors[mnc],
					borderColor: mncColors[mnc],
					data: [],
					fill: false
				};

				config.data.labels = [];
				for (let i = 0;i < keys.length; i++) {
					newLine.data.push(data[keys[i]]);
					config.data.labels.push(getDateString(parseInt(keys[i]) * 1000));
				}

				config.data.datasets.push(newLine);
				window.chart.update();
			}
			
			function clearGraph() {
				config.data.datasets = [];
				window.chart.update();
			}

			function addData(el, mnc, query) {
				let additionalParams = "";
				if ($("#cumulative").is(":checked")) {
					additionalParams += "&cumulative=true"
				}
				if ($("#useupdated").is(":checked")) {
					additionalParams += "&useupdated=true"
				}
				
				let interval = parseInt($("#interval").val()) * 86400;
				additionalParams += "&int=" + interval;

				$.get(base + "?mnc=" + mnc + additionalParams + "&carrier=" + query, function(resp) {
					el.text("Parsing...");
					let r = JSON.parse(resp);
					console.log(r);
					updateChart(r, mnc, query);
					el.text("Added to chart").attr("disabled", true);
				});
			}

			$(".add_chart").each(function(){
				$(this).on("click enter",function() {
					$(this).text("Adding...");
					addData($(this), $(this).data("mnc"), $(this).data("query"));
				});
			});
			$("#clear_graph").on("click enter",clearGraph)
		</script>

	</body>
</html>
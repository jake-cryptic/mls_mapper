<?php

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.26.0/moment.min.js" integrity="sha256-5oApc/wMda1ntIEK4qoWJ4YItnV4fBHMwywunj8gPqc=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" integrity="sha256-R4pqcOYV8lt7snxMQO/HSbVCFRPMdrhAFMH+vr9giYI=" crossorigin="anonymous"></script>

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
		<option value="7">1 Week</option>
		<option value="14">1 Fortnight</option>
		<option value="30" selected>1 Month</option>
		<option value="60">2 Months</option>
		<option value="90">3 Months</option>
	</select>
	<button id="clear_graph">Clear Graph</button>
	<button id="enable_fill">Toggle Fill</button>
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
<script type="text/javascript" src="assets/js/stats.js"></script>
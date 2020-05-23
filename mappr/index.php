<!DOCTYPE HTML>
<html lang="en">
	<head>
	
		<title>Loading...</title>
		<meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport" />
		<meta content="IE=edge" http-equiv="X-UA-Compatible" />
		<meta content="#1a1a1a" name="theme-color" />
		
		<link rel="stylesheet" type="text/css" href="assets/css/styles.css?t<?php echo time(); ?>" />
		
		<!-- jQuery -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
		
		<!-- Bootstrap -->
		<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.4.0/umd/popper.min.js" integrity="sha256-FT/LokHAO3u6YAZv6/EKb7f2e0wXY3Ff/9Ww5NzT+Bk=" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
		
		<!-- Leaflet -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.6.0/leaflet.css" integrity="sha256-SHMGCYmST46SoyGgo4YR/9AlK1vf3ff84Aq9yK4hdqM=" crossorigin="anonymous" />
		<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.6.0/leaflet.js" integrity="sha256-fNoRrwkP2GuYPbNSJmMJOCyfRB2DhPQe0rGTgzRsyso=" crossorigin="anonymous"></script>
		
	</head>
	<body>
	
		<div id="app">

			<div id="opts">
				<select id="map_name" onChange='v.m.change(map_name)'>
					<option value='osm'>OSM</option>
					<option value='rdi' selected="selected">G Streets</option>
					<option value='arm'>G Streets Alt</option>
					<option value='hyb'>G Hybrid</option>
					<option value='ter'>G Terrain</option>
					<option value='rdo'>G Roads Only</option>
					<option value='tro'>G Terrain Only</option>
					<option value='sat'>G Sat Only</option>
				</select>
				<select id="mobile_network_code" onChange='v.changeMno(mobile_network_code)'>
					<option value='0'>All</option>
					<option value='10'>O2-UK</option>
					<option value='15'>Vodafone</option>
					<option value='20'>Three</option>
					<option value='30'>EE</option>
					<option value='55'>Sure</option>
					<option value='58'>Manx</option>
				</select>
				<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchPopup">Advanced</button>
				<a href="info.php">View Carrier Statistics</a>
			</div>

			<!-- Settings -->
			<div class="modal fade" id="searchPopup" tabindex="-1" role="dialog" aria-labelledby="searchPopupLabel" aria-hidden="true">
				<div class="modal-dialog modal-xl" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="searchPopupLabel">Advanced Search</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<fieldset>
								<legend>Find a specific site...</legend>
								<input type="number" placeholder="Lower eNB ID*" name="adv_enb_lower_bound" id="adv_enb_lower_bound" />
								<input type="number" placeholder="Upper eNB ID*" name="adv_enb_upper_bound" id="adv_enb_upper_bound" />
								* Set both in order for this to take effect...
								<h5> - or - </h5>
								<input type="number" placeholder="eNodeB ID" name="adv_enb_specific" id="adv_enb_specific" />
							</fieldset>

							<fieldset>
								<legend>Map Settings</legend>
								<label for="adv_load_only_bounds_ne">Constrain NE</label>
								<input type="checkbox" name="adv_load_only_bounds_ne" id="adv_load_only_bounds_ne" checked />
								<label for="adv_load_only_bounds_sw">Constrain SW</label>
								<input type="checkbox" name="adv_load_only_bounds_sw" id="adv_load_only_bounds_sw" checked />
							</fieldset>

							<fieldset>
								<legend>Data Quantity (Ignored for now)</legend>
								<input type="number" placeholder="Max eNBs to load (default:2500)" name="adv_max_num_enb" id="adv_max_num_enb" />
								<input type="number" placeholder="Max sector count to load (default:36)" name="adv_max_num_sec" id="adv_max_num_sec" />
							</fieldset>

							<fieldset>
								<legend>Sectors to show</legend>
								<label for="adv_load_only_bounds_sector">Constrain to current area</label>
								<input type="checkbox" name="adv_load_only_bounds_sector" id="adv_load_only_bounds_sector" checked />
								<div id="sector_list">
									Not live yet...
								</div>
							</fieldset>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
							<button type="button" class="btn btn-primary" id="advanced_search">Search</button>
						</div>
					</div>
				</div>
			</div>

			<div id="map"></div>
			<div id="results">
				<table>
					<thead>
						<tr>
							<th>MNC</th>
							<th>eNB</th>
							<th>Sectors</th>
							<th>Opts</th>
						</tr>
					</thead>
					<tbody id="results_tbl">
						<tr>
							<td colspan="3">No data</td>
						</tr>
					</tbody>
				</table>
			</div>

		</div>
		
		<script type="text/javascript" src="assets/js/map.js?t<?php echo time(); ?>" defer></script>
		
	</body>
</html>
<!DOCTYPE HTML>
<html lang="en">
	<head>
		<title>Waiting...</title>
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
		<style type="text/css">
		body,html{margin:0;padding:0;font-size:1.1em;font-weight:100;font-family:sans-serif;}
		select {background:rgba(255,255,255,0.3);color:#1a1a1a !important;border:.25vh solid #1a1a1a;font-size:1.2em;
		line-height:3vh;height:100%;padding:.4vh;top:0;display:block;float:left;width:33.33%;}
		#map{height:90vh;z-index:10;transition:.2s opacity ease-in-out;;}
		.site_popup .leaflet-popup-content-wrapper{font-size:1.2em;background:rgba(255,255,255,0.9) !important;}
		.loading{opacity:0.5;}.ticket_title{font-weight:600;font-size:1.1em;}.ticket_data{border-bottom:1px solid #1a1a1a;}
		#opts{z-index:10;height:10vh;}.site_popup_title{font-size:1.6em;font-weight:600;margin:.5em 0 .25em 0;text-align:center;display:block;}
		#progress {position:fixed;left:0;width:0%;height:10vh;top:0vh;background-color:#d1d1d1;z-index:-1;}
		.sector {display:block;padding:4px;font-family:"Lucida Console";float:left;}
		@media screen and (max-width:768px) {select{font-size:1.1em;}}
		@media screen and (max-width:512px) {select{font-size:1em;}}
		@media screen and (max-width:468px) {select{font-size:.8em;}#map{height:87.5vh;}#opts{height:12.5vh;}}
		</style>
		<!-- JS Code for map -->
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.0-rc.3/dist/leaflet.css" />
		<script src="https://unpkg.com/leaflet@1.0.0-rc.3/dist/leaflet.js"></script>
		<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
	</head>
	<body>
		<div id="progress"></div>
		<div id="app">
			<div id="opts"></div>
			<div id="map"></div>
		</div>
		<script type="text/javascript">
		var v = {
			map:null,
			base:null,
			markers:[]
		};
		v.map = L.map('map').setView([52.5201508,-1.5807446], 7);
		v.base = new L.TileLayer(
			'https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',
			{attribution: '<a href="https://maps.google.co.uk">Google Maps</a>'}
		);
		v.map.addLayer(v.base);
		
		
		var file = prompt("File:","https://localhost/cell_locator/cells/located/cellexport-234-15-lte_located.json");
		$.get(file,function(data){
			//var decoded = JSON.parse(data);
			//return;
			console.log(data);
			var keys = Object.keys(data.LTE);
			var vals = Object.values(data.LTE);
			
			for (var i = 1250;i<keys.length;i++){
				if (i>2250) break;
				var msg = "<h2>eNB " + keys[i] + "</h2>";
				
				var sectors = Object.keys(vals[i].sectors);
				for (var j = 0;j<sectors.length;j++){
					msg += "<span class='sector'>" + sectors[j] + "</span>";
				}
				
				msg += '<br /><a href="https://www.cellmapper.net/map?MCC=234&MNC=15&type=LTE&latitude='+vals[i].lat+'&longitude='+vals[i].lon+'&zoom=16&clusterEnabled=false" target="_blank">Cell Mapper</a>'
				v.markers.push(
					new L.marker(
						[vals[i].lat,vals[i].lon]
					).bindPopup(msg,{
						maxWidth:(screen.availWidth >= 600 ? 600 : screen.availWidth),
						className:'site_popup'
					})
				);
			}
			
			for (var marker in window.v.markers){
				window.v.map.addLayer(v.markers[marker]);
			}
		});
		</script>
	</body>
</html>
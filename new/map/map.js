var v = {
	map:null,
	ico:{
		main:null,
		multiple:null
	},
	
	mno:10,
	markers:[],
	
	onlySectors:null,
	cData:{
		10:{
			"L08":[110,120,130,140,150,160],
			"L18":[114,124,134,144,154,164],
			"L21":[115,125,135,145,155,165],
			"L23":[116,126,136,146,156,166,117,127,137,147,157,167],
			"L23-C1":[116,126,136,146,156,166],
			"L23-C2":[117,127,137,147,157,167]
		},
		15:{},
		20:{
			"L18":[0,1,2,3,4,5],
			"L08":[6,7,8],
			"L21":[71,72,73,74,75,76]
		},
		30:{}
	},
	
	init:function(){
		v.map = L.map('map').setView([52.5201508,-1.5807446], 7);
		v.initMapOsm();
		
		v.map.addEventListener('contextmenu',v.mapMove);
		
		var techIcon = L.Icon.extend({
			options: {
				iconSize:[25,41],
				iconAnchor:[12.5,41],
				popupAnchor:[0,-28]
			}
		});
		
		v.ico.main		= new techIcon({iconUrl: 'img/marker-default.png'});
		v.ico.multiple	= new techIcon({iconUrl: 'img/marker-multiple.png'});
	},
	
	changeMap:function(map){
		if (v.base){
			v.map.removeLayer(v.base);
		}
		if (map.value === "sat") v.initGMap("s");
		if (map.value === "ter") v.initGMap("p");
		if (map.value === "tro") v.initGMap("t");
		if (map.value === "rdo") v.initGMap("h");
		if (map.value === "rdi") v.initGMap("m");
		if (map.value === "arm") v.initGMap("r");
		if (map.value === "hyb") v.initGMap("y");
		if (map.value === "osm") v.initMapOsm();
	},
	
	changeMno:function(mno){
		v.mno = parseInt(mno.value);
		v.removeMapMarkers();
		v.genOptions();
		v.loadPoints();
	},
	
	initGMap:function(l){
		v.base = new L.TileLayer(
			'https://mt1.google.com/vt/lyrs='+l+'&x={x}&y={y}&z={z}',
			{attribution: '<a href="https://maps.google.co.uk">Google Maps</a>'}
		);
		v.map.addLayer(v.base);
	},
	initMapOsm:function(){
		v.base = new L.TileLayer(
			'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
			{attribution: '<a href="http://openstreetmap.org">OpenStreetMap</a>'}
		);
		v.map.addLayer(v.base);
	},
	
	genOptions:function(){
		v.onlySectors = null;
		
		$("#mnoSectors").empty().append(
			$("<select/>",{"value":"null","selected":true}).text("All")
		);
		
		var mnoData = Object.keys(v.cData[v.mno]);
		console.log(mnoData);
		for (var i = 0;i<mnoData.length;i++){
			$("#mnoSectors").append(
				$("<select/>",{"value":mnoData[i]}).text(mnoData[i])
			);
		}
	},
	
	mapMove:function(evt){
		console.log(evt);
		v.removeMapMarkers();
		v.loadPoints();
	},
	
	removeMapMarkers:function(){
		for (var marker in v.markers){
			v.map.removeLayer(v.markers[marker]);
		}
		v.markers = [];
	},
	
	findItem:function(arr1,arr2){
		console.log(arr1);
		for (var i = 0;i<arr2.length;i++){
			if (arr1.indexOf(arr2[i].toString()) !== -1) return true;
		}
		return false;
	},
	
	sectorInfo:function(mno,sectors){
		var ret = "Bands: ";
		if (mno === 10){
			if (v.findItem(sectors,[115,125,135,145,155,165])) ret += "1 ";
			if (v.findItem(sectors,[114,124,134,144,154,164])) ret += "3 ";
			if (v.findItem(sectors,[110,120,130,140,150,160])) ret += "20 ";
			if (v.findItem(sectors,[112,122,132])) ret += "8 ";
			if (v.findItem(sectors,[116,126,136,146,156,166])) ret += "40C1 ";
			if (v.findItem(sectors,[117,127,137,147,157,167])) ret += "40C2";
		} else if (mno === 15){
			if (v.findItem(sectors,[15,25,35,45,55,65])) ret += "1 ";
			if (v.findItem(sectors,[14,24,34,44,54,64])) ret += "1 ";
			if (v.findItem(sectors,[16,26,36,46,56,66])) ret += "3 ";
			if (v.findItem(sectors,[18,28,38,48,58,68])) ret += "7 ";
			if (v.findItem(sectors,[12,22,32])) ret += "8 ";
			if (v.findItem(sectors,[10,20,30,40,50,60])) ret += "20 ";
			if (v.findItem(sectors,[19,29,39,49,59,69])) ret += "38";
		} else if (mno === 20){
			if (v.findItem(sectors,[71,72,73,74,75,76])) ret += "1 ";
			if (v.findItem(sectors,[0,1,2,3,4,5])) ret += "3 ";
			if (v.findItem(sectors,[6,7,8])) ret += "20";
		} else if (mno === 30) {
			if (v.findItem(sectors,[18,19,20])) ret += "1 ";
			if (v.findItem(sectors,[0,1,2])) ret += "3P ";
			if (v.findItem(sectors,[3,4,5])) ret += "3S ";
			if (v.findItem(sectors,[6,7,8])) ret += "7P ";
			if (v.findItem(sectors,[9,10,11])) ret += "7S ";
			if (v.findItem(sectors,[12,13,14])) ret += "20";
		}
		
		return ret;
	},
	
	loadPoints:function(){
		var bounds = v.map.getBounds();
		
		$.ajax({
			url:'get-pins.php',
			type:'GET',
			data:{
				nelat:bounds._northEast.lat,
				nelng:bounds._northEast.lng,
				swlat:bounds._southWest.lat,
				swlng:bounds._southWest.lng,
				mno:v.mno,
				sectors:v.cData[v.onlySectors]
			},
			dataType:'json',
			success:v.placePoints,
			error:function(e){
				console.error(e);
			}
		});
	},
	
	placePoints:function(data){
		for (var i = 0;i<data.length;i++){
			let tLat = data[i].lat;
			let tLng = data[i].lng;
			
			var marker = v.ico.main;
			var txt = data[i].id + '<br/><strong>Coord Lat:</strong><input type="text" readonly value="'+tLat+'" /><br/>\
				<strong>Coord Lon:</strong><input type="text" readonly value="'+tLng+'" /><br/><br/>\
				View area on <a href="https://www.google.co.uk/maps/search/'+tLat+','+tLng+'/" target="_blank">Google Maps</a>\
				<a href="https://www.cellmapper.net/map?MCC=234&MNC='+v.mno+'&type=LTE&latitude='+tLat+'&longitude='+tLng+'&zoom=16&clusterEnabled=false" target="_blank">Cell Mapper</a>';
			
			txt += "<div class='sect_block'>";
			for (var s in data[i].sectors){
				txt += "<span class='sect'>" + s + "</span>";
			}
			txt += "</div>";
			
			v.markers.push(
				new L.marker(
					[tLat,tLng],
					{icon:marker}
				).bindPopup(
					txt,{maxWidth:(screen.availWidth >= 600 ? 600 : screen.availWidth),className:'site_popup'}
				).bindTooltip(v.sectorInfo(v.mno,Object.keys(data[i].sectors)),
				{
					permanent: true, 
					direction: 'right'
				})
			);
		}
		
		for (var marker in v.markers){
			v.map.addLayer(v.markers[marker]);
		}
	}
};

v.init();
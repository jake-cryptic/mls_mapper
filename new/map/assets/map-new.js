var v = {
	mLimit: 5000,
	sLimit: 36,
	mno: 0,
	mcc: 234,
	markers: [],
	polygons: [],

	attr: {
		g: '<a href="https://maps.google.co.uk">Google Maps</a>',
		o: '<a href="http://openstreetmap.org">OpenStreetMap</a>'
	},

	mncData: {},

	cData: {
		10: {
			"L08": [110, 120, 130, 140, 150, 160],
			"L18": [114, 124, 134, 144, 154, 164],
			"L21": [115, 125, 135, 145, 155, 165],
			"L23-C1": [116, 126, 136, 146, 156, 166],
			"L23-C2": [117, 127, 137, 147, 157, 167]
		},
		15: {
			"L08": [10, 20, 30, 40, 50, 60],
			"L09": [12, 22, 32],
			"L18": [16, 26, 36, 46, 56, 66],
			"L21": [14, 24, 34, 44, 54, 64, 15, 25, 35],
			"L26": [18, 28, 38, 48, 58, 68],
			"L26T": [19, 29, 39]
		},
		20: {
			"L18": [0, 1, 2, 3, 4, 5],
			"L08": [6, 7, 8],
			"L21": [71, 72, 73, 74, 75, 76]
		},
		30: {
			"L18": [0, 1, 2],
			"L18-C2": [3, 4, 5],
			"L26-C1": [6, 7, 8],
			"L26-C2": [9, 10, 11],
			"L26-C3": [15, 16, 17],
			"L08": [12, 13, 14],
			"L21": [18, 19, 20]
		}
	},

	sData: {
		10: JSON.parse('{"110":"L08","114":"L18","115":"L21","116":"L23-C1","117":"L23-C2","120":"L08","124":"L18","125":"L21","126":"L23-C1","127":"L23-C2","130":"L08","134":"L18","135":"L21","136":"L23-C1","137":"L23-C2","140":"L08","144":"L18","145":"L21","146":"L23-C1","147":"L23-C2","150":"L08","154":"L18","155":"L21","156":"L23-C1","157":"L23-C2","160":"L08","164":"L18","165":"L21","166":"L23-C1","167":"L23-C2"}'),
		15: JSON.parse('{"10":"L08","12":"L09","14":"L21","15":"L21","16":"L18","18":"L26","19":"L26T","20":"L08","22":"L09","24":"L21","25":"L21","26":"L18","28":"L26","29":"L26T","30":"L08","32":"L09","34":"L21","35":"L21","36":"L18","38":"L26","39":"L26T","40":"L08","44":"L21","46":"L18","48":"L26","50":"L08","54":"L21","56":"L18","58":"L26","60":"L08","64":"L21","66":"L18","68":"L26"}'),
		20: JSON.parse('{"0":"L18","1":"L18","2":"L18","3":"L18","4":"L18","5":"L18","6":"L08","7":"L08","8":"L08","71":"L21","72":"L21","73":"L21","74":"L21","75":"L21","76":"L21"}'),
		30: JSON.parse('{"0":"L18","1":"L18","2":"L18","3":"L18-C2","4":"L18-C2","5":"L18-C2","6":"L26-C1","7":"L26-C1","8":"L26-C1","9":"L26-C2","10":"L26-C2","11":"L26-C2","12":"L08","13":"L08","14":"L08","15":"L26-C3","16":"L26-C3","17":"L26-C3","18":"L21","19":"L21","20":"L21"}'),
		55: {},
		58: {}
	},

	init: function () {
		v.m.init();
		v.getMncData();

		$("#advanced_search").on("click enter", function () {
			$("#searchPopup").modal("hide");
			v.loadData();
		});
	},

	getLocation: function (cb) {
		navigator.geolocation.getCurrentPosition(function (position) {
			cb(position.coords.latitude, position.coords.longitude);
		});
	},

	m: {
		zoom: 10,
		map: null,
		ico: {
			main: null,
			multiple: null
		},

		init: function () {
			v.m.map = L.map('map').setView([52.5201508, -1.5807446], v.m.zoom);
			v.m.map.addEventListener('contextmenu', v.m.mapMove);

			v.m.change({value: "rdi"});
			v.m.initIcons();

			v.getLocation(function (lat, lon) {
				v.m.map.setView([lat, lon], v.m.zoom);
			});
		},

		initIcons: function () {
			let techIcon = L.Icon.extend({
				options: {
					iconSize: [25, 41],
					iconAnchor: [12.5, 41],
					popupAnchor: [0, -28]
				}
			});

			v.m.ico.main = new techIcon({iconUrl: 'img/marker-default.png'});
			v.m.ico.multiple = new techIcon({iconUrl: 'img/marker-multiple.png'});
		},

		change: function (map) {
			if (v.base) v.m.map.removeLayer(v.base);

			let maps = {
				"sat": "s",
				"ter": "p",
				"tro": "t",
				"rdo": "h",
				"rdi": "m",
				"arm": "r",
				"hyb": "y"
			};

			let server = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
				attr = v.attr.o;

			if (map.value !== "osm") {
				attr = v.attr.g;
				server = 'https://mt1.google.com/vt/lyrs=' + maps[map.value] + '&x={x}&y={y}&z={z}';
			}

			v.base = new L.TileLayer(server, {attribution: attr});
			v.m.map.addLayer(v.base);
		},

		mapMove: function (evt) {
			console.log(evt);
			v.m.removeMapItems();
			v.loadData();
		},

		removeMapItems: function () {
			for (let marker in v.markers) {
				v.m.map.removeLayer(v.markers[marker]);
			}

			for (let polygon in v.polygons) {
				v.m.map.removeLayer(v.polygons[polygon]);
			}

			v.polygons = [];
			v.markers = [];
		}
	},

	changeMno: function (mno) {
		v.mno = parseInt(mno.value);
		v.m.removeMapItems();
		v.updateMncOpts();
		v.loadData();
	},

	getMncData: function () {
		var reqData = {
			mcc: v.mcc,
			mastdb: "masts2"
		};

		if ($("#adv_load_only_bounds_sector").is(":checked")) {
			let bounds = v.m.map.getBounds();
			reqData["nelat"] = round(bounds._northEast.lat, 9);
			reqData["nelng"] = round(bounds._northEast.lng, 9);
			reqData["swlat"] = round(bounds._southWest.lat, 9);
			reqData["swlng"] = round(bounds._southWest.lng, 9);
		}

		$.ajax({
			url: 'api/get-mnc.php',
			type: 'GET',
			data: reqData,
			dataType: 'json',
			success: v.updateMncData,
			error: function (e) {
				console.error(e);
			}
		});
	},

	updateMncData: function (data) {
		v.mncData = data;
		v.updateMncOpts();
	},

	updateMncOpts:function(){
		let $cont = $("#sector_list");
		$cont.empty();

		if (v.mno === 0){
			$cont.append(
				$("<h2/>").append("Select a specific operator to change sector IDs shown.")
			);
			return;
		}

		let keys = Object.keys(v.mncData);
		for (let i = 0, l = keys.length; i < l; i++) {
			let mncSectors = v.mncData[keys[i]];
			if (parseInt(keys[i]) !== v.mno) continue;

			$cont.append($("<strong/>").text(keys[i]));

			for (let j = 0, k = mncSectors.length; j < k; j++){
				if (j % 5 === 0) $cont.append($("<br />"));

				$cont.append(
					$("<label/>").text(mncSectors[j] + " "),
					$("<input/>",{
						"type":"checkbox",
						"name":"sectors[]",
						"value":mncSectors[j]
					})
				);
			}
		}
	},

	findItem: function (arr1, arr2) {
		for (let i = 0; i < arr2.length; i++) {
			if (arr1.indexOf(arr2[i].toString()) !== -1) return true;
		}
		return false;
	},

	sectorInfo: function (mno, enb, sectors) {
		var ret = "<strong>" + enb + "</strong>: ";
		if (mno === 10) {
			if (v.findItem(sectors, [115, 125, 135, 145, 155, 165])) ret += "1 ";
			if (v.findItem(sectors, [114, 124, 134, 144, 154, 164])) ret += (enb >= 500000 ? "3 " : "1 ");
			if (v.findItem(sectors, [110, 120, 130, 140, 150, 160])) ret += "20 ";
			if (v.findItem(sectors, [112, 122, 132])) ret += "8 ";
			if (v.findItem(sectors, [116, 126, 136, 146, 156, 166])) ret += (enb >= 500000 ? "40C1 " : "3 ");
			if (v.findItem(sectors, [117, 127, 137, 147, 157, 167])) ret += "40C2";
		} else if (mno === 15) {
			if (v.findItem(sectors, [15, 25, 35, 45, 55, 65])) ret += "1 ";
			if (v.findItem(sectors, [14, 24, 34, 44, 54, 64])) ret += "1 ";
			if (v.findItem(sectors, [16, 26, 36, 46, 56, 66])) ret += "3 ";
			if (v.findItem(sectors, [18, 28, 38, 48, 58, 68])) ret += "7 ";
			if (v.findItem(sectors, [12, 22, 32])) ret += "8 ";
			if (v.findItem(sectors, [10, 20, 30, 40, 50, 60])) ret += "20 ";
			if (v.findItem(sectors, [19, 29, 39, 49, 59, 69])) ret += "38";
		} else if (mno === 20) {
			if (v.findItem(sectors, [71, 72, 73, 74, 75, 76])) ret += "1 ";
			if (v.findItem(sectors, [0, 1, 2, 3, 4, 5])) ret += "3 ";
			if (v.findItem(sectors, [16])) ret += "3SC ";
			if (v.findItem(sectors, [6, 7, 8])) ret += "20";
		} else if (mno === 30) {
			if (v.findItem(sectors, [18, 19, 20])) ret += "1 ";
			if (v.findItem(sectors, [15, 16, 17])) ret += "7T ";
			if (v.findItem(sectors, [0, 1, 2])) ret += "3P ";
			if (v.findItem(sectors, [3, 4, 5])) ret += "3S ";
			if (v.findItem(sectors, [6, 7, 8])) ret += "7P ";
			if (v.findItem(sectors, [9, 10, 11])) ret += "7S ";
			if (v.findItem(sectors, [12, 13, 14])) ret += "20";
		}

		return ret;
	},

	getDataParameters: function () {
		var data = {
			"limit_m": 2500,
			"limit_s": 36,
			"mastdb": "masts2"
		};

		// Coordinate bounds
		let bounds = v.m.map.getBounds();
		if ($("#adv_load_only_bounds_ne").is(":checked")) {
			data["nelat"] = round(bounds._northEast.lat, 12);
			data["nelng"] = round(bounds._northEast.lng, 12);
		}
		if ($("#adv_load_only_bounds_sw").is(":checked")) {
			data["swlat"] = round(bounds._southWest.lat, 12);
			data["swlng"] = round(bounds._southWest.lng, 12);
		}

		// Limits
		if ($("#adv_max_num_enb").val()) data["limit_m"] = $("#adv_max_num_enb").val();
		if ($("#adv_max_num_sec").val()) data["limit_s"] = $("#adv_max_num_sec").val();

		// eNB range
		if ($("#adv_enb_lower_bound").val().length !== 0 && $("#adv_enb_upper_bound").val().length !== 0) {
			data["enb_range"] = [
				$("#adv_enb_lower_bound").val(),
				$("#adv_enb_upper_bound").val()
			];
		}

		// Specific eNB
		if ($("#adv_enb_specific").val().length !== 0) {
			data["enb"] = $("#adv_enb_specific").val();
		}

		// Specific sectors
		if ($("input[type='checkbox'][name='sectors[]']").serialize().length !== 0){
			let sectors = $("input[type='checkbox'][name='sectors[]']").serializeArray();
			data["sectors"] = sectors.map(function(x){
				return parseInt(x.value);
			});
		}

		data["mnc"] = $("#mobileNetwork").val();

		return data;
	},

	loadData: function () {
		$.ajax({
			url: 'api/lookup-enb.php',
			type: 'GET',
			data: v.getDataParameters(),
			dataType: 'json',
			success: v.viewData,
			error: function (e) {
				console.error(e);
			}
		});
	},

	getPopupText: function (enb, mnc, lat, lng) {
		let t = enb;

		t += '<br /><strong>Lat:</strong><input type="text" readonly value="' + lat + '" />';
		t += '<br /><strong>Lon:</strong><input type="text" readonly value="' + lng + '" />';
		t += '<br />View area on:';
		t += '<br /><a href="https://www.google.co.uk/maps/search/' + lat + ',' + lng + '/" target="_blank">Google Maps</a>';
		t += '<br /><a href="https://www.cellmapper.net/map?MCC=234&MNC=' + mnc + '&type=LTE&latitude=' + lat + '&longitude=' + lng + '&zoom=16&clusterEnabled=false" target="_blank">Cell Mapper</a>';

		return t;
	},

	getSectorText: function () {

	},

	getSectorColor: function (mnc, sector) {
		let sectorName = v.sData[mnc][sector];
		let sectorId = mnc.toString() + sectorName;
		let sectorMD5 = MD5(sectorId);

		return '#' + sectorMD5.substring(0, 6);
	},

	viewData: function (data) {
		$("#results_tbl").empty();

		for (let i = 0; i < data.length; i++) {
			v.addPointToMap(data[i]);
			v.addPointToTable(data[i]);
		}

		// Display items on map
		v.markers.forEach(function (marker) {
			v.m.map.addLayer(marker);
		});
		v.polygons.forEach(function (polygon) {
			v.m.map.addLayer(polygon);
		});
	},

	addPointToMap: function (point) {
		let tLat = point.lat;
		let tLng = point.lng;
		let tEnb = point.id;

		let markerPopOpts = {
			maxWidth: (screen.availWidth >= 600 ? 600 : screen.availWidth),
			className: 'site_popup'
		};

		let markerToolOpts = {
			permanent: true,
			direction: 'bottom'
		};

		function pushPolygon(siteloc, sectorloc, color) {
			v.polygons.push(
				L.polygon(
					[siteloc, sectorloc],
					{color: color}
				)
			);
		}

		function pushMarker(siteloc, poptext, tooltext) {
			v.markers.push(
				new L.marker(
					siteloc, {icon: v.m.ico.main}
				).bindPopup(
					poptext, markerPopOpts
				).bindTooltip(
					tooltext, markerToolOpts
				)
			);
		}

		let txt = v.getPopupText(tEnb, point.mnc, tLat, tLng);

		//sectCoords = [];
		txt += "<div class='sect_block'>";
		for (let s in point.sectors) {
			let color = v.getSectorColor(point.mnc, s);
			
			let dateObj = new Date();
			let dates = "";
			dateObj.setTime(parseInt(point.sectors[s][2]) * 1000);
			dates += "First Seen: " + dateObj.toUTCString() + "\n";
			dateObj.setTime(parseInt(point.sectors[s][3]) * 1000);
			dates += "Last Seen: " + dateObj.toUTCString() + "\n";
			
			txt += "<span class='sect' style='background-color:" + color + "' title='" + dates + "'>" + s + "</span>";

			//sectCoords.push([point.sectors[s][0], point.sectors[s][1]]);
			pushPolygon([tLat, tLng], [parseFloat(point.sectors[s][0]), parseFloat(point.sectors[s][1])], color);
		}
		txt += "</div>";

		pushMarker([tLat, tLng], txt, v.sectorInfo(parseInt(point.mnc), tEnb, Object.keys(point.sectors)));
		/*v.polygons.push(
			L.polygon(sectCoords, {color: 'red'})
		);*/
	},

	addPointToTable: function (point) {
		let $r = $("#results_tbl");

		function getSectors(){
			return Object.keys(point.sectors).join(", ");
		}

		$r.append(
			$("<tr/>").append(
				$("<td/>").text(point.mnc),
				$("<td/>").text(point.id),
				$("<td/>").text(getSectors()),
				$("<td/>").append(
					$("<button/>",{
						"data-lat":point.lat,
						"data-lng":point.lng
					}).text("View").on("click enter",v.goToHereData)
				)
			)
		);
	},

	goToHereData:function(){
		v.m.map.setView([$(this).data("lat"), $(this).data("lng")], 18);
	}
};

function tile2long(x,z) { 
	return (x/Math.pow(2,z)*360-180);
}

function tile2lat(y,z) {
    var n=Math.PI-2*Math.PI*y/Math.pow(2,z);
    return (180/Math.PI*Math.atan(0.5*(Math.exp(n)-Math.exp(-n))));
}

function round(n, dp){
	var exp = Math.pow(10,dp);
	return Math.floor(Math.round(n*exp))/exp;
}

var MD5 = function(s){function L(k,d){return(k<<d)|(k>>>(32-d))}function K(G,k){var I,d,F,H,x;F=(G&2147483648);H=(k&2147483648);I=(G&1073741824);d=(k&1073741824);x=(G&1073741823)+(k&1073741823);if(I&d){return(x^2147483648^F^H)}if(I|d){if(x&1073741824){return(x^3221225472^F^H)}else{return(x^1073741824^F^H)}}else{return(x^F^H)}}function r(d,F,k){return(d&F)|((~d)&k)}function q(d,F,k){return(d&k)|(F&(~k))}function p(d,F,k){return(d^F^k)}function n(d,F,k){return(F^(d|(~k)))}function u(G,F,aa,Z,k,H,I){G=K(G,K(K(r(F,aa,Z),k),I));return K(L(G,H),F)}function f(G,F,aa,Z,k,H,I){G=K(G,K(K(q(F,aa,Z),k),I));return K(L(G,H),F)}function D(G,F,aa,Z,k,H,I){G=K(G,K(K(p(F,aa,Z),k),I));return K(L(G,H),F)}function t(G,F,aa,Z,k,H,I){G=K(G,K(K(n(F,aa,Z),k),I));return K(L(G,H),F)}function e(G){var Z;var F=G.length;var x=F+8;var k=(x-(x%64))/64;var I=(k+1)*16;var aa=Array(I-1);var d=0;var H=0;while(H<F){Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=(aa[Z]| (G.charCodeAt(H)<<d));H++}Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=aa[Z]|(128<<d);aa[I-2]=F<<3;aa[I-1]=F>>>29;return aa}function B(x){var k="",F="",G,d;for(d=0;d<=3;d++){G=(x>>>(d*8))&255;F="0"+G.toString(16);k=k+F.substr(F.length-2,2)}return k}function J(k){k=k.replace(/rn/g,"n");var d="";for(var F=0;F<k.length;F++){var x=k.charCodeAt(F);if(x<128){d+=String.fromCharCode(x)}else{if((x>127)&&(x<2048)){d+=String.fromCharCode((x>>6)|192);d+=String.fromCharCode((x&63)|128)}else{d+=String.fromCharCode((x>>12)|224);d+=String.fromCharCode(((x>>6)&63)|128);d+=String.fromCharCode((x&63)|128)}}}return d}var C=Array();var P,h,E,v,g,Y,X,W,V;var S=7,Q=12,N=17,M=22;var A=5,z=9,y=14,w=20;var o=4,m=11,l=16,j=23;var U=6,T=10,R=15,O=21;s=J(s);C=e(s);Y=1732584193;X=4023233417;W=2562383102;V=271733878;for(P=0;P<C.length;P+=16){h=Y;E=X;v=W;g=V;Y=u(Y,X,W,V,C[P+0],S,3614090360);V=u(V,Y,X,W,C[P+1],Q,3905402710);W=u(W,V,Y,X,C[P+2],N,606105819);X=u(X,W,V,Y,C[P+3],M,3250441966);Y=u(Y,X,W,V,C[P+4],S,4118548399);V=u(V,Y,X,W,C[P+5],Q,1200080426);W=u(W,V,Y,X,C[P+6],N,2821735955);X=u(X,W,V,Y,C[P+7],M,4249261313);Y=u(Y,X,W,V,C[P+8],S,1770035416);V=u(V,Y,X,W,C[P+9],Q,2336552879);W=u(W,V,Y,X,C[P+10],N,4294925233);X=u(X,W,V,Y,C[P+11],M,2304563134);Y=u(Y,X,W,V,C[P+12],S,1804603682);V=u(V,Y,X,W,C[P+13],Q,4254626195);W=u(W,V,Y,X,C[P+14],N,2792965006);X=u(X,W,V,Y,C[P+15],M,1236535329);Y=f(Y,X,W,V,C[P+1],A,4129170786);V=f(V,Y,X,W,C[P+6],z,3225465664);W=f(W,V,Y,X,C[P+11],y,643717713);X=f(X,W,V,Y,C[P+0],w,3921069994);Y=f(Y,X,W,V,C[P+5],A,3593408605);V=f(V,Y,X,W,C[P+10],z,38016083);W=f(W,V,Y,X,C[P+15],y,3634488961);X=f(X,W,V,Y,C[P+4],w,3889429448);Y=f(Y,X,W,V,C[P+9],A,568446438);V=f(V,Y,X,W,C[P+14],z,3275163606);W=f(W,V,Y,X,C[P+3],y,4107603335);X=f(X,W,V,Y,C[P+8],w,1163531501);Y=f(Y,X,W,V,C[P+13],A,2850285829);V=f(V,Y,X,W,C[P+2],z,4243563512);W=f(W,V,Y,X,C[P+7],y,1735328473);X=f(X,W,V,Y,C[P+12],w,2368359562);Y=D(Y,X,W,V,C[P+5],o,4294588738);V=D(V,Y,X,W,C[P+8],m,2272392833);W=D(W,V,Y,X,C[P+11],l,1839030562);X=D(X,W,V,Y,C[P+14],j,4259657740);Y=D(Y,X,W,V,C[P+1],o,2763975236);V=D(V,Y,X,W,C[P+4],m,1272893353);W=D(W,V,Y,X,C[P+7],l,4139469664);X=D(X,W,V,Y,C[P+10],j,3200236656);Y=D(Y,X,W,V,C[P+13],o,681279174);V=D(V,Y,X,W,C[P+0],m,3936430074);W=D(W,V,Y,X,C[P+3],l,3572445317);X=D(X,W,V,Y,C[P+6],j,76029189);Y=D(Y,X,W,V,C[P+9],o,3654602809);V=D(V,Y,X,W,C[P+12],m,3873151461);W=D(W,V,Y,X,C[P+15],l,530742520);X=D(X,W,V,Y,C[P+2],j,3299628645);Y=t(Y,X,W,V,C[P+0],U,4096336452);V=t(V,Y,X,W,C[P+7],T,1126891415);W=t(W,V,Y,X,C[P+14],R,2878612391);X=t(X,W,V,Y,C[P+5],O,4237533241);Y=t(Y,X,W,V,C[P+12],U,1700485571);V=t(V,Y,X,W,C[P+3],T,2399980690);W=t(W,V,Y,X,C[P+10],R,4293915773);X=t(X,W,V,Y,C[P+1],O,2240044497);Y=t(Y,X,W,V,C[P+8],U,1873313359);V=t(V,Y,X,W,C[P+15],T,4264355552);W=t(W,V,Y,X,C[P+6],R,2734768916);X=t(X,W,V,Y,C[P+13],O,1309151649);Y=t(Y,X,W,V,C[P+4],U,4149444226);V=t(V,Y,X,W,C[P+11],T,3174756917);W=t(W,V,Y,X,C[P+2],R,718787259);X=t(X,W,V,Y,C[P+9],O,3951481745);Y=K(Y,h);X=K(X,E);W=K(W,v);V=K(V,g)}var i=B(Y)+B(X)+B(W)+B(V);return i.toLowerCase()};

v.init();

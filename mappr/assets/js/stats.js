function getDateString(uts) {
	let d = uts ? new Date(uts) : new Date();
	return d.getFullYear() + "-" + d.getMonth() + "-" + (d.getDate().toString().length === 1 ? "0" + d.getDate() : d.getDate());
}

let base = "api/stats/";
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
	type: 'scatter',
	data: {
		datasets:[]
	},
	options: {
		responsive: true,
		animation: {
			duration: 0
		},
		hover: {
			animationDuration: 0
		},
		responsiveAnimationDuration: 0,
		title: {
			display: true,
			text: 'Carrier Comparison'
		},
		scales: {
			xAxes: [{
				display: true,
				type: 'time',
				displayFormats: {
					quarter: 'YYYY MMM D'
				},
				scaleLabel: {
					display: true,
					labelString: 'Time'
				}
			}],
			yAxes: [{
				display: true,
				scaleLabel: {
					display: true,
					labelString: '# of sites'
				},
				beginAtZero:false
			}]
		}
	}
};
window.chart = new Chart(ctx, config);

function getColor(str){
	return "#" + MD5(str).substr(0,6);
}

function updateChart(resp, mnc, title) {
	let data = resp.results;
	let keys = Object.keys(data);
	let col = getColor(title + mnc);
	let newLine = {
		label: title,
		backgroundColor: col,
		borderColor: col,
		data: [],
		showLine:true,
		fill:false,
		tension: 0
	};

	for (let i = 0;i < keys.length; i++) {
		newLine.data.push({
			x:new Date(parseInt(keys[i]) * 1000),
			y:parseInt(data[keys[i]])
		});
	}

	config.data.datasets.push(newLine);
	window.chart.update();
}

function clearGraph() {
	config.data.datasets = [];
	window.chart.update();
}

function toggleFill() {
	for (let i = 0; i < config.data.datasets.length; i++) {
		config.data.datasets[i].fill = !config.data.datasets[i].fill;
	}
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
$("#clear_graph").on("click enter",clearGraph);
$("#enable_fill").on("click enter",toggleFill);
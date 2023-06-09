var simTime = 0;
var simBefore = 0;
var simAfter = 0;
var offset = 0;
var offsetType = 0;
var simulationData = null;

//Eksomiwsi gia ton diaxiristi

currentTime = new Date();  //Prokathorismeni wra gia tin eksomiwsi
h = currentTime.getHours(),  //trexousa wra
	m = currentTime.getMinutes();
if(h < 10) h = '0' + h;
if(m < 10) m = '0' + m;
var x = document.getElementById("input-hour");
x.defaultValue = h + ":" + m;

$(document).on("submit","#sim-form",function(e) {
	e.preventDefault();
	$( "#current-time" ).empty();
	$( "#before" ).empty();
	$( "#after" ).empty();

	var hour = $("#input-hour").val(); //wra pou tha ginei i eksomiwsi
	var g_offset = $("#input-offset").val();  //metavoli kata wra/lepta gia eksomiwsi
	var g_offsetType = $("#input-select").val(); //lepta i wres
	console.log(hour);

	if (hour === "") { //an den exei isaxthei wra
		alert("Εισάγετε ώρα");
		return;
	}
	if (g_offset !== "") {
		offset = Number(g_offset);
		offsetType = g_offsetType;
	}

	simTime = hour;
	simHour = Number( hour.split(':')[0] ) ;
	simMin = Number( hour.split(':')[1] );

	$("#sim").html('<img src="images/ajax-loader.gif" /> &nbsp; Υπό εκτέλεση...');
	simulation(simHour, simMin);
});

//Ektelesi eksomiwsis
function simulation(hour, min, flag=false){
	let time = "";
	if(hour < 10) time = '0' + hour + ":";
	else time = hour + ":";
	if(min < 10) time = time + '0' + min;
	else time = time + min;

	if(flag) {
		$("#current-time").html('<img src="images/ajax-loader.gif" /> Εκτέλεση νέας εξομοίωσης για την ώρα: ' + time +'...');
		$( "#but-before" ).prop('disabled', true);
		$( "#but-after" ).prop('disabled', true);
	}

	var color;
	var occupied = 0;
	var obj = {hour : hour, min: min};
	console.log(obj);

	$.ajax({
		url: 'simulation.php',
		method: "POST",
		data: obj,
		dataType: 'json',
		error: function(request, status, error) {
			alert(request.status + error);
		},
		success: function(response) {
			simulationData = response;

			for (i = 0; i < num; i++) {
				//vriskoume to pososto kateilimenwn thesewn diavazontas to apo to json arxeio
				let parksegm;
				if(simulationData[i].id == i) occupied = simulationData[i].occupied_park_spots;
				else occupied = -1;

				if (occupied >= 0 && occupied <= 59) color = "green";
				else if (occupied > 59 && occupied <= 84) color = "yellow";
				else if (occupied > 84 && occupied <= 100) color = "red";
				else color = "grey";
				polygons[i].setStyle({color: color, fillColor: color, opacity: 0.05, fillOpacity:0.5});

				if (polygons[i].options.park === undefined) parksegm = "";
				else parksegm = 'Συνολικά '+ polygons[i].options.park + ' θέσεις<br>';

				polygons[i].bindTooltip('<h4 style="background-color: transparent; padding: 0px; margin-top: 0;">' + parksegm + occupied.toFixed(2) + "% Κατειλημμένες θέσεις </h4>(Περίπου " + simulationData[i].free_park_spots.toFixed(0) + " Ελεύθερες θέσεις)");
			}

			$("#current-time").html("<h6> Τρέχουσα ώρα εξομοίωσης: " + time + "</h6>");
			$("#sim").html('Εκτέλεση εξομοίωσης');

			if(offset != 0) {
				getNextSimulation(hour,min);
			}
		}
	});
}

//ektelesis eksomiwsis kata metavoli wras i leptwn
function getNextSimulation(hour, min){
	var type;
	let now = new Date();
	now.setMinutes(min);
	now.setHours(hour);

	let after = new Date(now);
	if(offsetType == "hour"){
		after.setHours(after.getHours() + offset);
	}
	else if(offsetType == "min"){
		after.setMinutes(after.getMinutes() + offset);
	}

	let before = new Date(now);
	if(offsetType == "hour"){
		before.setHours(before.getHours() - offset);
		if( offset > 1) type = "ώρες";
		else type = "ώρα";
	}
	else if(offsetType == "min"){
		before.setMinutes(before.getMinutes() - offset);
		if( offset > 1) type = "λεπτά";
		else type = "λεπτό";
	}

	h_a = after.getHours();
	m_a = after.getMinutes();
	if(h_a < 10) simAfter = '0' + h_a + ":";
	else simAfter = h_a + ":";
	if(m_a < 10) simAfter = simAfter + '0' + m_a;
	else simAfter = simAfter + m_a;


	h_b = before.getHours();
	m_b = before.getMinutes();
	if(h_b < 10) simBefore = '0' + h_b + ":";
	else simBefore = h_b + ":";
	if(m_b < 10) simBefore = simBefore + '0' + m_b;
	else simBefore = simBefore + m_b;

	$("#before").html('<button id="but-before" type="button" onclick="simulation(h_b, m_b, true)">' + offset + ' ' + type + ' νωρίτερα ( ' + simBefore + ' )' +  '</button>');
	$("#after").html('<button id="but-after" type="button" onclick="simulation(h_a, m_a, true)">' + offset + ' ' + type + ' αργότερα ( ' + simAfter + ' )' + '</button>');
	$( "#but-before" ).prop('disabled', false);
	$( "#but-after" ).prop('disabled', false);
}

//Epanafora
function resetEverything() {
	for(i=0; i<num; i++){
		polygons[i].setStyle({color: "grey", fillColor: "grey", opacity: 0.1, fillOpacity:0.55});
		polygons[i].unbindTooltip();
	}
	$( "#input-offset" ).val(null);
	$( "#input-hour" ).val(null);
	$( "#input-select" ).val("hour");
	$( "#current-time" ).empty();
	$( "#before" ).empty();
	$( "#after" ).empty();

	simTime = 0;
	offset = 0;
	offsetType = 0;
	simHour = 0;
	simMin = 0;
	simBefore = 0;
	simAfter = 0;
}
<?php
ini_set('display_errors', 'Off');
header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set("Europe/Athens");

$hour = (int) $_POST["hour"]; //pernoume tin wra eksomiwsis apo simulation.js
$minutes = (int) $_POST["min"]; //pernoume ta lepta eksomiwsis apo simulation.js
$minHours = $minutes / 60;
$hour_format = $hour + $minHours;

$h1 = $hour;
$h2 = $hour + 1; //lamvanoume ipopsi kai +1 wra

if ($h2 == 24)
	$h2 = 0;

$h_avg = ($h1 + $h2) / 2; //mesos oros metaksi twn dio orwn
$h1_dev = $h1 - $h_avg;
$h2_dev = $h2 - $h_avg;
$hour_sqr_sum = pow($h1_dev, 2) + pow($h2_dev, 2);

$file_contents = file_get_contents("general_info.json"); //lipsi arithmwn poligonwn apo json arxeio
$array = json_decode($file_contents, true);
$numOfPols = $array['numOfPol'];

$results = array();

$conn = mysqli_connect("localhost", "root", "806175", "parking");
if (!$conn) {
	//gia to kathe poligono vriskoume kai apothikevoume to id, katelimenes theseis parking kai sintetagmenes kentroides tou
	//gia tin apothikeufsi se json
	for ($i=0; $i < $numOfPols; $i++) { 
		array_push($results, array(
		'id' => $i,
		'occupied_park_spots' => -1,
		'centroid' => array("x"=>0, "y"=>0)
		));
	}
	echo json_encode($results);
    die();
}


for($i=0; $i < $numOfPols; $i++) {
	//diavasma apo tin vasi
	$query1 = "SELECT `parkspots` FROM `pol_info` WHERE `id`=$i";
	$query2 = "SELECT `percent` FROM `kamp` WHERE `id`=$i AND (`hour`=$h1 OR `hour`=$h2)";
	$query3 = "SELECT `population` FROM `polygon` WHERE `tetragono_id`=$i";
	$query4 = "SELECT `x`,`y` FROM `centroids` WHERE `id`=$i";

	if ($result = $conn->query($query1)) {
		//evresi theseis parking
		if (mysqli_num_rows($result) == 0){
			$parkspots = 0;
		}
		else {
			$row = mysqli_fetch_row($result);
			$parkspots = (int) $row[0];
		}
	}
	else {
		$parkspots = 0;
	}

	if ($result = $conn->query($query2)) {
		//evresi kampilwn zitisis gia to kathe poligono me mia ek twn dio wrwn h1 i h2
		if (mysqli_num_rows($result) == 0) {
			$percent1 = 0;
			$percent2 = 0;
		}
		else {
			$row = mysqli_fetch_row($result);
			$percent1 = (float) $row[0];		
			$row = mysqli_fetch_row($result);
			$percent2 = (float) $row[0];
		}
	}
	else { //an den vrei tote percent1 kai percent2 einai isa me 0
		$percent1 = 0;
		$percent2 = 0;
	}

	if ($result = $conn->query($query3)) {
		//evresi plithismou gia kathe poligono
		if (mysqli_num_rows($result) == 0){
			$population = 0;
		}
		else {
			$row = mysqli_fetch_row($result);
			$population = (int) $row[0];
		}
	}
	else {
		$population = 0;
	}

	$perc_avg = ($percent1 + $percent2) / 2; //mesos oros twn kampilwn zitisis
	
 	$slope = ($h1_dev * ($percent1 - $perc_avg) + $h2_dev * ($percent2 - $perc_avg)) / $hour_sqr_sum; //klisi
	$intercept = $perc_avg - ($slope * $h_avg); //simio tomis me y axona
	
	$percent = ($slope * $hour_format) + $intercept;
	
	$const_demand = 0.2 * $population; //gia tous monimous katikous
	$remaining_spots = $parkspots - $const_demand; //ipolipes theseis parking

	if($remaining_spots < 0) {
		$occupied_spots_perc = 100;
		$free_spots = 0;
	}
	else {
		$free_percent = 1 - $percent;
		$free_spots = $free_percent * $remaining_spots; //eleftheres theseis parking
		$occupied_spots = $remaining_spots - $free_spots; //katilimenes theseis parking
		
		if ($parkspots != 0)
			$free_spots_perc = ($free_spots / $parkspots) * 100; //pososto eleftherwn thesewn parking
		else
			$free_spots_perc = 0;
		
		$occupied_spots_perc = 100 - $free_spots_perc; //pososto katilimenwn thesews
	}
	
	if ($result = $conn->query($query4)) {
		//evresi sintetagmenwn kentroidi
		if (mysqli_num_rows($result) == 0) {
			$centroid['x'] = 0;
			$centroid['y'] = 0;
		}
		else {
			$row = mysqli_fetch_row($result);
			$centroid['x'] = (float) $row[0]; //cast se float
			$centroid['y'] = (float) $row[1];
		}
	}
	else { //an den vrethoun isa me 0
			$centroid['x'] = 0;
			$centroid['y'] = 0;
	}

	//Prosthiki kathe fora sto array results gia tin apothikefsi tou se json
	array_push($results, array(
		'id' => $i,
		'free_park_spots' => $free_spots,
		'occupied_park_spots' => $occupied_spots_perc,
		'centroid' => $centroid
	));
}

$fp = fopen('emulationresults.json', 'w');
fwrite($fp, json_encode($results, JSON_PRETTY_PRINT));
fclose($fp);
mysqli_close($conn);

echo json_encode($results);

?>


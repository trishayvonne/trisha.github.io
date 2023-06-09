<?php
ini_set('display_errors', 'Off');
header("Content-Type: application/json; charset=UTF-8");
session_start();
//from usermap.js
$radius = (int) $_POST["r"];
$coords = $_POST["position"];
$y = (float) $coords[0];
$x = (float) $coords[1];
$EarthR = 6373;
$radius = $radius / 1000;
$strJsonFileContents = file_get_contents("general_info.json");
$array = json_decode($strJsonFileContents, true);
$num = $array['numOfPol'];
$nearbyPolygons = array();
$polygons_nearby = array();
$id_closed_found = array(); //array to store closed polygon's ids found

$conn = mysqli_connect("localhost", "root", " ", "parking");

for ($i = 0; $i < $num; $i++) {
	$query = "SELECT `id`, `x`,`y` FROM `centroids` WHERE `id`=$i";

	if ($result = $conn->query($query)) {
		if (mysqli_num_rows($result) == 0) {
			continue;
		} else {
			$row = $result->fetch_assoc();
			$id = (float)$row['id'];
			$x_p = (float)$row['x'];
			$y_p = (float)$row['y'];
			$d_lon = $x_p - $x;
			$d_lat = $y_p - $y;

			$a = (sin(deg2rad($d_lat) / 2) ** 2) + (cos(deg2rad($y)) * cos(deg2rad($y_p)) * (sin(deg2rad($d_lon) / 2) ** 2));
			$c = 2 * atan2(sqrt($a), sqrt(1 - $a));

			$dist = $EarthR * $c;
			if ($dist <= $radius) {
				array_push($nearbyPolygons, array('id' => $id, 'dist' => $dist));
				array_push($polygons_nearby, array('id' => $id, 'x_c' => $x_p, 'y_c' => $y_p));
			}
		}
	}
}

if (empty($polygons_nearby)) {
	echo json_encode([]);
	die();
}

$_SESSION['id_closed'] = $polygons_nearby;
mysqli_close($conn);
?>

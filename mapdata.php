<?php

header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 'Off'); 

function errorHandler($errno, $errstr) {
	$error = true;
	$msg = "Failed to load data";
	$error_msg = $errstr;
	$data = null;
	$response = array(
		'error' => $error,
		'msg' => $msg,
		'error_msg' => $error_msg,		
		'data' => $data
	);
	$jdata = json_encode($response);
	echo $jdata;
	die();
}

// Set user-defined error handler function
set_error_handler("errorHandler");

$coords = array();
$mysqli = new mysqli("localhost", "root", "", "parking");
$sql = "SELECT `y`,`x` FROM `info`"; //coordinates of the city
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$res = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$city = $res[0];
$sql = "SELECT MAX(`square_id`) FROM `polygon`"; //we find the polygon with the largest id to
//we know the number of polygons
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$res = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$num = $res[0]['MAX(`square_id`)'];
$num = $num + 1;

$info[0] = $city; //city coordinates
$info[1] = $num; //number of polygons

$numData = array( 'numOfPol' => $num ); //save number of polygons to array numData

for($i=0; $i < $num; $i++) {
	$sql ="SELECT `Y`,`X`  FROM `polygon` WHERE `square_id`=$i ";
	$stmt = $mysqli->prepare($sql);
	$stmt->execute();
	$result = $stmt->get_result();
	$pol = $result->fetch_all(MYSQLI_ASSOC);
	$coords[$i] = $pol;
}
$stmt->close();
$data[0] = $info;
$data[1] = $coords; //polygon coordinates

$msg = "Success";
$response = array(
	'error' => false,
	'msg' => $msg,
	'error_msg' => "",		
	'data' => $data
);

mysqli_close($mysqli);

//save number of polygons to general_info.json
$fp = fopen('general_info.json', 'w');
fwrite($fp, json_encode($numData));
fclose($fp);

$fp = fopen('polydata.json', 'w');
fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
fclose($fp);

$jdata = json_encode($response);

echo $jdata;
?>
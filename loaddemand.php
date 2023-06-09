<?php
//Loading the demand curves from the base
header("Content-Type: application/json; charset=UTF-8");
$id = (int) $_POST['id']; //load demand data for the square from map.js
ini_set('display_errors', 'Off');
$conn = mysqli_connect("localhost", "root", " ", "parking");

if (!$conn) {
    $error = true;
	$msg = "Connection failed SQL: ";
	$error_msg = mysqli_connect_error();
	$response = array(
		'error' => $error,
		'msg' => $msg,
		'error_msg' => $error_msg
	);
	
	$jdata = json_encode($response);
	echo $jdata;
	die();
}

$query = "SELECT `hour`,`percent` FROM `kamp` WHERE `id`=$id";
 
if ($result = $conn -> query($query)) {
	$num = mysqli_num_rows($result);
	if(mysqli_num_rows($result) == 0){
		$data = null;
	}
	else {			
		$data = array();
		for($i = 0; $i < 24; $i++){
			$data[$i] = null;
		}
		
		while($row = $result->fetch_assoc()){
			$hour = (int) $row['hour'];
			$percent = (float) $row['percent'];
			$data[$hour] = $percent;
		}
	}
	
	$error = false;
	$response = array(
		'error' => $error,
		'num' => $num,
		'error_msg' => $error_msg,
		'data' => $data
	);
		
}
else {
	$error = true;
	$error_msg = $conn->error();
	$response = array(
		'error' => $error,
		'error_msg' => $error_msg
	);
}

mysqli_close($conn);
$jdata = json_encode($response);
echo $jdata;

?>
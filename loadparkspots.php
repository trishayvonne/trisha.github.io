<?php

//Load parking spaces from array pol_info
//and send to map.js
header("Content-Type: application/json; charset=UTF-8");
$error = null;
$msg = null;
$data = null;
ini_set('display_errors', 'Off');
$conn = mysqli_connect("localhost", "root", " ", "parking");

if (!$conn) {
    $error = true;
	$msg = "Connection failed SQL: ";
	$error_msg = mysqli_connect_error();
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
$strJsonFileContents = file_get_contents("general_info.json");
$array = json_decode($strJsonFileContents, true);
$num = $array['numOfPol']; //find polygon numbers from xson file
$data = array();

for ($i = 0; $i < $num; $i++) {
    $query = "SELECT `parkspots` FROM `pol_info` WHERE `id`=$i";

    if ($result = $conn->query($query)) {
            if( mysqli_num_rows($result) ==0 ){
                $object = null;
            }
            else {
                $row = $result->fetch_assoc();
                $object = (int) $row['parkspots'];
            }
            $data[$i] = $object; //import parkspots to array data
    }
    else {
        $error = true;
        $msg = "Failed to load data";
        $error_msg = $conn->error;
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

}

$error = false;
$msg = "Success";
$response = array(
	'error' => $error,
	'msg' => $msg,
	'data' => $data
);

mysqli_close($conn);
$jdata = json_encode($response);
echo $jdata;

?>
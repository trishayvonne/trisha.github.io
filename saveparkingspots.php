<?php

//Enimerwsi tou pinaka pol_info gia tis theseis parking kathe poligwnou

header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 'Off');
$id = $_POST["id"]; //lipsi id poligwnou apo map.js
$park = $_POST["parkspots"]; //lipsi parkingspots apo map.js
$error_msg = null;
$conn = mysqli_connect("localhost", "root", "806175", "parking");

if (!$conn) {
    $error = true;
	$msg = "Αποτυχημένη σύνδεση SQL: ";
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

$query = "SELECT * FROM `pol_info` WHERE `id`=$id";
 
if ($result = $conn->query($query)) {
	if(mysqli_num_rows($result) == 0 ){
	    //eiagwgi stin vasi ta parkingspots gia to kathe poligwno
		$query = "INSERT INTO `pol_info` (`id`,`parkspots`) VALUES ('$id' ,'$park')";
		if (mysqli_query($conn, $query)) { //epitixis eisagwgi
			$msg = "Επιτυχής αποθήκευση πλήθους θέσεων στάθμευσης του πολυγώνου ". $id;
			$error = false;
		}
		else {
			$msg = "Σφάλμα: δεν ενημερώθηκε το πλήθος θέσεων στάθμευσης";
			$error = true;
			$error_msg = $conn->error;
		}
	}
	else {
	    //otan iparxei ginete enimerwsi
		$query = "UPDATE `pol_info` SET `parkspots`='$park' WHERE `id`=$id";
		if (mysqli_query($conn, $query)) { //epitixis enimerwsi
			$msg = "Επιτυχής αποθήκευση: Ενημερώθηκε η τιμή του πλήθους θέσεων στάθμευσης του πολυγώνου ". $id;
			$error = false;
		}
		else {
			$msg = "Σφάλμα: δεν ενημερώθηκε το πλήθος θέσεων στάθμευσης";
			$error = true;
			$error_msg = $conn->error;			
		}
	}
}

$data = null;
$response = array(
	'error' => $error,
	'msg' => $msg,
	'error_msg' => $error_msg,		
	'data' => $data
);

$jdata = json_encode($response);
mysqli_close($conn);
echo $jdata;

?>
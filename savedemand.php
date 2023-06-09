<?php

//Apothikeusi twn pososto kampilwn zitisis kai enimerwsi tou pinaka kamp
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 'Off'); 

$id = $_POST["id"];
$values = $_POST["valuesPerH"]; //pernume to pososto kampilis zitisis gia kathe wra apo to map.js

$error_msg = null;
$error = false;
$results = Array();
$what_error = Array();
$where = Array(); 

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

for ($i=0; $i < 24; $i++) { //gia oles tis wres
	
	if ($values[$i] === null){
		continue;
	}

	//epilogi apo ton pinaka kamp gia to sigkekrimeno poligwno kai gia tin kathe wra
	$query = "SELECT * FROM `kamp` WHERE `id`=$id AND `hour`=$i";
	if ($result = $conn->query($query)) {
		if (mysqli_num_rows($result) == 0){
            //ean den iparxei ston pinaka kamp gia to sigkekrimeno poligwno eggrafi gia kapia wra ginete eisagwgi
			$query = "INSERT INTO `kamp` (`id`,`hour`,`percent`) VALUES ('$id' ,'$i', '$values[$i]')";
			if (mysqli_query($conn, $query)){
					$results[$i] = "Ώρα: ". $i."  "."Επιτυχής αποθήκευση τιμής: ".$values[$i].".\n";
				}
				else {
					$error = true;
					$results[$i] =  "Ώρα: ". $i."  Μη επιτυχής αποθήκευση της τιμής ζήτησης.\n";
					array_push($what_error, $conn->error);
					array_push($where, $i);	
				}
		}
		else { //ginete enimerwsi tou pinaka an vrethikan idi iparxonta pososta gia to sigkekrimeno
		    //poligwno gia tin sigkekrimeni wra
			$row = $result->fetch_assoc();
			$percent = (float) $row['percent'];	 //pososto kampilis zitisis
			if($percent != $values[$i]){
				$query = "UPDATE `kamp` SET `percent`='$values[$i]' WHERE `id`=$id AND `hour`=$i";
				if (mysqli_query($conn, $query)) { //epitis enimerwsi
					$results[$i] = "Ώρα: ". $i."  Επιτυχής αποθήκευση: ενημέρωση τιμής. Νέα τιμή: ".$values[$i].".\n";
				}
				else {
					$error = true;
					$results[$i] =  "Ώρα: ". $i."  Μη επιτυχής αποθήκευση της τιμής ζήτησης.\n";
					array_push($what_error, $conn->error);
					array_push($where, $i);					
				}
			}
			else $results[$i] = "Ώρα: ". $i."  Επιτυχής αποθήκευση: καμμία ενημέρωση τιμής: ".$values[$i].".\n";
		}
	}
	else {
		$error = true;
		$results[$i] =  "Ώρα: ". $i."  Μη επιτυχής αποθήκευση της τιμής ζήτησης.\n";
	    array_push($what_error, $conn->error);
		array_push($where, $i);
	}
}

$msg = $results;
$response = array(
	'error' => $error,
	'msg' => $msg,
	'the_errors' => $what_error,		
	'error_pos' => $where,
	'num_of_errors' => sizeof($what_error)
);

$jdata = json_encode($response);
mysqli_close($conn);

echo $jdata;
?>
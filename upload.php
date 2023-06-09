<?php

$errors = [];
$numOfErrors = 0;
	
if (isset($_FILES) && !empty($_FILES)) {
    $fileExtensions = ['kml'];
    $fileName = $_FILES['file']['name'];
    $fileSize = $_FILES['file']['size'];
    $fileTmpName  = $_FILES['file']['tmp_name'];
    $fileType = $_FILES['file']['type'];
	$tmp = explode('.', $fileName);
	$fileExtension = strtolower(end($tmp));
	
	if (! in_array($fileExtension,$fileExtensions)) {
		array_push($errors, "Δεν επιτρέπεται αρχείο τέτοιου τύπου.");
		$numOfErrors += 1;
		$didUpload = 0;
	}

	if ($fileSize > 1000000000) {
		array_push($errors, "Το αρχείο υπερβαίνει τα 10MB.");
		$numOfErrors += 1;
		$didUpload = 0;
	}
	
	if (empty($errors) && $numOfErrors == 0) {
		$fileName = "kmlfile.kml";
		$didUpload = move_uploaded_file($fileTmpName,"uploads/".$fileName);
		
		if ($didUpload) {
			//$message =  "Το αρχείο φορτώθηκε επιτυχώς.";
			$response = "Ok";
		}
		else $response = "Σφάλμα. Αποτυχία φόρτωσης αρχείου.";
	} 
	else {
		$response = implode( "\n", $errors);
	}
}
else {
	$response = "Αποτυχία φόρτωσης αρχείου";
}

echo $response;

?>

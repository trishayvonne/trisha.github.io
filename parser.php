<?php

foreach (glob("uploads/*.kml") as $file) {} //find xml files in the directory
$kml = simplexml_load_file($file); //load file found
$arr = array();
$population = array();
$kml_inst = new DomDocument();
$kml_inst->load($file);
$conn = mysqli_connect("localhost", "root", "", "parking");
$descr_tag = $kml_inst->getElementsByTagName("description"); //finds all xml elements with descriptor names
$i=0;

require_once('getCentroid.php'); //for finding the centroids of each polygon

//Emptying tables before inserting into database
mysqli_query($conn, 'TRUNCATE TABLE centroids');
mysqli_query($conn, 'TRUNCATE TABLE kamp');
mysqli_query($conn, 'TRUNCATE TABLE polygon');
mysqli_query($conn, 'TRUNCATE TABLE pol_info');
mysqli_query($conn, 'TRUNCATE TABLE info');

if (!$conn) {
    die("SQL connection failed: " . mysqli_connect_error());
}

//Find population from the xml file for each polygon
foreach($descr_tag as $d) {
    $pop = simplexml_load_string("<base>".$d->nodeValue."</base>");
    $population[]  = (string)$pop->xpath("//li[strong/span[text()='Population']]")[0]->span;
}

//Find longitude, latitude, altitude hml and enter values in the info table
foreach($kml->Document as $doc) {
    $map_longitude = $doc->LookAt->longitude;
    $map_latitude = $doc->LookAt->latitude;
    $map_altitude = $doc->LookAt->altitude;
	$query = "INSERT INTO `info` (`x`, `y`) VALUES ('$map_longitude', '$map_latitude')";
	if (mysqli_query($conn, $query)) {}
    else {
         echo "Error: " . $query . "<br>" . mysqli_error($conn);
         echo "<br />";
    }
}

$polygon_counter = 0;
//Calculation of x and y coordinates of each polygon
foreach($kml->Document->Folder->Placemark as $pm) {
    if(isset($pm->MultiGeometry->Polygon)) { //an exei vrethei element 'Polygon'
        $coordinates = $pm->MultiGeometry->Polygon->outerBoundaryIs->LinearRing->coordinates;
        $cordsData = trim($coordinates); //delete vowels
        $explodedData = explode("\n", $cordsData); //split the string when it finds a newline
        $explodedData = array_map('trim', $explodedData);

            //the coordinates we find for each polygon are in the form:
            //<coordinates>x1,y1 x2,y2 ... </coordinates>
            foreach ($explodedData as $index => $coordinateString) {
                //at each position of the coordinateSet array the simia are stored in form x,y
                $coordinateSet = array_map('trim', explode(' ', $coordinateString));
                //save in format x,y
            }

            $k = 0;
            $counter_coordinateSet = count($coordinateSet);
            $points_counter = 2 * count($coordinateSet); //we have x and h for each coordinate

            for ($m = 0; $m < $points_counter; $m++) {
                $points = explode(',', $coordinateSet[$m]); //delete ',' at each position of coordinateSet array
                $arr[$polygon_counter][$k] = $points[0]; //sto points[0] exw to x
                $k++;
                $arr[$polygon_counter][$k] = $points[1]; //in points[1] I have n
                $k++;
            }

                //Call function getCentroid and store its centroid
                //polygon in the x_centroid and n_centroid variables
                list($x_centroid,$y_centroid) = getCentroid($arr, $points_counter, $polygon_counter);
                $query1 = "";
                //entering centroids into the centroid table for each polygon
                $query1 = "INSERT INTO `centroids` (`id`, `x`, `y`) VALUES (NULL, $x_centroid, $y_centroid)";
                if (mysqli_query($conn, $query1)) {}
                 else {
                    echo "Error: " . $query1 . "<br>" . mysqli_error($conn);
                    echo "<br />";
                 }

              $counter = 0;

              //Insert the elements of each polygon into the polygon table
              while($counter < $points_counter) {
                  $query = "";
                  $query = "INSERT INTO `polygon` (`id`,`population`,`square_id`,`X`,`Y`) VALUES (NULL, '" . (int)$population[$polygon_counter] . "', '" . $polygon_counter ."' , '" . $arr[$polygon_counter][$counter] . "', '" . $arr[$polygon_counter][$counter+1] . "')";
                  $counter=$counter + 2;;
                  if (mysqli_query($conn, $query)) {} //successful execution
                  else {
                      echo "Error: " . $query . "<br>" . mysqli_error($conn);
                      echo "<br />";
                  }
              }
              $polygon_counter++;
    }
}

mysqli_close($conn);
echo "Ok.";

?>
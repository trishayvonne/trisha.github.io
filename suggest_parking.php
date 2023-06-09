
<?php
//1.Dimiourgia toswn tixaiwn simiwn oses einai kai oi eleftheres theseis
//parking tou kathe poligwnou, me 50 metra apostasi apo to kentroidi tou kathe poligwnou pou
//exon vrethei entos tis apostasis pou orise o xristis.
//2.Eisodos twn tixaiwn simiwn ston algorithmo DBscan kai eksagwgi twn cluster
//3.Epilogi tou cluster me ta perissotera simia

ini_set('display_errors', 'Off');
header("Content-Type: application/json; charset=UTF-8");
require_once('dbscan.php');
require_once('findnearpols.php');
$EarthR = 6373;
$coords = $_POST["position"];
$x_user = (float) $coords[1];
$y_user = (float) $coords[0];
$random_x_points = [];
$random_y_points = [];
$circle_radius = 50/111000; //aktina 50 metrwn
$strJsonFileContents1 = file_get_contents("emulationresults.json");
$items = json_decode($strJsonFileContents1,true);
$conn = mysqli_connect("localhost", "root", "806175", "parking");
$random_x_y_points = array();
$free_park_spots = 0;

//Dimiourgia tixaiou arithmou float
function randomFloat($min = 0, $max = 1) {
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}
foreach($_SESSION['id_closed'] as $polygon) { //ta polygons_nearby sto findnearpols einai se SESSION
    //gia ola ta poligwna pou exoun vrethei entos tis aktinas pou orise o xristis
    $temp_x = [];
    $temp_y = [];
    foreach ($items as $line) {
        if ($line['id'] ==  $polygon['id']) {
            //evresi eleftherwn thesewn tou poligwnou
            $free_park_spots = intval($line['free_park_spots']);
        }
    }
    //echo "\n";
    //echo " Polygon X_centroid: ", $polygon['x_c'];
    //echo "\n";
    //echo " Polygon Y_centroid: ", $polygon['y_c'];

    //print_r($free_park_spots);
    if ($free_park_spots > 0) {
        for ($k = 0; $k < $free_park_spots; $k++) {
            //dimiourgia toswn tixaiwn simiwn osa kai ta free_park_spots

            //Dimiourgia tixaiwn simiwn me apostasi 50 metra apo to x0 kai y0 pou einai
            // ta kentroidi kathe poligwnou

            $y0 = $polygon['y_c'];
            $x0 = $polygon['x_c'];
            $u = randomFloat(0,1);
            $v = randomFloat(0,1);
            $w = $circle_radius * sqrt($u);
            $t = 2 * pi() * $v;
            $xx = $w * cos($t);
            $y1 = $w * sin($t);
            $x1 = $xx / cos($y0);

            $y = $y0 + $y1; //tixaio simio y
            $x = $x0 + $x1; //tixaio simio x


            array_push($temp_x, $x);
            array_push($temp_y, $y);
            array_push($random_x_points, $x);
            array_push($random_y_points, $y);

        }
        //to array random_x_y_points periexei ola ta tixea simia x kai y pou dimiourgithikan gia to kathe poligwno
        array_push($random_x_y_points,array('id' => $polygon['id'], 'x_points' => $temp_x, 'y_points' => $temp_y));
    }

}
//print_r($random_x_y_points);

//xrisimopiite gia tin eisodo tou dbscan
for ($i = 0; $i < count($random_x_points); $i++)
    $point_ids[] = $i;

//dimiourgia distance_matrix array gia ton ipologismo twn apostasewn metaksi twn tixaiwn simiwn
$distance_matrix = [];

for ($i = 0; $i < count($random_x_points); $i++)
    $distance_matrix[$i] = [];

for ($i = 0; $i < count($random_x_points); $i++) {
    for ($j = 0; $j < count($random_x_points); $j++) {
        //Ipologismos apostasis metaksi twn tixaiwn simiwn
        $d_lon = abs($random_x_points[$i] - $random_x_points[$j]);
        //print_r($d_lon);
        $d_lat = abs($random_y_points[$i] - $random_y_points[$j]);

        $a = (sin(deg2rad($d_lat) / 2) ** 2) + (cos(deg2rad($random_y_points[$j])) * cos(deg2rad($random_y_points[$i])) * (sin(deg2rad($d_lon) / 2) ** 2));
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance_matrix[$i][$j] = ($EarthR * $c) * 1000; //metatropi km se metra gia kaliteri akrivia
    }
}
//print_r($distance_matrix);
mysqli_close($conn);

$DBSCAN = new dbscan($distance_matrix, $point_ids);
$epsilon = 50; //aktina tis geitonias apo ena simio x
$minpoints = 6; //elaxistos arithmos simiwn gia na dimiourgithei to cluster entos tis aktinas epsilon

$clusters = $DBSCAN->dbscan($epsilon, $minpoints);
$points = 0;
$max_points = 0;
$m = 0;

foreach ($clusters as $index => $cluster)  {
    if (sizeof($cluster) > 0) {
        $points = 0;

        foreach ($cluster as $member_point_id) {
            //metrisi arithmwn simiwn pou periexetai se kathe cluster pou exei dimiourgithei
            $points++;
        }
    }
    else $points = 0;

    //vriskoume to cluster pou periexei ta perissotera simia
    //lamvanoume ipopsi kai tin periptwsi pou clusters exon ton idio arithmo max simiwn
    if ($points > $max_points) {
        $max_points = $points;
        $max_clusters[$m] = $index;
    }
}

//an den exoun vrethei cluster
if ($max_points < 1) {
    echo json_encode([]);
    die();
}

foreach ($clusters as $index => $cluster)  {
    if (sizeof($cluster) > 0) {
        $points = 0;
        //echo "\n";
        //echo  "****************************\n";

        //echo ' Cluster number '.($index).':';
        foreach ($cluster as $member_point_id) {
            //echo $member_point_id, ' ';
            $points++;
        }

    }
    else $points = 0;

    if ($points == $max_points) {
        $max_clusters[$m++] = $index;
    }
}

$x = 0.0;
$y = 0.0;
$z = 0.0;
$cluster_centroid = array();

for ($i = 0; $i < count($max_clusters); $i++) {
    $index = $max_clusters[$i];
    $k = 1;
    $sum_x = 0;
    $sum_y = 0;

    foreach($clusters[intval($index)] as $id_cluster => $points_of_cluster) {
        $sum_x = $sum_x + $random_x_points[$points_of_cluster];
        $sum_y = $sum_y + $random_y_points[$points_of_cluster];
        $k++;
    }
    //ipologismos tou kentroides tou kathe cluster
    $cluster_centroid_x[$i] = $sum_x / ($k - 1);
    $cluster_centroid_y[$i] = $sum_y / ($k - 1);
    $d_lon = $cluster_centroid_x[$i] - $x_user;
    $d_lat = $cluster_centroid_y[$i] - $y_user;
    $a = (sin(deg2rad($d_lat) / 2) ** 2) + (cos(deg2rad($y_user)) * cos(deg2rad($cluster_centroid_y[$i])) * (sin(deg2rad($d_lon) / 2) ** 2));
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    //evresi apostasis apo kentroides cluster me tou xristi kai apothikeusi sto json
    $dist_user_to_cluster = ($EarthR * $c) * 1000;
    array_push($cluster_centroid, array( 'centroid_X' => $cluster_centroid_x[$i], 'centroid_Y' => $cluster_centroid_y[$i], 'distance' => $dist_user_to_cluster));

    //eksagwgi se json
    $fp = fopen('cluster_centroid.json', 'w');
    fwrite($fp, json_encode($cluster_centroid[$i]));
    fclose($fp);
}

echo json_encode($cluster_centroid);

?>
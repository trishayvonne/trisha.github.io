<?php
//Function for calculating the centroids of each polygon
function getCentroid($array, $points_counter, $polygon_counter)
{
    $area = 0;
    $i = 0;

    while($i < $points_counter) {
        $x1 = (float)$array[$polygon_counter][$i];
        $y1 = (float)$array[$polygon_counter][$i+1];
        $x2 = (float)$array[$polygon_counter][$i+2];
        $y2 = (float)$array[$polygon_counter][$i+3];
        $area += (float) ($x1 * $y2) - ($y1 * $x2);
        $i = $i + 2;
    }

    $area = abs(($area / 2));
    $cx = 0;
    $cy = 0;
    $i = 0;

    while($i < $points_counter) {
        $x1 = (float)$array[$polygon_counter][$i];
        $y1 = (float)$array[$polygon_counter][$i+1];
        $x2 = (float)$array[$polygon_counter][$i+2];
        $y2 = (float)$array[$polygon_counter][$i+3];
        $p = (float)($x1 * $y2) - ($y1 * $x2);
        $cx += (float)($x1 + $x2) * $p;
        $cy += (float)($y1 + $y2) * $p;
        $i = $i + 2;
    }

    $cx = -$cx / (6 * $area);
    $cy = -$cy / (6 * $area);

    return array($cx, $cy);
}
?>

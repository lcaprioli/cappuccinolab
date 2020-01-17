<?php
// Constants
$firestore_key = "AIzaSyAnwLYRe1EYDsfDmhbRmx1tiCAgYeLx4ys";
$project_id = "fir-tabu";
$endpoint = "https://firestore.googleapis.com/v1beta1/projects/";
$table = "partners";

$url = $endpoint.$project_id."/databases/(default)/documents/".$table;
$curl = curl_init();
curl_setopt( $curl, CURLOPT_URL, $url );
curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
$response = curl_exec( $curl );
curl_close( $curl );
// Show result
echo $response . "\n";
echo "<br><br><br> ------------------- <br><br>";

    
?>
<?php
// Constants
$origem["firestore_key"] = "AIzaSyAnwLYRe1EYDsfDmhbRmx1tiCAgYeLx4ys";
$origem["project_id"] = "fir-tabu";

$destino["firestore_key"] = "AIzaSyBepQm6CZohGCwbEZx61XD-dTKQsCGFP9g";
$destino["project_id"] = "fir-mdm-ef0dd";

$endpoint = "https://firestore.googleapis.com/v1beta1/projects/";
$table = "users";

$url = $endpoint.$origem["project_id"]."/databases/(default)/documents/".$table;
$curl = curl_init();
curl_setopt( $curl, CURLOPT_URL, $url );
curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
$response = curl_exec( $curl );
curl_close( $curl );
$listaDocs = json_decode($response);

foreach ($listaDocs as $docs){

    foreach($docs as $doc)
    {
        $fields = $doc->fields;
        $itemID = substr($doc->name,strrpos($doc->name,$table."/")+strlen($table)+1,20);
        $object = ["fields" => (object)$fields];
        $json = json_encode($object);
         $url = $endpoint.$destino["project_id"]."/databases/(default)/documents/".$table."/";
         $curl = curl_init();
     
         curl_setopt_array($curl, array(
             CURLOPT_RETURNTRANSFER => true,
             CURLOPT_CUSTOMREQUEST => 'POST',
             CURLOPT_HTTPHEADER => array('Content-Type: application/json',
                 'Content-Length: ' . strlen($json),
                 'X-HTTP-Method-Override: POST'),
             CURLOPT_URL => $url . '?key='.$destino["firestore_key"],
             CURLOPT_USERAGENT => 'cURL',
             CURLOPT_POSTFIELDS => $json
         ));
         $response = curl_exec( $curl );
         curl_close( $curl );
         echo $response;
    }

}

    
?>
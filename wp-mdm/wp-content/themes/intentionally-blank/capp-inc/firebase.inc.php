<?php

function saveDataFirebase($table,$data)
{
    global $endpoint, $project_id, $firestore_key;

	$object = ["fields" => (object)$data];
    $json = json_encode($object);

    $url = $endpoint.$project_id."/databases/(default)/documents/".$table."/";
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array('Content-Type: application/json',
            'Content-Length: ' . strlen($json),
            'X-HTTP-Method-Override: POST'),
        CURLOPT_URL => $url . '?key='.$firestore_key,
        CURLOPT_USERAGENT => 'cURL',
        CURLOPT_POSTFIELDS => $json
    ));
    $response = curl_exec( $curl );
    curl_close( $curl );
    write_log("salva ");
    write_log($table);
    write_log($object);
    write_log($response);
    if(strlen(substr($response,strrpos($response,$table."/")+strlen($table)+1,20)) == 20){
        return substr($response,strrpos($response,$table."/")+strlen($table)+1,20);
    }
    else{
        return false;
    }
    
    
}

function editDataFirebase($table,$index,$data){

    global $endpoint, $project_id, $firestore_key;
    
    $object = ["fields" => (object)$data];
    $json = json_encode($object);
    $url = $endpoint.$project_id."/databases/(default)/documents/".$table."/".$index;
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'PATCH',
        CURLOPT_HTTPHEADER => array('Content-Type: application/json',
            'Content-Length: ' . strlen($json),
            'X-HTTP-Method-Override: PATCH'),
        CURLOPT_URL => $url . '?key='.$firestore_key,
        CURLOPT_USERAGENT => 'cURL',
        CURLOPT_POSTFIELDS => $json
    ));

    $response = curl_exec( $curl );
    write_log("edita ");
    write_log($table);
    write_log($response);
    curl_close( $curl );
    return $response;
}

function deleteDataFirebase($table,$index){

    global $endpoint, $project_id, $firestore_key;
    
    $url = $endpoint.$project_id."/databases/(default)/documents/".$table."/".$index;
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_HTTPHEADER => array('Content-Type: application/json',
            'X-HTTP-Method-Override: DELETE'),
        CURLOPT_URL => $url . '?key='.$firestore_key,
        CURLOPT_USERAGENT => 'cURL'
    ));

    $response = curl_exec( $curl );
    curl_close( $curl );
    write_log("delete");
    write_log($response);
    return $response;

}


function montaArray($arrai){

    $payload = [
        'values' => [],
    ];
    $payload['values'][] = $arrai;
    
    return $payload;
}

function montaArrayFromMap($arrai){

    $payload = [
        'values' => [],
    ];
    foreach($arrai as $mapItem){
       
        $payload['values'][] = $mapItem;

    }
    
    
    return $payload;
}

function montaMap($map){

	$payload = [
        'fields' => [],
	];
	foreach($map as $key => $value){
		$payload['fields'][$key] = $value;
	}
    return $payload;
}

?>
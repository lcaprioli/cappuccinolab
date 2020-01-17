
<?php 
$firestore_key = "e444e1c0e12f358d55c619231c07cc0aeca2ede9";
$project_id = "teste-18089";

//faz a chamada para gravar os dados
function escreveNovoDado($tabela,$json){

    global $firestore_key,$project_id;

    $url = "https://firestore.googleapis.com/v1beta1/projects/".$project_id."/databases/(default)/documents/".$tabela."/";
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
   // $newid = substr($response,strrpos($response,"/")+1,20);
	//return $newid;
	return $response;
}
$json = '{"fields":{"id":{"integerValue":106},"name":{"stringValue":"aaaaaa"},"status":{"stringValue":"publish"},"description":{"stringValue":"aaaaaaa"},"short_description":{"stringValue":""},"price":{"doubleValue":"1"},"stock_quantity":{"integerValue":0},"weight":{"stringValue":""},"length":{"stringValue":""},"width":{"stringValue":""},"height":{"stringValue":""},"menu_order":{"integerValue":0},"image_id":{"stringValue":""},"cats":{"arrayValue":{{"integerValue":23},{"integerValue":24}}}}}';

$retorno = escreveNovoDado("produtos",$json);    
echo $retorno;

?>

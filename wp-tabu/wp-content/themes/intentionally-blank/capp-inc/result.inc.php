<?php
function createResult($post_id, $titulo, $user_id, $order_id, $product_id, $status, $url_resultado){
  
$resultData = [];
$resultData["paciente"] = $user_id;
$resultData["pedido"] = $order_id;
$resultData["produto"] = $product_id;
$resultData["status"] = $status;
$resultData["url_resultado"] = $url_resultado;

$resultJSON = resultCreateJSON($post_id, $resultData);
if(get_post_meta($post_id, 'firebaseID', true))
{
    $firebaseID = get_post_meta($post_id, 'firebaseID', true);
    editDataFirebase("results",$firebaseID,$resultJSON);
}
else
{
    $newid = saveDataFirebase("results",$resultJSON);
    update_post_meta($post_id, "firebaseID", $newid);
}
    user_create(userCreateJSON($user_id), $user_id);
}
add_action( 'save_post_resultado', 'action_resultado_save',12,3 );
function action_resultado_save($post_id, $post, $update) {
   //save stuff
   if(isset($_POST["acf"]))
    {
        $postData = $_POST["acf"];
        $keys = array_keys($postData);
        createResult($post_id, get_the_title($post_id), $postData[$keys[0]], $postData[$keys[1]], $postData[$keys[2]], $postData[$keys[4]], $postData[$keys[3]]);
    }
}


function beforeDeleteResult($post_id){

	$firebaseID = get_post_meta($post_id, 'firebaseID', true);
	if($firebaseID)
	{
		deleteDataFirebase("results",$firebaseID);
    } 
    $user_id = get_field('paciente', $post_id);
	user_create(userCreateJSON($user_id), $user_id);
 
}
function resultCreateJSON($post_id,$resultData)
{
  //trata o caminho absoluto do arquivo
    if($resultData["url_resultado"] != "")
    {
        $result_firebase_data["url_result"] = ["stringValue" => wp_get_attachment_url( $resultData["url_resultado"], '')];
    }
    else{
        $result_firebase_data["url_result"] = ["nullValue" => null];    
    }
        
    $result_firebase_data["id_wordpress"] = ["integerValue" => $post_id]; 
    $result_firebase_data["product"] = ["stringValue" => get_post_meta($resultData["produto"], 'firebaseID', true)]; 
    $result_firebase_data["order"] = ["stringValue" => get_post_meta($resultData["pedido"], 'firebaseID', true)]; 
    $result_firebase_data["patient"] = ["stringValue" => get_user_meta($resultData["paciente"],'firebaseID', true)]; 
    
    //checa se o resultado possui outros status
    $status_history = get_post_meta($post_id, "_status_history");
    if($status_history)
    {
	    foreach ($status_history as $key => $item ){


            $statusDate = explode(",",$item)[0];
            $statusInfo = explode(",",$item)[1];
		    $statusItem["date"] = ["timestampValue" => convertDate($statusDate)];
		    $statusItem["status"] = ["stringValue" => $statusInfo];
		    $statusList[] = ["mapValue" => montaMap($statusItem)];
        }
    }

    add_post_meta($post_id,"_status_history",wp_date("Y-m-d G:i:s").",".$resultData["status"]);

    $statusItem["date"] = ["timestampValue" => convertDate(wp_date("Y-m-d G:i:s"))];
	$statusItem["status"] = ["stringValue" => $resultData["status"]];
    $statusList[] = ["mapValue" => montaMap($statusItem)];
	
    $result_firebase_data["status_history"] = ["arrayValue" => montaArrayFromMap($statusList)];

    return $result_firebase_data;

}
?>
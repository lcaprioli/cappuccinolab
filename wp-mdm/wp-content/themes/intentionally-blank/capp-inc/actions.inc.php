<?php
function createAction($post_id,$address, $date, $local, $description){
    
    $actionData = [];
    $actionData["address"] = $address;
    $actionData["date"] = $date;
    $actionData["local"] = $local;
    $actionData["description"] = $description;
    $actionData["title"] = get_post($post_id)->post_title;
    $actionJSON = actionCreateJSON($post_id, $actionData);

    if(get_post_meta($post_id, 'firebaseID', true))
    {
        $firebaseID = get_post_meta($post_id, 'firebaseID', true);
        editDataFirebase("actions",$firebaseID,$actionJSON);
    }
    else
    {
        $newid = saveDataFirebase("actions",$actionJSON);
        update_post_meta($post_id, "firebaseID", $newid);
    }
    
}
add_action( 'save_post_acao', 'action_acao_save',12,3 );
function action_acao_save($post_id, $post, $update) {
   //save stuff
   if(isset($_POST["acf"]))
    {
        $postData = $_POST["acf"];
        $keys = array_keys($postData);
        createAction($post_id, $postData[$keys[0]], $postData[$keys[1]],$postData[$keys[2]],$postData[$keys[3]]);
    }
}


function beforeDeleteAction($post_id){

	$firebaseID = get_post_meta($post_id, 'firebaseID', true);
	if($firebaseID)
	{
		deleteDataFirebase("actions",$firebaseID);
    } 

}
function actionCreateJSON($post_id,$actionData)
{
    $action_firebase_data["date"] = ["timestampValue" => convertDate($actionData["date"])];
    $local = $actionData["local"];
    $action_firebase_data["local"] = ["stringValue" => get_post_meta($local, 'firebaseID', true)];

    $action_firebase_data["title"] = ["stringValue" => $actionData["title"]];
    $action_firebase_data["address"] = ["stringValue" => $actionData["address"]];
    $action_firebase_data["description"] = ["stringValue" => $actionData["description"]];

    return $action_firebase_data;

}
?>
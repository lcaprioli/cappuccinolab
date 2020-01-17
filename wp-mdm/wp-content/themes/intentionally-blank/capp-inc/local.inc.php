<?php
 add_action( 'save_post_local', 'action_local_save',10,3 );
 function action_local_save($post_id, $post, $update) {
	//save stuff
	if(isset($_POST["acf"]))
 	{
		$partnerData = localCreateJSON($post_id, $_POST["acf"]);
		if(get_post_meta($post_id, 'firebaseID', true))
		{
			$firebaseID = get_post_meta($post_id, 'firebaseID', true);
			editDataFirebase("locals",$firebaseID,$partnerData);
		}
		else
		{
			$newid = saveDataFirebase("locals",$partnerData);
			update_post_meta($post_id, "firebaseID", $newid);
		}
 	}
 }
 
 
function deleteLocal($post_id){

	$firebaseID = get_post_meta($post_id, 'firebaseID', true);
	if($firebaseID)
	{
		deleteDataFirebase("locals",$firebaseID);
	} 

}

function localCreateJSON($post_id,$postData)
{
	$keys = array_keys($postData);

    $local_data  = [
		"id_wordpress" => ["integerValue" => $post_id],
		"name" => ["stringValue" => get_the_title($post_id)],
		"address" => ["stringValue" => $postData[$keys[0]]],
		"city" => ["stringValue" => $postData[$keys[1]]],
		"state" => ["stringValue" => $postData[$keys[2]]],
		"zip_code" => ["stringValue" => $postData[$keys[3]]]
	];
		$location =  explode(",",$postData[$keys[4]]);
		$local_data["location"] = ["geoPointValue" => [ "latitude" => $location[0], "longitude" => $location[1] ]];
		

    return $local_data;

}
?>
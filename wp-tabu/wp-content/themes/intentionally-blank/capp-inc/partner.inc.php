<?php
 add_action( 'save_post_parceiro', 'action_parceiro_save',10,3 );
 function action_parceiro_save($post_id, $post, $update) {
	//save stuff
	if(isset($_POST["acf"]))
 	{
		$partnerData = partnerCreateJSON($post_id, $_POST["acf"]);
		if(get_post_meta($post_id, 'firebaseID', true))
		{
			$firebaseID = get_post_meta($post_id, 'firebaseID', true);
			editDataFirebase("partners",$firebaseID,$partnerData);
		}
		else
		{
			$newid = saveDataFirebase("partners",$partnerData);
			update_post_meta($post_id, "firebaseID", $newid);
		}
 	}
 }
 
 
function beforeDeletePartner($post_id){

	$firebaseID = get_post_meta($post_id, 'firebaseID', true);
	if($firebaseID)
	{
		deleteDataFirebase("partner",$firebaseID);
	} 

}

function partnerCreateJSON($post_id,$postData)
{
	$keys = array_keys($postData);

    $partner_data  = [
		"id_wordpress" => ["integerValue" => $post_id],
		"name" => ["stringValue" => get_the_title($post_id)],
		"address" => ["stringValue" => $postData[$keys[0]]],
		"city" => ["stringValue" => $postData[$keys[1]]],
		"state" => ["stringValue" => $postData[$keys[2]]],
		"zip_code" => ["stringValue" => $postData[$keys[3]]],
		"mobile" => ["stringValue" => $postData[$keys[4]]],
		"email" => ["stringValue" => $postData[$keys[5]]],
		"type" => ["stringValue" => $postData[$keys[6]]]
	];
		$location =  explode(",",$postData[$keys[8]]);
		$partner_data["location"] = ["geoPointValue" => [ "latitude" => $location[0], "longitude" => $location[1] ]];
		
		//trata o caminho absoluto da imagem
		if($postData[$keys[7]] != "")
		{
			$partner_data["url_profile"] = ["stringValue" => wp_get_attachment_image_url( $postData[$keys[7]], '')];
		}
		else{
			$partner_data["url_profile"] = ["nullValue" => null];    
		}

    return $partner_data;

}
?>
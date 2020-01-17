<?php

add_action( 'user_register', 'myplugin_registration_save', 10, 1 );
function myplugin_registration_save( $user_id ) {

	//verifica se é do REST - post vem vazio
	if(count($_POST) > 0)
	{
		user_create(userCreateJSON($user_id),$user_id);
	}

}
//add_action('edit_user_profile_update', 'crf_update_profile_fields');
 function update_profile_firebase($user_id) {
    $json_user = userCreateJSON($user_id);
    user_create($json_user, $user_id);
 }

//add_action( 'personal_options_update', 'crf_update_profile_fields' );
add_action( 'profile_update', 'crf_update_profile_fields' );
function crf_update_profile_fields($user_id) {
	
		user_create(userCreateJSON($user_id),$user_id);

}

function userCreateJSON($user_id){

    $user_obj = get_user_by('id', $user_id);
//	write_log($user_obj);
	write_log(get_user_meta($user_id, 'billing_address_1', true));
	$userJSON["id_wordpress"] = ["integerValue" => $user_id];
	$userJSON["email"] = ["stringValue" => $user_obj->user_email];
	$userJSON["genre"] = ["stringValue" => get_user_meta($user_id, 'billing_sex', true)];
	$userJSON["name"] = ["stringValue" => get_user_meta($user_id, 'billing_first_name', true)];
	$userJSON["last_name"] = ["stringValue" => get_user_meta($user_id, 'billing_last_name', true)];
	$userJSON["username"] = ["stringValue" => $user_obj->user_login];


	global $wpdb;
	global $database_name;

	// pegar pedidos
	$orders = $wpdb->get_results("SELECT * FROM ". $database_name. ".wp_posts a, ". $database_name. ".wp_postmeta b where a.ID = b.post_id and a.post_type = 'shop_order' and a.post_status <> 'trash' and b.meta_value = '$user_id' and b.meta_key = '_customer_user'", ARRAY_A);

	if(!empty($orders)){
		$arr_orders = [];
		foreach ($orders as $order) {
			$post_id = $order['ID'];
			if(get_post_meta($post_id, 'firebaseID', true)){
				$arr_orders[] = ["stringValue" => get_post_meta($post_id, 'firebaseID', true)];
			}
			else{
				$arr_orders[] = ["stringValue" => $post_id];
			}
		}
		$userJSON["orders"] = ["arrayValue"  => montaArray($arr_orders)];
	}

	// pegar resultados
	$results = $wpdb->get_results("SELECT * FROM ". $database_name. ".wp_posts a, ". $database_name. ".wp_postmeta b where a.ID = b.post_id and a.post_type = 'resultado' and a.post_status <> 'trash' and b.meta_value = '$user_id' and b.meta_key = 'paciente'", ARRAY_A);

	if(!empty($results)){
		$arr_results = [];
		foreach ($results as $result) {
			$post_id = $result['ID'];
			if(get_post_meta($post_id, 'firebaseID', true) != ""){
				$arr_results[] = ["stringValue" => get_post_meta($post_id, 'firebaseID', true)];
			}
			else{
				$arr_results[] = ["stringValue" => $post_id];
			}
		}
		$userJSON["results"] = ["arrayValue"  => montaArray($arr_results)];
	}


	$billing_information["city"] = ["stringValue" => get_user_meta($user_id, 'billing_city', true)];
	$billing_information["state"] = ["stringValue" => get_user_meta($user_id, 'billing_state', true)];
	$billing_information["email"] = ["stringValue" => get_user_meta($user_id, 'billing_email', true)];
	$billing_information["name"] = ["stringValue" => get_user_meta($user_id, 'billing_first_name', true)];
	$billing_information["mobile"] = ["stringValue" => get_user_meta($user_id, 'billing_phone', true)];
	$billing_information["address"] = ["stringValue" => get_user_meta($user_id, 'billing_address_1', true)];
	$billing_information["last_name"] = ["stringValue" => get_user_meta($user_id, 'billing_last_name', true)];
	$billing_information["zip_code"] = ["stringValue" => get_user_meta($user_id, 'billing_postcode', true)];
	$userJSON["billing_information"] = ["mapValue" => montaMap($billing_information)];

	$shipping_information["city"] = ["stringValue" => get_user_meta($user_id, 'shipping_city', true)];
	$shipping_information["state"] = ["stringValue" => get_user_meta($user_id, 'shipping_state', true)];
	$shipping_information["name"] = ["stringValue" => get_user_meta($user_id, 'shipping_first_name', true)];
	$shipping_information["address"] = ["stringValue" => get_user_meta($user_id, 'shipping_address_1', true)];
	$shipping_information["last_name"] = ["stringValue" => get_user_meta($user_id, 'shipping_last_name', true)];
	$shipping_information["zip_code"] = ["stringValue" => get_user_meta($user_id, 'shipping_postcode', true)];
	$userJSON["shipping_information"] = ["mapValue" => montaMap($shipping_information)];
    
    return $userJSON;
}

function user_create($json_user, $user_id){

	if(get_user_meta($user_id, 'firebaseID', true))
	{
		$firebaseID = get_user_meta($user_id, 'firebaseID', true);
		editDataFirebase("users",$firebaseID,$json_user);
	}
	else
	{
		$newid = saveDataFirebase("users",$json_user);
		update_user_meta($user_id, "firebaseID", $newid);
    }
}

add_action( 'delete_user', 'custom_remove_user', 10 );
function custom_remove_user($user_id){
	$firebaseID = get_user_meta($user_id, 'firebaseID', true);
	if($firebaseID)
	{
		deleteDataFirebase("users",$firebaseID);
	} 
}


?>
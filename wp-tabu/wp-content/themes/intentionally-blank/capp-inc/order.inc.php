<?php

add_action( 'woocommerce_product_options_advanced', 'misha_adv_product_options');
function misha_adv_product_options(){
 
	echo '<div class="options_group">';
 
	woocommerce_wp_checkbox( array(
		'id'      => 'has_result',
		'value'   => get_post_meta( get_the_ID(), 'has_result', true ),
		'label'   => 'Tem resultado?',
		'desc_tip' => true,
		'description' => 'Define se este produto tem resultado ou não.',
	) );
 
	echo '</div>';
 
}
 
 
add_action( 'woocommerce_process_product_meta', 'misha_save_fields', 10, 2 );
function misha_save_fields( $id, $post ){
 
	if( !empty( $_POST['has_result'] ) ) {
		update_post_meta( $id, 'has_result', $_POST['has_result'] );
	} else {
		delete_post_meta( $id, 'has_result' );
	}
 
}

function addOrder($order_id)
{
	$order = new WC_Order( $order_id );

	if($order->get_items()){


		$order->update_meta_data( '_status_history', array( current_time( 'mysql' ) => 'pending' ) );
		$order->save(); // Save
		$orderData = orderCreateJSON($order_id,$order);
		$newid = saveDataFirebase("orders",$orderData);
		update_post_meta($order_id, "firebaseID", $newid);
		$user_id = get_post_meta($order_id, '_customer_user', true);
		$userName = get_user_meta($user_id, 'billing_first_name', true);
		//cria os resultados 
		$order_items = $order->get_items();
	
		foreach ($order_items as $item_id => $item){
			$product_id = $item->get_product_id();
			if(get_post_meta($product_id, 'has_result', true))
			{
				//conta quantidade de itens
				$i=0;
				$itemQuatity = $item->get_quantity();
				while($i<$itemQuatity)
				{
					//cria o fake post de resultado
					$titulo = "Paciente: " . $userName . " - Pedido: " . $order_id . " - Exame: " . $item->get_name() . " - Item #" . ($i+1);

					$arr_metas = [
						'paciente' => $user_id,
						'pedido'	  => $order_id,
						'produto'   => $product_id,
						'status' => "Aguardando envio"
					];
	
					$retorno = array(
					  'post_title'    => $titulo,
					  'post_content'  => '',
					  'post_status'   => 'publish',
					  'post_author'   => 1,
					  'post_type' 	  => 'resultado',
					  'meta_input'	  => $arr_metas
					);
					$id = wp_insert_post($retorno);
					createResult($id, $titulo, $user_id, $order_id,$product_id,   "Aguardando envio", "");
					$i++;
				}
	
			}

		}
	
		user_create(userCreateJSON($user_id), $user_id);



	}


	

}

add_action( "woocommerce_payment_complete", 'filter_woocommerce_rest_pre_insert_post_type_object', 10, 3 );
function filter_woocommerce_rest_pre_insert_post_type_object( $order_id) { 

	addOrder($order_id);
}

// add the action 
add_action( 'woocommerce_new_order', 'wooCreateOrder', 10, 3 );
// define the woocommerce_new_order callback 
function wooCreateOrder( $order_id ) { 
	
	addOrder($order_id);

	}; 

	// define the woocommerce_order_status_changed callback 
function action_woocommerce_order_status_changed( $this_get_id, $this_status_transition_from, $new_status, $order ) { 
	// make action magic happen here...
	// Get order status history
	$order_status_history = $order->get_meta( '_status_history' ) ? $order->get_meta( '_status_history' ) : array();
	$order_status_history[current_time( 'mysql' )] = $new_status;
	$order->update_meta_data( '_status_history', $order_status_history );
	$order->save(); // Save

	$orderData = orderCreateJSON($this_get_id,$order);
	$firebaseID = get_post_meta($this_get_id, 'firebaseID', true);
	editDataFirebase("orders",$firebaseID,$orderData);
}; 
         
// add the action 
add_action( 'woocommerce_order_status_changed', 'action_woocommerce_order_status_changed', 10, 4 ); 

function beforeDeleteOrder($post_id){

	$firebaseID = get_post_meta($post_id, 'firebaseID', true);
	if($firebaseID)
	{
		deleteDataFirebase("orders",$firebaseID);
	} 
	$user_id = get_post_meta($post_id, '_customer_user', true);
	user_create(userCreateJSON($user_id), $user_id);
 

}

function orderCreateJSON($order_id, $order){

    $orderObject = $order->get_data();

	$orderJSON["date"] = ["timestampValue" => convertDate($orderObject["date_created"])];
	$orderJSON["id_wordpress"] = ["integerValue" => $order_id];
	$orderJSON["shipping"] = ["doubleValue" => is_null($orderObject["shipping_total"]) ? 0 : $orderObject["shipping_total"]];
	$orderJSON["payment_method"] = ["stringValue" => $orderObject["payment_method"]];
	$orderJSON["status"] = ["stringValue" => $orderObject["status"]];

	$user_id = get_post_meta($order_id, '_customer_user', true);

	if(get_user_meta($user_id, 'firebaseID', true)){
		$user_FB = get_user_meta($user_id, 'firebaseID', true);
	}
	else{
		$user_FB = 'xxxxxxxxxxxxxxx';
	}

	//$clientData["id"] = ["stringValue" => $user_FB];
	//$clientData["id_wordpress"] = ["integerValue" => $user_id];
	//$orderJSON["client"] = ["mapValue" => montaMap($clientData)];
	$orderJSON["patient"] = ["stringValue" => $user_FB];

	// Iterating through each WC_Order_Item_Product objects
	foreach ($order->get_items() as $item_key => $item ){

		if(get_post_meta($item->get_product_id(), 'firebaseID', true)){
			$productItem["id"] = ["stringValue" => get_post_meta($item->get_product_id(), 'firebaseID', true)];
		}
		else
		{
			$productItem["id"] = ["stringValue" => "Not assigned"];
		}
		

		//$productItem["id_wordpress"] = ["integerValue" => $item->get_product_id()];
		$productItem["quantity"] = ["integerValue" => $item->get_quantity()];
		$productItem["price"] = ["doubleValue" => is_null($item->get_total()) ? 0 : $item->get_total()];
		$productsList[] = $productItem;

		createProduct($item->get_product_id());

	}
	if(count($productsList) > 1){
		foreach($productsList as $productItemJSON)
		{
			$productMap[] = ["mapValue" => montaMap($productItemJSON)];
		}
		$orderJSON["products"] = ["arrayValue" => montaArrayFromMap($productMap)];
	}
	else{
		$orderJSON["products"] = ["mapValue" => montaMap($productsList[0])];
    }
    
	$billing_information["city"] = ["stringValue" => $orderObject['billing']['city']];
	$billing_information["state"] = ["stringValue" => $orderObject['billing']['state']];
	$billing_information["email"] = ["stringValue" => $orderObject['billing']['email']];
	$billing_information["name"] = ["stringValue" => $orderObject['billing']['first_name']];
	$billing_information["mobile"] = ["stringValue" => $orderObject['billing']['phone']];
	$billing_information["address"] = ["stringValue" => $orderObject['billing']['address_1'] . "," . $orderObject['billing']['address_2']];
	$billing_information["last_name"] = ["stringValue" => $orderObject['billing']['last_name']];
	$billing_information["zip_code"] = ["stringValue" => $orderObject['billing']['postcode']];
	$orderJSON["billing_information"] = ["mapValue" => montaMap($billing_information)];

	$shipping_information["city"] = ["stringValue" => $orderObject['shipping']['city']];
	$shipping_information["state"] = ["stringValue" => $orderObject['shipping']['state']];
	$shipping_information["name"] = ["stringValue" => $orderObject['shipping']['first_name']];
	$shipping_information["address"] = ["stringValue" => $orderObject['shipping']['address_1'] . "," . $orderObject['shipping']['address_2']];
	$shipping_information["last_name"] = ["stringValue" => $orderObject['shipping']['last_name']];
	$shipping_information["zip_code"] = ["stringValue" => $orderObject['shipping']['postcode']];
	$orderJSON["shipping_information"] = ["mapValue" => montaMap($shipping_information)];


	// Get the history data
	$status_history = $order->get_meta('_status_history');
	foreach ($status_history as $key => $item ){
		$statusItem["date"] = ["timestampValue" => convertDate($key)];
		$statusItem["status"] = ["stringValue" => $item];
		$statusList[] = ["mapValue" => montaMap($statusItem)];
	}


	
    $orderJSON["status_history"] = ["arrayValue" => montaArrayFromMap($statusList)];
    
    return $orderJSON;
}

?>
<?php
add_action( 'before_delete_post', 'beforeDelete', 99 );
function beforeDelete($post_id){

	switch (get_post_type($post_id)) {
		case "product":
			beforeDeleteProduct($post_id);
			break;
		case "resultado":
			beforeDeleteResult($post_id);
			break;
		case "shop_order":
			beforeDeleteOrder($post_id);
			break;
	}


}

add_action('acf/save_post', 'my_acf_save_post');
function my_acf_save_post( $post_id ) {

	$post_type = get_post_type( $post_id );

	if($post_type == "resultado"){

		$user_id = get_field('paciente', $post_id);
		user_create(userCreateJSON($user_id), $user_id);
	}

}

?>	
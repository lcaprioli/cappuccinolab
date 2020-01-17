<?php
/*add_role('patient',__( 'Paciente' ),	array(
	'read'         => true,  // true allows this capability
	'edit_posts'   => true,
	)
	);

add_role('attendant',__( 'Atendente' ),	array(
	'read'         => true,  // true allows this capability
	'edit_posts'   => true,
	)
	);

add_role('doctor',__( 'Médico' ),	array(
	'read'         => true,  // true allows this capability
	'edit_posts'   => true,
	)
	);
*/
// Campos especiais de usuário
add_action( 'show_user_profile', 'crf_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'crf_show_extra_profile_fields' );



function crf_show_extra_profile_fields( $user ) {

	$anamnese = getAnamnese();

	$posts = get_posts(array(
		'post_type' => 'local'
	));

	if (count($posts) > 0){

		echo '<h3>Local</h3>';
		echo '<select name="local">';

		foreach($posts as $post)
		{
			echo '<option value="' . $post->ID . '" ';
			if(get_user_meta( $user->ID, "local", true) == $post->ID)
			{
				echo 'selected'; 
			}
			echo '>';
			echo $post->post_title;
		}
		echo '</select>';
	}

	if ( in_array( 'patient', $user->roles ) ) {
		
	?>


	<h2><?php esc_html_e( 'Anamnese', 'crf' ); ?></h2>

<?php
	foreach($anamnese as $key => $value){
		$groupID = $key;
		$groupTitle = $value["title"];
		echo '<fieldset style="border: 1px solid;padding: 10px;margin-bottom: 20px;">';
		echo '<legend>'. $groupTitle . '</legend>';


		$questions = $value["questions"];
		$questionIndex = 0;


		foreach($questions as $question){
			
			$answerIndex = 1;
			$answerTitle = $question["question"];
			$fieldID = 'anamnese_'.$groupID.'_'.$questionIndex;

			echo '<label>'. $answerTitle  . '</label>';
			echo '<table class="form-table"><tr>';
			

			foreach($question["answers"] as $answer)
			{
				echo '<td>
				<input type="radio"
			       id="'.$fieldID.'_'.$answerIndex.'" 
			       name="'.$fieldID.'" 
				   value="'. $answerIndex .'"';

				   if( get_the_author_meta( $fieldID , $user->ID ) == strval($answerIndex) ) 
				   { 
					   echo 'checked="checked"';
					}
					echo '/>' . $answer . '</td>';

				$answerIndex++;
			}

			$questionIndex++;

			echo '</tr></table>';
		}
?>

	
	<?php
	echo '</fieldset>';
	}
	}
	?>


	<?php
}
?>
<?php 

add_action( 'user_register', 'myplugin_registration_save', 10, 1 );
function myplugin_registration_save( $user_id ) {

	//verifica se é do REST - post vem vazio
	if(count($_POST) > 0)
	{
		user_create(userCreateJSON($user_id),$user_id);
	}

}

add_action( 'profile_update', 'action_edit_user_profile_update' ); 
function action_edit_user_profile_update($user_id) {

	$anamnese = getAnamnese();
	foreach($anamnese as $key => $value){
		$groupID = $key;
		$questions = $value["questions"];
		$questionIndex = 0;
		foreach($questions as $question){

			$fieldID = 'anamnese_'.$groupID.'_'.$questionIndex;
			$userValue = 0;
			if(!empty($_POST[$fieldID]) && strlen($_POST[$fieldID])){
				$userValue = $_POST[$fieldID];
			}

			update_user_meta( $user_id, $fieldID , $userValue);
			
			$questionIndex++;
		}
	}
		update_user_meta( $user_id, "local" , $_POST["local"]);
		user_create(userCreateJSON($user_id),$user_id);

}

function userCreateJSON($user_id){

    $user_obj = get_user_by('id', $user_id);

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

//verificar perfil
if (in_array( 'attendant', $user_obj->roles ) ) {

	$userJSON["role"] = ["stringValue"  => "attendant"];

}

	//verificar perfil
	if (in_array( 'patient', $user_obj->roles ) ) {

		$userJSON["role"] = ["stringValue"  => "patient"];
		// pegar atendimentos
		$results = $wpdb->get_results("SELECT * FROM ". $database_name. ".wp_posts a, ". $database_name. ".wp_postmeta b where a.ID = b.post_id and a.post_type = 'atendimento' and a.post_status <> 'trash' and b.meta_value = '$user_id' and b.meta_key = 'paciente'", ARRAY_A);

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
			$userJSON["attendences"] = ["arrayValue"  => montaArray($arr_results)];
		}
		
		$anamnese = getAnamnese();
		$itemAnamnese = [];
		foreach($anamnese as $key => $value){
			$groupID = $key;
			$questions = $value["questions"];
			$questionIndex = 0;
			$arrayAnswers = [];
			foreach($questions as $question){

				$fieldID = 'anamnese_'.$groupID.'_'.$questionIndex;
				$answerItem = 0;
				$userValue = get_user_meta($user_id, $fieldID, true);
				if(!empty($userValue) && strlen($userValue))
				{
					$answerItem = $userValue;
				}
				$arrayAnswers[] = ["integerValue" => $answerItem];
				$questionIndex++;
			}
			$itemAnamnese[$groupID] = ["arrayValue"  => montaArray($arrayAnswers)];
		}
		$userJSON["anamnese"] = ["mapValue" => montaMap($itemAnamnese)];

	}

	if	( in_array( 'doctor', $user_obj->roles )  ) {

		$userJSON["role"] = ["stringValue"  => "doctor"];
		// pegar pacientes do medico
		$results = $wpdb->get_results("SELECT * FROM ". $database_name. ".wp_posts a, ". $database_name. ".wp_postmeta b where a.ID = b.post_id and a.post_type = 'atendimento' and a.post_status <> 'trash' and b.meta_value = '$user_id' and b.meta_key = 'medico'", ARRAY_A);

		$userJSON["patients"] = ["nullValue"  => null];
		
		if(!empty($results)){
			$arr_results = [];
			foreach ($results as $result) {
			
				$post_id = $result['ID'];
				$patientAttendence = get_post_meta($post_id, 'paciente', true);
				$patientFirebase = get_user_meta($patientAttendence, "firebaseID", true);
				$arr_results[] = $patientFirebase;
			}
			array_unique($arr_results);
			if(!empty($arr_results)){

				$jsonPatients = [];
				
				foreach($arr_results as $patient){
					$jsonPatients[] = ["stringValue"  => $patient];

				}

					$userJSON["patients"] = ["arrayValue"  => montaArray($jsonPatients)];
			
				
				
			}
		
		}
		

	}
	
	$localUser = get_user_meta($user_id, 'local', true);
	if($localUser){
		$localID = get_post_meta($localUser, 'firebaseID', true);
		$userJSON["local"] = ["stringValue" => $localID ];
	}
	else{
		$userJSON["local"] = ["nullValue" => null ];
	}	
	if(get_cupp_meta($user_id, "original")){
		$userJSON["avatar_url"] = ["stringValue" => get_cupp_meta($user_id, "original") ];
	}
	else{
		$userJSON["avatar_url"] = ["nullValue" => null ];
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
		if($newid){
			update_user_meta($user_id, "firebaseID", $newid);
		}
		
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
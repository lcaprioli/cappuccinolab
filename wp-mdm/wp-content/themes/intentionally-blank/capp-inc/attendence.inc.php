<?php
function createAttendence($post_id,$date, $user_doctor_id, $user_patient_id, $local,$registro, $tipo){
  
$attendenceData = [];
$attendenceData["date"] = $date;
$attendenceData["patient"] = $user_patient_id;
$attendenceData["doctor"] = $user_doctor_id;
$attendenceData["local"] = $local;
$attendenceData["registro"] = $registro;
$attendenceData["tipo"] = $tipo;

$attendenceJSON = attendenceCreateJSON($post_id, $attendenceData);
if(get_post_meta($post_id, 'firebaseID', true))
{
    $firebaseID = get_post_meta($post_id, 'firebaseID', true);
    editDataFirebase("attendences",$firebaseID,$attendenceJSON);
}
else
{
    $newid = saveDataFirebase("attendences",$attendenceJSON);
    update_post_meta($post_id, "firebaseID", $newid);
}
    user_create(userCreateJSON($user_doctor_id), $user_doctor_id);
    user_create(userCreateJSON($user_patient_id), $user_patient_id);
}
add_action( 'save_post_atendimento', 'action_atendimento_save',12,3 );
function action_atendimento_save($post_id, $post, $update) {
   //save stuff
   if(isset($_POST["acf"]))
    {
        $postData = $_POST["acf"];
        $keys = array_keys($postData);
        createAttendence($post_id, $postData[$keys[0]], $postData[$keys[1]],$postData[$keys[2]],$postData[$keys[3]],$postData[$keys[4]],$postData[$keys[5]]);
    }
}


function beforeDeleteAttendence($post_id){

	$firebaseID = get_post_meta($post_id, 'firebaseID', true);
	if($firebaseID)
	{
		deleteDataFirebase("attendences",$firebaseID);
    } 
    $user_id = get_field('paciente', $post_id);
	user_create(userCreateJSON($user_id), $user_id);
 
}
function attendenceCreateJSON($post_id,$attendenceData)
{
    $attendence_firebase_data["date"] = ["timestampValue" => convertDate($attendenceData["date"])];

    $patient = $attendenceData["patient"];
    $attendence_firebase_data["patient"] = ["stringValue" => get_user_meta($patient, 'firebaseID', true)];
        
    $doctor = $attendenceData["doctor"];
    $attendence_firebase_data["doctor"] = ["stringValue" => get_user_meta($doctor, 'firebaseID', true)];

    $local = $attendenceData["local"];
    $attendence_firebase_data["local"] = ["stringValue" => get_post_meta($local, 'firebaseID', true)];

    $attendence_firebase_data["registro"] = ["stringValue" => $attendenceData["registro"]];

    $attendence_firebase_data["tipo"] = ["stringValue" => $attendenceData["tipo"]];

    return $attendence_firebase_data;

}
?>
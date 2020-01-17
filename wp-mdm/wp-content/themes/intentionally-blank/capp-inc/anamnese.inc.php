<?php

function getAnamnese(){

    global $wpdb;
    global $database_name;

    $anamnese = [];
    // pegar pedidos
    $anamneseList = $wpdb->get_results("SELECT * FROM ". $database_name. ".wp_posts where post_type = 'wpforms'", ARRAY_A);
    if(!empty($anamneseList)){
		$arr_anamnese = [];
		foreach ($anamneseList as $item) {
            $anamneseInfo = json_decode($item['post_content'], true);
            $anamneseDesc = $anamneseInfo["settings"]["form_desc"];
            $anamneseTitle = $anamneseInfo["settings"]["form_title"];
            $anamnese[$anamneseTitle]["title"] =  $anamneseDesc;
            $i=0;
            foreach($anamneseInfo["fields"] as $field){
              //  $anamnese["social"]["questions"][0]["question"] = "Pergunta sociais #1";
              

                $tituloPergunta = $field["label"];
                $anamnese[$anamneseTitle]["questions"][$i]["question"] =  $tituloPergunta;
              
                $answers = [];
                foreach($field["choices"] as $choice){

                    $titleChoice = $choice["label"];
                    $answers[] = $titleChoice;
                    
                }
                $anamnese[$anamneseTitle]["questions"][$i]["answers"] = $answers;
                $i++;
            }
            
		}
		
    }
 
    return $anamnese;

}

function getAnamneseByID($post_id){

    global $wpdb;
    global $database_name;

    $anamnese = [];
    // pegar pedidos
    $anamneseList = $wpdb->get_results("SELECT * FROM ". $database_name. ".wp_posts where post_type = 'wpforms' and ID = " . $post_id, ARRAY_A);
    if(!empty($anamneseList)){
		$arr_anamnese = [];
		foreach ($anamneseList as $item) {
            $anamneseInfo = json_decode($item['post_content'], true);
            $anamneseDesc = $anamneseInfo["settings"]["form_desc"];
            $anamneseTitle = $anamneseInfo["settings"]["form_title"];
            $anamnese[$anamneseTitle]["title"] =  $anamneseDesc;
            $i=0;
            foreach($anamneseInfo["fields"] as $field){
              //  $anamnese["social"]["questions"][0]["question"] = "Pergunta sociais #1";
              

                $tituloPergunta = $field["label"];
                $anamnese[$anamneseTitle]["questions"][$i]["question"] =  $tituloPergunta;
              
                $answers = [];
                foreach($field["choices"] as $choice){

                    $titleChoice = $choice["label"];
                    $answers[] = $titleChoice;
                    
                }
                $anamnese[$anamneseTitle]["questions"][$i]["answers"] = $answers;
                $i++;
            }
            
		}
		
    }
 
    return $anamnese;

}

add_action( 'save_post_wpforms', 'action_anamnese_save',10,3 );
function action_anamnese_save($post_id, $post, $update){

    $anamneseData = anamneseUpdate(getAnamneseByID($post_id));
    if(get_post_meta($post_id, 'firebaseID', true))
    {
        $firebaseID = get_post_meta($post_id, 'firebaseID', true);
        editDataFirebase("anamnese",$firebaseID,$anamneseData);
    }
    else
    {
        $newid = saveDataFirebase("anamnese",$anamneseData);
        update_post_meta($post_id, "firebaseID", $newid);
    }
}

function anamneseUpdate($anamnese){

    $anamneseJSON = [];
    foreach($anamnese as $key => $value){
		$groupID = $key;
		$groupTitle = $value["title"];
		
        $anamneseJSON["type"] = ["stringValue" => $groupID];
        $anamneseQuestions = [];

		$questions = $value["questions"];
		$questionIndex = 0;


		foreach($questions as $question){
			
			$answerIndex = 1;
			$answerTitle = $question["question"];
			$fieldID = 'anamnese_'.$groupID.'_'.$questionIndex;
            $anamneseQuestion = [];
            
            $anamneseQuestion["title"] = ["stringValue" => $answerTitle];
            $answers = [];
			foreach($question["answers"] as $answer)
			{
                   $answers[] = ["stringValue" => $answer];

                    
				$answerIndex++;
            }
            $anamneseQuestion["answers"] = ["arrayValue"  => montaArray($answers)];

            $anamneseQuestions[] = ["mapValue" => montaMap($anamneseQuestion)];
			$questionIndex++;

        }

        $anamneseJSON["questions"] = ["arrayValue" => montaArrayFromMap($anamneseQuestions)];
    
    }

    return $anamneseJSON;
}
?>
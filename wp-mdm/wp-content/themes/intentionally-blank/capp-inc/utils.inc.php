<?php

function convertDate($date){
	$dateTimeWP = str_replace(" ","T",$date);
	$dateTimeWP = substr($dateTimeWP,0,19);
    $dateTimeWP = $dateTimeWP . "Z";
    return $dateTimeWP;
}

function write_log($log) {
    if (true === WP_DEBUG) {
        if (is_array($log) || is_object($log)) {
            error_log(print_r($log, true));
        } else {
            error_log($log);
        }
    }
}


?>
<?php

	include("../php/init.php");
	
	$instanceID = intval(FromPostIfExist("LogScoreInstanceID", ""));
	$value = intval(FromPostIfExist("LogScoreValue", ""));
	$text = FromPostIfExist("LogScoreText", "");
	
	if($GLOBAL_IsLoggedIn){
		print json_encode(DB_ScoreInsert($GLOBAL_LoggedInUserId, $GLOBAL_IP, $instanceID, $value, $text));
	}else{
		ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	}
?>
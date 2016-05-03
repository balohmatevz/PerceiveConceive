<?php

	include("../php/init.php");
	
	$classID = intval(FromGetIfExist("classID", ""));
	$userID = intval(FromGetIfExist("userID", ""));
	//$role = intval(FromPostIfExist("classEnrollRole", ""));
	$role = 1;
	
	if(!$userID){
		ThrowErrorAndDie(ERROR_NO_USER_ID_PROVIDED);
	}
	
	if(!$classID){
		ThrowErrorAndDie(ERROR_NO_CLASS_ID_PROVIDED);
	}
	
	if($GLOBAL_IsLoggedIn){
		print json_encode(DB_StudentEnroll($classID, $userID, $GLOBAL_LoggedInUserId, $role));
	}else{
		ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	}

?>
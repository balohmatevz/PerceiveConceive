<?php

//Because I cannot spell enrol properly.
	include("../php/init.php");
	
	$classID = intval(FromGetIfExist("classID", ""));
	$userID = intval(FromGetIfExist("userID", ""));
	
	if(!$userID){
		ThrowErrorAndDie(ERROR_NO_USER_ID_PROVIDED);
	}
	if(!$classID){
		ThrowErrorAndDie(ERROR_NO_CLASS_ID_PROVIDED);
	}
	
	if($GLOBAL_LoggedInUserId){
		print json_encode(DB_StudentUnenroll($classID, $userID, $GLOBAL_LoggedInUserId));
	}else{
		ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	}
?>
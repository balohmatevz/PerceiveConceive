<?php

	include("../php/init.php");
	
	$userID = intval(FromGetIfExist("userID", ""));
	$classID = intval(FromGetIfExist("classID", ""));
	
	if(!$userID){
		ThrowErrorAndDie(ERROR_NO_USER_ID_PROVIDED);
	}
	
	if(!$classID){
		ThrowErrorAndDie(ERROR_NO_CLASS_ID_PROVIDED);
	}
	
	if($GLOBAL_IsLoggedIn){
		print json_encode(DB_StudentMakeTeacher($userID, $classID));
	}else{
		print json_encode(ERROR_NOT_LOGGED_IN);
	}
	
?>
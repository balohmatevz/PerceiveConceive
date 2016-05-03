<?php

	include("../php/init.php");
	
	$classID = intval(FromGetIfExist("classID", ""));
	//$role = intval(FromPostIfExist("classEnrollRole", ""));
	$role = 1;
	
	if($GLOBAL_IsLoggedIn){
		print json_encode(DB_StudentEnroll($classID, $GLOBAL_LoggedInUserId, $GLOBAL_LoggedInUserId, $role));
	}else{
		ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	}

?>
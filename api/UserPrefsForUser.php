<?php

	include("../php/init.php");
	
	$userID = intval(FromGetIfExist("userID", ""));
	
	if($userID){
		print json_encode(DB_GetUserPreferences($userID, $GLOBAL_LoggedInUserId));
	}else{
		ThrowErrorAndDie(ERROR_NO_USER_ID_PROVIDED);
	}
	
?>
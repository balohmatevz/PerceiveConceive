<?php

	include("../php/init.php");
	
	//$userID = intval(FromGetIfExist("userID", ""));
	$userID = $GLOBAL_LoggedInUserId;
	
	if($userID){
		print json_encode(DB_ClassGetForUser($userID, $GLOBAL_LoggedInUserId));
	}else{
		ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	}

?>
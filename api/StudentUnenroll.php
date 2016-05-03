<?php

//Because I cannot spell enrol properly.
	include("../php/init.php");
	
	$classID = intval(FromGetIfExist("classID", ""));
	
	if($GLOBAL_IsLoggedIn){
		print json_encode(DB_StudentUnenroll($classID, $GLOBAL_LoggedInUserId, $GLOBAL_LoggedInUserId));
	}else{
		ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	}

?>
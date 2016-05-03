<?php

	include("../php/init.php");
	
	$userID = -1;
	
	$sessionID = FromCookieIfExist("sessionID", "");;
	
	if($sessionID){
		$info = DB_IsLoggedIn($sessionID, $ip, $userAgent);
		if(isset($info["user_id"])){
			$userID = intval($info["user_id"]);
		}
	}
	
	if($userID != -1){
		print json_encode(DB_GetUserPreferences($userID, $GLOBAL_LoggedInUserId));
	}else{
		ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	}
	
?>
<?php

	include("../php/init.php");
	
	$sessionID = FromCookieIfExist("sessionID", "");;
	
	if($sessionID){
		print json_encode(DB_IsLoggedIn($sessionID, $ip, $userAgent));
	}else{
		ThrowErrorAndDie(ERROR_NO_SESSION_ID_PROVIDED);
	}
	
?>
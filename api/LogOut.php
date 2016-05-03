<?php

	include("../php/init.php");
	
	$sessionID = FromCookieIfExist("sessionID", "");
	
	if($sessionID){
		setcookie("sessionID", "", time()-3600, "/");
		print json_encode(DB_LogOut($sessionID));
	}else{
		ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	}
	
?>
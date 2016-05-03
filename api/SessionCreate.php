<?php

	include("../php/init.php");
	
	$userID = intval(FromPostIfExist("createSessionUserID", ""));
	
	$sessionID = DB_SessionInsert($userID, $ip, $userAgent);
	
	if($sessionID){
		$expires = time()+60*60*24*30;
		setcookie("sessionID", $sessionID, $expires, "/");
	}else{
		ThrowErrorAndDie(ERROR_SESSION_CREATION_ERROR);
	}
	
?>
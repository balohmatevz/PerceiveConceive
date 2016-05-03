<?php

	include("../php/init.php");
	
	$username = FromPostIfExist("LogInUsername", "");
	$password = FromPostIfExist("LogInPassword", "");
	
	
	$output = DB_LogInUser($username, $password, $ip, $userAgent);
	$sessionID = $output["session_id"];
	
	if($sessionID){
		$expires = time()+60*60*24*30;
		setcookie("sessionID", $sessionID, $expires, "/");
		print json_encode($output);
	}else{
		ThrowErrorAndDie(ERROR_SESSION_CREATION_ERROR);
	}
?>
	
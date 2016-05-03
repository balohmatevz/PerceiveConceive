<?php

	include("../php/init.php");
	
	$userID = intval(FromGetIfExist("userID", ""));
	
	if($userID){
		print json_encode(DB_UserDelete($userID));
	}else{
		ThrowErrorAndDie(ERROR_NO_USER_ID_PROVIDED);
	}

?>
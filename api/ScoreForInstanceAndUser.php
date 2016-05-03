<?php

	include("../php/init.php");
	
	$instanceID = intval(FromGetIfExist("instanceID", ""));
	$userID = intval(FromGetIfExist("userID", ""));
	$orderBy = FromGetIfExist("orderBy", "");
	if($orderBy == ""){
		$orderBy == "time";
	}
	
	if($instanceID){
		if($userID){
			print json_encode(DB_ScoreForInstanceAndUser($instanceID, $userID, $orderBy));
		}else{
			throwErrorAndDie(ERROR_NO_USER_ID_PROVIDED);
		}
	}else{
		throwErrorAndDie(ERROR_NO_INSTANCE_ID_PROVIDED);
	}
?>
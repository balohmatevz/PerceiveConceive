<?php

	include("../php/init.php");
	
	$instanceID = intval(FromGetIfExist("instanceID", ""));
	$orderBy = FromGetIfExist("orderBy", "");
	if($orderBy == ""){
		$orderBy == "time";
	}
	
	if($instanceID){
		print json_encode(DB_ScoreForInstance($instanceID, $orderBy));
	}else{
		ThrowErrorAndDie(ERROR_NO_INSTANCE_ID_PROVIDED);
	}
?>
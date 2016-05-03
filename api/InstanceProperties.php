<?php

	include("../php/init.php");
	
	$instanceID = intval(FromGetIfExist("instanceID", ""));
	
	if($instanceID){
		print json_encode(DB_GetInstanceProperties($instanceID));
	}else{
		ThrowErrorAndDie(ERROR_NO_INSTANCE_ID_PROVIDED);
	}
?>
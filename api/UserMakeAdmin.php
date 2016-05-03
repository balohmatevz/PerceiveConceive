<?php

	include("../php/init.php");
	
	$userID = intval(FromGetIfExist("userID", ""));
	
	if($userID){
		print json_encode(DB_UserMakeAdmin($userID));
	}else{
		print json_encode(ERROR_NO_USER_ID_PROVIDED);
	}
	
?>
<?php

	include("../php/init.php");
	
	$gameID = intval(FromPostIfExist("newConstantGameID", ""));
	$name = FromPostIfExist("newConstantName", "");
	$value = FromPostIfExist("newConstantValue", "");
	
	if($GLOBAL_IsLoggedIn){
		DB_AddConstant($gameID, $name, $value);
	}else{
		ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	}
?>
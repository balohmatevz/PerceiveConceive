<?php

	include("../php/init.php");
	
	$instanceID = intval(FromPostIfExist("newVarInstanceID", ""));
	$gameID = intval(FromPostIfExist("newVarGameID", ""));
	$name = FromPostIfExist("newVarName", "");
	$type = FromPostIfExist("newVarType", "");
	$value = FromPostIfExist("newVarValue", "");
	
	if($GLOBAL_IsLoggedIn){
		DB_AddVariable($instanceID, $gameID, $name, $type, $value);
	}else{
		ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	}
?>
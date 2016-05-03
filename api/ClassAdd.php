<?php

	include("../php/init.php");
	
	$className = FromPostIfExist("className", "");
	$classDescription = FromPostIfExist("classDescription", "");
	$classGlyph = FromPostIfExist("classGlyph", "");
	$classPublic = intval(FromPostIfExist("classPublic", "0"));
	
	$classDescription = str_replace(PHP_EOL, "<br>", $classDescription);
	$classDescription = str_replace("\n", "", $classDescription);
	$classDescription = str_replace("\r", "", $classDescription);
	
	if($GLOBAL_IsLoggedIn){
		print json_encode(DB_ClassInsert($GLOBAL_UserInfo["user_id"], $GLOBAL_IP, $className, $classDescription, $classGlyph, $classPublic));
	}else{
		ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	}
	
?>
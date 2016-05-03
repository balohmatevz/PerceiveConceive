<?php

	include("../php/init.php");
	
	$classID = intval(FromPostIfExist("updateClassID", ""));
	$className = FromPostIfExist("updateClassName", "");
	$classDescription = FromPostIfExist("updateClassDescription", "");
	$classGlyph = FromPostIfExist("updateClassGlyph", "");
	
	$classDescription = str_replace(PHP_EOL, "<br>", $classDescription);
	$classDescription = str_replace("\n", "", $classDescription);
	$classDescription = str_replace("\r", "", $classDescription);
	
	if($GLOBAL_IsLoggedIn){
		print json_encode(DB_ClassUpdate($classID, $className, $classDescription, $classGlyph));
	}else{
		ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	}
	
?>
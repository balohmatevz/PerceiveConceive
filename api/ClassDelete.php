<?php

	include("../php/init.php");
	
	$classID = intval(FromGetIfExist("classID", ""));
	
	if($classID){
		print json_encode(DB_ClassDelete($classID));
	}else{
		ThrowErrorAndDie(ERROR_NO_CLASS_ID_PROVIDED);
	}

?>
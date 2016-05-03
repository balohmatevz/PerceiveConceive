<?php

	include("../php/init.php");
	
	$gameID = intval(FromGetIfExist("gameID", ""));
	
	if($gameID){
		print json_encode(DB_GetVariablesForGame($gameID));
	}else{
		ThrowErrorAndDie(ERROR_NO_GAME_ID_PROVIDED);
	}
?>
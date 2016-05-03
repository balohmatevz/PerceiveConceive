<?php

	include("../php/init.php");
	
	$gameID = intval(FromGetIfExist("gameID", ""));
	
	if($gameID){
		print json_encode(DB_GetInstancesForGame($gameID));
	}
?>
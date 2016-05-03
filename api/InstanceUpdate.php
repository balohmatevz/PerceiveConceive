<?

	include("../php/init.php");
	
	$instanceID = intval(FromPostIfExist("updateInstanceID", ""));
	$name = FromPostIfExist("updateInstanceName", "");
	$description = FromPostIfExist("updateInstanceDescription", "");
	$difficulty = intval(FromPostIfExist("updateInstanceDifficulty", ""));
	$timeLimit = intval(FromPostIfExist("updateInstanceTimeLimit", ""));
	$levels = intval(FromPostIfExist("updateInstanceLevels", ""));
	$retriesAvailable = intval(FromPostIfExist("updateInstanceRetriesAvailable", ""));
	
	DB_InstanceUpdate($instanceID, $name, $description, $difficulty, $timeLimit, $levels, $retriesAvailable);
?>
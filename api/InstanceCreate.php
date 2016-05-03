<?php

	include("../php/init.php");
	//print $GLOBAL_UserInfo["user_id"] . "a";
	//print_r($_POST);
	file_put_contents("out.txt", $_POST);
	
	
	$classID = intval(FromPostIfExist("ClassID", ""));
	$gameID = intval(FromPostIfExist("GameID", ""));
	$name = FromPostIfExist("InstanceName", "");
	$description = FromPostIfExist("InstanceDescription", "");
	$glyph = FromPostIfExist("InstanceGlyph", "");
	$difficulty = intval(FromPostIfExist("InstanceDifficulty", ""));
	$timeLimit = intval(FromPostIfExist("InstanceTimeLimit", ""));
	$levels = intval(FromPostIfExist("InstanceLevels", ""));
	$retriesAvailable = intval(FromPostIfExist("InstanceRetriesAvailable", ""));
	
	$retriesAvailable = intval(FromPostIfExist("InstanceRetriesAvailable", ""));
	
	//print "<li>".FromPostIfExist("VariableValue-0-0", "no")."</li>";
	
	$variables = Array();
	for($i = 0; $i < 1000; $i++){
		$varname = fromPostIfExist("VariableName-".$i, "");
		$varvalue = fromPostIfExist("VariableValue-".$i, "");
		if($varvalue == ""){
			//Either an array or actually genuinely blank.
			$elements = Array();
			for($j = 0; $j < 1000; $j++){
				$element = fromPostIfExist("VariableValue-".$i."-".$j, "");
				if($element != ""){
					//print "<li>$element</li>";
					$elements[] = $element;
				}
				//We cannot return out of here early because entries may have been added and removed during list generation, causing there to be missing values (IE if we have elements 1,2,3,4 and then the user removes element 2, leaving us with just 1,3,4.)
			}
			if(count($elements) > 0){
				//If it's a list, set it to the elements, else keep it as ""
				$varvalue = $elements;
			}
		}
		
		if($varname == ""){
			//Reached end of variables
			break;
		}
		
		$variables[$varname] = $varvalue;
	}
	
	//print_r($variables);
	
	if($GLOBAL_IsLoggedIn){
		print json_encode(DB_InstanceInsert($classID, $GLOBAL_UserInfo["user_id"], $gameID, $name, $description, $glyph, $difficulty, $timeLimit, $levels, $retriesAvailable, $variables));
	}else{
		ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	}
?>
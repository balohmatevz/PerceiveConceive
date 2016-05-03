<?php
	include("../php/init.php");
	
	//TODO Authentication + authorization
	//if($GLOBAL_IsLoggedIn){
	//	ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	//}
	
	$FileValidation = true;
	function ValidateUploadedFilesRec($main){
		global $FileValidation, $GLOBAL_GameUploadValidFileExtensions;
		$dirHandle = opendir($main);
		while($file = readdir($dirHandle)){
			if($file != '.' && $file != '..'){
				if(is_dir($main.$file)){
					print "<li><b>Entering: ".$main.$file."/</b>";
					ValidateUploadedFilesRec($main.$file."/");
				}else{
					$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
					if(in_array($ext, $GLOBAL_GameUploadValidFileExtensions)){
						print "<li>".$main.$file;
					}else{
						$FileValidation = false;
						print "<font color='red'><li>".$main.$file."</font>";
					}
				}
			}
		} 
	}
	
	function deleteDir($dirPath) {
		if (! is_dir($dirPath)) {
			die("$dirPath must be a directory");
		}
		if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
			$dirPath .= '/';
		}
		$files = glob($dirPath . '*', GLOB_MARK);
		foreach ($files as $file) {
			if (is_dir($file)) {
				deleteDir($file);
			} else {
				print "<br><font color='orange'>DELECING DIR $dirPath$file</font>";
				unlink($file);
			}
		}
		print "<br><font color='orange'>DELECING DIR $dirPath</font>";
		rmdir($dirPath);
	}
	
	//Handle file upload and zip extraction.
	if($_FILES["GameFile"]["name"]) {
		$filename = $_FILES["GameFile"]["name"];
		$source = $_FILES["GameFile"]["tmp_name"];
		$type = $_FILES["GameFile"]["type"];
		
		$name = explode(".", $filename);
		$accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed', 'application/octet-stream');
		foreach($accepted_types as $mime_type) {
			if($mime_type == $type) {
				$okay = true;
				break;
			} 
		}
		
		$continue = strtolower($name[count($name) - 1]) == 'zip' ? true : false;
		if(!$continue) {
			$message = "The file you are trying to upload is not a .zip file. Please try again.";
			die($message);
		}
		
		$FolderNumber = 0;
		for($i = 0; $i < 10000; $i++){
			if(!file_exists($GLOBAL_GameUploadRootFolder . $i)){
				$FolderNumber = $i;
				break;
			};
		}
		
		mkdir($GLOBAL_GameUploadRootFolder . $FolderNumber);
		$target_dir = $GLOBAL_GameUploadRootFolder . $FolderNumber;
		$target_dir_full  = $target_dir ."/";
		$target_path = $target_dir_full . $filename;
		if(move_uploaded_file($source, $target_path)) {
			$zip = new ZipArchive();
			$x = $zip->open($target_path);
			if ($x === true) {
				$zip->extractTo($target_dir_full);
				$zip->close();
				unlink($target_path);
			}
			$message = "Your .zip file was uploaded and unpacked.";
		} else {	
			$message = "There was a problem with the upload. Please try again.";
			die($message);
		}
	}else{
		$message = "No zip file provided";
		die($message);
	}
	
	//Check uploaded files
	$fileDir = (str_replace("../", "", $target_dir_full));
	//$target_dir = (str_replace("../", "", $target_dir));
	$fileLocation = $fileDir . "index.html";
	ValidateUploadedFilesRec($target_dir_full);
	
	//Zip contains more than just the allowed file types.
	if(!$FileValidation){
		print "<font color='blue'>$target_dir</font>";
		deleteDir($target_dir);
		die("ERROR: Zip contains more than just the allowed file types.");
	}
	
	print "<p>".$fileDir;
	print "<p>";
	
	//Process 
	$name = FromPostIfExist("newGameName", "");
	$description = FromPostIfExist("newGameDescription", "");
	$signatureVersion = "1.0";
	$author = FromPostIfExist("newGameAuthor", "");
	$dateOfBuild = FromPostIfExist("newGameDateOfBuild", "");
	$version = FromPostIfExist("newGameVersion", "");
	$license = FromPostIfExist("newGameLicense", "");
	$teacherInstructions = FromPostIfExist("newGameTeacherInstructions", "");
	$comment = FromPostIfExist("newGameComment", "");
	$languages = "SL|EN";

	//Get available styles
	$styles = Array("default", "blind", "inverted", "contrast", "protanopia", "tritanopia", "achromatopsia");
	$stylesAvailableList = Array();
	foreach($styles as $i => $style){
		$isSet = FromPostIfExist("newGameStylesAvailable-".$style, "");
		if($isSet != ""){
			$stylesAvailableList[] = $style;
		}
	}
	$stylesAvailable = implode("|", $stylesAvailableList);
	
	
	//Fetch language variables
	$dictionary = Array();
	for($i = 0; $i < 10000; $i++){
		$langVarName = FromPostIfExist("langvarname".$i, "");
		$langVarEnglish = FromPostIfExist("langvarenglish".$i, "");
		$langVarSlovene = FromPostIfExist("langvarslovene".$i, "");
		if($langVarName && strlen($langVarName) > 0){
			$dictionary["SL"]["language"] = "Slovensko";
			$dictionary["SL"]["dict"][$langVarName] = $langVarSlovene;
			$dictionary["EN"]["language"] = "English";
			$dictionary["EN"]["dict"][$langVarName] = $langVarEnglish;
		}
	}
	
	//Handle the predefined variables.
	$predefinedVariables = Array("Difficulty", "TimeLimit", "Levels", "Retries");
	$predVar = Array();
	foreach($predefinedVariables as $i => $var){
		$val = Array();
		$val["min"] = intval(FromPostIfExist($var."Min", '0'));
		$val["max"] = intval(FromPostIfExist($var."Max", '0'));
		$val["default"] = intval(FromPostIfExist($var."Value", '0'));
		$val["editable"] = intval(FromPostIfExist($var."Editable", '0'));
		$val["edit_instructions"] = FromPostIfExist($var."Instructions", '0');
		$predVar[$var] = $val;
	}
	$difficulty = $predVar["Difficulty"];
	$timeLimit = $predVar["TimeLimit"];
	$levels = $predVar["Levels"];
	$retriesAvailable = $predVar["Retries"];
	
	//Fetch variables
	$variables = Array();
	for($i = 0; $i < 10000; $i++){
		$varName = FromPostIfExist("varname".$i, "");
		$varType = FromPostIfExist("vartype".$i, "");
		$varValue = FromPostIfExist("varvalue".$i, "");
		$varDesc = FromPostIfExist("vardesc".$i, "");
		$varInstructions = FromPostIfExist("varinstructions".$i, "");
		$varConstraints = FromPostIfExist("varconstraints".$i, "");
		if($varName && strlen($varName) > 0 && $varType && strlen($varType) > 0){
			print "<br /><b>Pre $varName: </b>";
			print_r($varValue);
			if($varType == "stringlist" || $varType == "filelist"){
				//Handle lists
				$varValue = explode("|", $varValue);
			}
			$variables[$varName]["type"] = $varType;
			$variables[$varName]["default_value"] = $varValue;
			print "<br /><b>Post $varName: </b>";
			print_r($varValue);
			print "<br/>";
			$variables[$varName]["description"] = $varDesc;
			$variables[$varName]["edit_instructions"] = $varInstructions;
			$variables[$varName]["constraints"] = $varConstraints;
		}
	}
	
	//Fetch constants
	$constants = Array();
	for($i = 0; $i < 10000; $i++){
		$constName = FromPostIfExist("constname".$i, "");
		$constValue = FromPostIfExist("constvalue".$i, "");
		if($constName && strlen($constName) > 0){
			$constants[] = Array("name" => $constName, "value" => $constValue);
		}
	}
	
	print $name . "<br>";
	print $fileLocation . "<br>";
	print $description . "<br>";
	print $signatureVersion . "<br>";
	print $author . "<br>";
	print $dateOfBuild . "<br>";
	print $version . "<br>";
	print $license . "<br>";
	print $teacherInstructions . "<br>";
	print $comment . "<br>";
	print $stylesAvailable . "<br>";
	print $languages . "<br>";
	print_r($dictionary);
	print "<br>";
	print_r($variables);
	print "<br>";
	print_r($difficulty);
	print "<br>";
	print_r($timeLimit);
	print "<br>";
	print_r($levels);
	print "<br>";
	print_r($retriesAvailable);
	print "<br>";
	print_r($constants);
	print "<br>";
	if($GLOBAL_IsLoggedIn){
		DB_GameInsert($GLOBAL_UserInfo["user_id"], $GLOBAL_IP, $name, $fileLocation, $description, $signatureVersion, $author, $dateOfBuild, $version, $license, $teacherInstructions, $comment, $stylesAvailable, $languages, $dictionary, $variables, $difficulty, $timeLimit, $levels, $retriesAvailable, $constants);
	}else{
		ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	}
?>
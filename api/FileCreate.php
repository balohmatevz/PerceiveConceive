<?php
	include("../php/init.php");
	
	print_r( $_FILES);
	
	//TODO Authentication + authorization
	//if($GLOBAL_IsLoggedIn){
	//	ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	//}
	
	$FileValidation = true;
	function ValidateUploadedFilesRec($main){
		global $FileValidation, $GLOBAL_FileUploadValidFileExtensions;
		$dirHandle = opendir($main);
		while($file = readdir($dirHandle)){
			if($file != '.' && $file != '..'){
				if(is_dir($main.$file)){
					print "<li><b>Entering: ".$main.$file."/</b>";
					ValidateUploadedFilesRec($main.$file."/");
				}else{
					$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
					if(in_array($ext, $GLOBAL_FileUploadValidFileExtensions)){
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
	if($_FILES["NewFile"]["name"]) {
		$filename = $_FILES["NewFile"]["name"];
		$source = $_FILES["NewFile"]["tmp_name"];
		$type = $_FILES["NewFile"]["type"];
		
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
		
		print "<font color=red>".$GLOBAL_FileUploadRootFolder . $GLOBAL_LoggedInUserId."</font>";
		if(!file_exists($GLOBAL_FileUploadRootFolder . $GLOBAL_LoggedInUserId)){
			mkdir($GLOBAL_FileUploadRootFolder . $GLOBAL_LoggedInUserId);
		};
		
		$FolderNumber = 0;
		for($i = 0; $i < 10000; $i++){
			if(!file_exists($GLOBAL_FileUploadRootFolder . $GLOBAL_LoggedInUserId ."/". $i)){
				$FolderNumber = $i;
				break;
			};
		}
		
		print $GLOBAL_FileUploadRootFolder . $GLOBAL_LoggedInUserId ."/". $FolderNumber;
		mkdir($GLOBAL_FileUploadRootFolder . $GLOBAL_LoggedInUserId ."/". $FolderNumber);
		$target_dir = $GLOBAL_FileUploadRootFolder . $GLOBAL_LoggedInUserId ."/". $FolderNumber;
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
	ValidateUploadedFilesRec($target_dir_full);
	
	//Zip contains more than just the allowed file types.
	if(!$FileValidation){
		print "<font color='blue'>$target_dir</font>";
		deleteDir($target_dir);
		die("ERROR: Zip contains more than just the allowed file types. Allowed: $GLOBAL_FileUploadValidFileExtensions");
	}
	
	print "SUCCESS!";
?>
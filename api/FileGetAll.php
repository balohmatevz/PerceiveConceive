<?php
	include("../php/init.php");
	
	$fileList = Array();
	$fileList["files"] = Array();
	
	//List of valid extensions for returned files.
	$validExtensions = Array("png", "svg", "bmp", "jpg", "jpeg");
	
	//function from http://stackoverflow.com/questions/7121479/listing-all-the-folders-subfolders-and-files-in-a-directory-using-php
	function listFolderFiles($pathBack, $dir){
		global $fileList, $validExtensions;
		
		$folder = scandir($pathBack . $dir);
		foreach($folder as $entry){
			if($entry != '.' && $entry != '..'){
				if(is_dir($pathBack . $dir.'/'.$entry)){
					listFolderFiles($pathBack, $dir.'/'.$entry);
				}
				if(is_file($pathBack . $dir.'/'.$entry)){
					$ext = pathinfo(strtolower($entry), PATHINFO_EXTENSION);
					if(in_array($ext, $validExtensions)){
						$fileList["files"][] = Array("location" => $dir."/".$entry, "name" => $entry);
					}
				}
			}
		}
	}
	listFolderFiles("../", "files");
	
	$fileList["result"] = "SUCCESS";
	$fileList["resultText"] = "{{SUCCESS_FILE_LIST_RETRIEVED}}";
	
	print json_encode($fileList);
?>
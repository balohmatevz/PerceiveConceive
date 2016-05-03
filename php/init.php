<?php

	//Initialization process, loaded and executed at the start of every page.

	$ip = $_SERVER["REMOTE_ADDR"];
	$userAgent = $_SERVER["HTTP_USER_AGENT"];
	include("../config/config.php");
	include("db.php");
	include("helpers.php");
	ConnectToDB();
	
	$GLOBAL_SessionId = FromCookieIfExist("sessionID", "");
	
	$GLOBAL_IP = $ip;
	$GLOBAL_UserAgent = $userAgent;
	$GLOBAL_LoggedInUserId = 0;
	$GLOBAL_UserInfo = Array();
	$GLOBAL_IsLoggedIn = false;
	$GLOBAL_GameUploadRootFolder = "../games/upload/";	//Relative to GameCreate.php file
	$GLOBAL_GameUploadValidFileExtensions = Array("js", "html", "htm", "css");
	$GLOBAL_FileUploadRootFolder = "../files/upload/";	//Relative to FileCreate.php file
	$GLOBAL_FileUploadValidFileExtensions = Array("svg", "png", "bmp", "ico", "jpg", "jpeg", "mp3");
	$GLOBAL_IsAdmin = false;
	$GLOBAL_IsTeacher = false;
	
	if($GLOBAL_SessionId){
		$GLOBAL_LoggedInUserId = DB_UserIDFromSessionID($GLOBAL_SessionId, $GLOBAL_IP, $GLOBAL_UserAgent);
		if($GLOBAL_LoggedInUserId){
			$GLOBAL_IsLoggedIn = true;
			$GLOBAL_UserInfo = DB_GetUserPreferences($GLOBAL_LoggedInUserId, $GLOBAL_LoggedInUserId);
			$GLOBAL_IsAdmin = $GLOBAL_UserInfo["role"] == "ADMIN";
			
			$teacherStatus = DB_UserIsTeacherInAnyClass($GLOBAL_LoggedInUserId);
			if($teacherStatus["role"] == "TEACHER"){
				$GLOBAL_IsTeacher = true;
			}
		}
	}

	//Returns true/false based on whether the user is an admin or not.
	function IsUser(){
		global $GLOBAL_IsLoggedIn;
		
		return $GLOBAL_IsLoggedIn;
	}

	//Returns true/false based on whether or not the user is a teacher in any class.
	function IsTeacher(){
		global $GLOBAL_IsAdmin, $GLOBAL_IsTeacher;
		
		return $GLOBAL_IsAdmin || $GLOBAL_IsTeacher;
	}

	//Returns true/false based on whether the user is an admin or not.
	function IsAdmin(){
		global $GLOBAL_IsAdmin;
		
		return $GLOBAL_IsAdmin;
	}
	
	//Returns true/false based on whether the logged in user is a teacher in the given class
	function IsTeacherInClass($classID){
		global $GLOBAL_IsAdmin, $GLOBAL_UserInfo;
		
		if( $GLOBAL_IsAdmin == "ADMIN" ){
			//Admins are teachers in every class
			return true;
		}
		
		$Data = DB_GetUserRoleInClass($userID, $classID);
		
		return intval($Data["role"]) == 2;
	}

?>
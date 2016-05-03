<?php

	include("../php/init.php");
	
	//For role, see UserMakeAdmin and UserRemoveAdmin
	//For password, see UserChangePassword
	
	//userID checked within DB_UserUpdate(..)
	$userID = intval(FromPostIfExist("updateUserID", ""));
	$username = FromPostIfExist("updateUserUsername", "");
	$name = FromPostIfExist("updateUserName", "");
	$surname = FromPostIfExist("updateUserSurname", "");
	$language = FromPostIfExist("preferredUserLanguage", "");
	$style = FromPostIfExist("preferredUserStyle", "");
	
	if($userID && $GLOBAL_LoggedInUserId){
		print json_encode(DB_UserUpdate($userID, $GLOBAL_LoggedInUserId, $username, $name, $surname, $ip, $language, $style));
	}else{
		ThrowErrorAndDie(ERROR_NO_USER_ID_OR_NOT_LOGGED_IN);
	}
?>
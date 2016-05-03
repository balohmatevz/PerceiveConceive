<?php

	include("../php/init.php");
	
	$userID = intval(FromPostIfExist("userID", ""));
	$password1 = trim(FromPostIfExist("updateUserPassword1", ""));
	$password2 = trim(FromPostIfExist("updateUserPassword2", ""));
	$passwordOld = trim(FromPostIfExist("updateUserPasswordOld", ""));
	
	if($password1 != $password2){
		ThrowErrorAndDie(ERROR_PASSWORDS_DO_NOT_MATCH);
	}
	
	if($userID){
		print json_encode(DB_UserChangePassword($userID, $GLOBAL_LoggedInUserId, $passwordOld, $password1));
	}else{
		ThrowErrorAndDie(ERROR_NO_USER_ID_PROVIDED);
	}
	
?>
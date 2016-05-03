<?php

	include("../php/init.php");
	
	$username = FromPostIfExist("createUserUsername", "");
	$password = FromPostIfExist("createUserPassword", "");
	$name = FromPostIfExist("createUserName", "");
	$surname = FromPostIfExist("createUserSurname", "");
	$role = intval(FromPostIfExist("createUserRole", ""));
	$language = FromPostIfExist("createUserLanguage", "");
	$styles = FromPostIfExist("createUserStyles", "");
	
	DB_UserInsert($username, $password, $ip, $userAgent);;
	
?>
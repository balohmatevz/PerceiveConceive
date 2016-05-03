<?php

	include("../php/init.php");
	
	$username = FromPostIfExist("createUserUsername", "");
	$password = FromPostIfExist("createUserPassword", "");
	$name = FromPostIfExist("createUserName", "");
	$surname = FromPostIfExist("createUserSurname", "");
	$role = 1;
	$language = "SL";
	$styles = "default";
	
	print json_encode(DB_UserInsert($username, $password, $name, $surname, $ip, $role, $language, $styles));
	
?>
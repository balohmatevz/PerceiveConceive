<?php
//Database related functions including connection and for each table the relevant insert, update, delete functions.
$DBConnected = false;

//Error messages
define("ERROR_NO_DB_CONNECTION", "{{ERROR_NO_DB_CONNECTION}}");
define("ERROR_USER_ALREADY_IN_DATABASE", "{{ERROR_USER_ALREADY_IN_DATABASE}}");
define("ERROR_USER_NOT_IN_DATABASE", "{{ERROR_USER_NOT_IN_DATABASE}}");
define("ERROR_WRONG_USERNAME_AND_PASSWORD", "{{ERROR_WRONG_USERNAME_AND_PASSWORD}}");
define("ERROR_USER_WRONG_OLD_PASSWORD", "{{ERROR_USER_WRONG_OLD_PASSWORD}}");
define("ERROR_MORE_THAN_ONE_USER_MACHES_CREDENTIALS", "{{ERROR_MORE_THAN_ONE_USER_MACHES_CREDENTIALS}}");
define("ERROR_NOT_LOGGED_IN", "{{ERROR_NOT_LOGGED_IN}}");
define("ERROR_CLASS_NOT_IN_DATABASE", "{{ERROR_CLASS_NOT_IN_DATABASE}}");
define("ERROR_GAME_NOT_IN_DATABASE", "{{ERROR_GAME_NOT_IN_DATABASE}}");
define("ERROR_INSTANCE_NOT_IN_DATABASE", "{{ERROR_INSTANCE_NOT_IN_DATABASE}}");
define("ERROR_GAME_OR_INSTANCE_NOT_IN_DATABASE", "{{ERROR_GAME_OR_INSTANCE_NOT_IN_DATABASE}}");
define("ERROR_NO_CLASS_ID_PROVIDED", "{{ERROR_NO_CLASS_ID_PROVIDED}}");
define("ERROR_NO_USER_ID_PROVIDED", "{{ERROR_NO_USER_ID_PROVIDED}}");
define("ERROR_NO_SESSION_ID_PROVIDED", "{{ERROR_NO_SESSION_ID_PROVIDED}}");
define("ERROR_NO_INSTANCE_ID_PROVIDED", "{{ERROR_NO_INSTANCE_ID_PROVIDED}}");
define("ERROR_NO_GAME_ID_PROVIDED", "{{ERROR_NO_GAME_ID_PROVIDED}}");
define("ERROR_STUDENT_ALREADY_ENROLLED", "{{ERROR_STUDENT_ALREADY_ENROLLED}}");
define("ERROR_CANNOT_DELETE_GAME_HAS_INSTANCES", "{{ERROR_CANNOT_DELETE_GAME_HAS_INSTANCES}}");
define("ERROR_STUDENT_NOT_ENROLLED", "{{ERROR_STUDENT_NOT_ENROLLED}}");
define("ERROR_USER_ALREADY_ADMIN", "{{ERROR_USER_ALREADY_ADMIN}}");
define("ERROR_USER_NOT_ADMIN", "{{ERROR_USER_NOT_ADMIN}}");
define("ERROR_STUDENT_ALREADY_TEACHER", "{{ERROR_STUDENT_ALREADY_TEACHER}}");
define("ERROR_STUDENT_NOT_TEACHER", "{{ERROR_STUDENT_NOT_TEACHER}}");
define("ERROR_PASSWORDS_DO_NOT_MATCH", "{{ERROR_PASSWORDS_DO_NOT_MATCH}}");
define("ERROR_INVALID_ORDER_BY_PARAMETER", "{{ERROR_INVALID_ORDER_BY_PARAMETER}}");
define("ERROR_UNKNOWN_AUTH_LEVEL", "{{ERROR_UNKNOWN_AUTH_LEVEL}}");
define("ERROR_PERMISION_DENIED", "{{ERROR_PERMISION_DENIED}}");
define("ERROR_SESSION_CREATION_ERROR", "{{ERROR_SESSION_CREATION_ERROR}}");
define("ERROR_SESSION_USER_NOT_LOGGED_IN", "{{ERROR_SESSION_USER_NOT_LOGGED_IN}}");

//Database tables
define("DB_TABLE_USER", "zs_user");
define("DB_TABLE_SESSION", "zs_session");
define("DB_TABLE_CLASS", "zs_class");
define("DB_TABLE_STUDENT", "zs_student");
define("DB_TABLE_INSTANCE", "zs_instance");
define("DB_TABLE_SCORE", "zs_score");
define("DB_TABLE_GAME", "zs_game");
define("DB_TABLE_VARIABLE", "zs_variable");
define("DB_TABLE_CONSTANT", "zs_constant");

//Authorization levels
define("AUTH_SKIP", -2);
define("AUTH_UNREGISTERED", -1);
define("AUTH_USER", 1);
define("AUTH_TEACHER", 2);
define("AUTH_ADMIN", 3);

//Other constants
define('PHP_INT_MIN', ~PHP_INT_MAX);

//Connects to DB
//parameters: none
//returns nothing
function ConnectToDB(){
	global $DBConnected, $DB_Host, $DB_Username, $DB_Password, $DB_DatabaseName;
	
	if($DBConnected){
		return;
	}
	
	mysql_connect($DB_Host, $DB_Username, $DB_Password) or die("Database connection error"); 
	mysql_select_db($DB_DatabaseName) or die("Database selection failure");	
	mysql_query('SET character_set_results="utf8"') or die("Database result type setting failure");	
	mysql_set_charset ("utf8");
	$DBConnected = true;
}

//Validates based on conditions, dies if fails, 
//parameters: descriptor (i.e. username), type (see Validate() function for list), min and max for type.
//returns nothing (dies)
function ValidateOrDie($descriptor, $value, $type, $min, $max, $TextOnFail = "Error"){
	if(!Validate($value, $type, $min, $max)){
		ThrowErrorAndDie("$TextOnFail");
	}
}

//Validates value based on given type, min and max values.
//parameters: value to test, expected type, min and max for given type. See this function's case statement for valid types
//returns true/false
function Validate($value, $type, $min, $max){
	switch($type){
		case "IP":
			return filter_var($value, FILTER_VALIDATE_IP);
		break;
		case "STRING":
		case "PASSWORD":
		case "DATETIME":
		case "FILE":
			return (is_string($value)) && (strlen($value) >= $min) && (strlen($value) <= $max);
		break;
		case "INT":
			return (is_int($value)) && ($value >= $min) && ($value <= $max);
		break;
		case "ARRAY":
			return (is_array($value));
		break;
		default:
			ThrowErrorAndDie("UNKNOWN VALIDATION TYPE: $type");
		break;
	}
}
function ValidateAndGenerateUpdateStatement($attributeName, $attributeType, $descriptor, $value, $validationType, $min, $max){
	if(Validate($value, $validationType, $min, $max)){
		switch($attributeType){
			case "VARCHAR":
				return "$attributeName = '$value' ";
			break;
			case "PASSWORD":
				return "$attributeName = '".GeneratePassword($value)."' ";
			break;
			case "INT":
				return "$attributeName = $value ";
			break;
		}
	}
}

//Checks if a connection exists, dies if it does not.
//No parameters
//Returns nothing (dies)
function CheckConnectionOrDie(){
	global $DBConnected;
	
	if(!$DBConnected){
		ThrowErrorAndDie(ERROR_NO_DB_CONNECTION);
	}
}

function AuthorizeUser($requiredAuth, $table, $operation){	//TODO Remove $table - $operation
	switch($requiredAuth){
		case AUTH_SKIP:
			return true;
		break;
		case AUTH_UNREGISTERED:
			return true;
		break;
		case AUTH_USER:
			return IsUser();
		break;
		case AUTH_TEACHER:
			return IsTeacher();
		break;
		case AUTH_ADMIN:
			return IsAdmin();
		break;
		default:
			ThrowErrorAndDie(ERROR_UNKNOWN_AUTH_LEVEL . " $table - $operation");	//TODO Remove $table - $operation
		break;
	}
}

//Checks if the current user can perform this action.
//Parameters: table identifier (string), operation identifier (string)
//Returns nothing (dies)
function CheckAuthorizationOrDie($table, $operation){
	//TODO: Fill authorization.
	$requiredAuth = 0; //Required authorization level AUTH_UNREGISTERED, AUTH_USER, AUTH_TEACHER, AUTH_ADMIN, etc.
	switch($table){
		case "USER":
			switch($operation){
				case "INSERT":
					$requiredAuth = AUTH_UNREGISTERED;
				break;
				case "UPDATE_SELF":
					$requiredAuth = AUTH_USER;
				break;
				case "UPDATE_OTHER_USER":
					$requiredAuth = AUTH_ADMIN;
				break;
				case "UPDATE_PASSWORD_SELF":
					$requiredAuth = AUTH_USER;
				break;
				case "UPDATE_PASSWORD_OTHER_USER":
					$requiredAuth = AUTH_ADMIN;
				break;
				case "SELECT_USER_ID_FROM_USERNAME":
					$requiredAuth = AUTH_UNREGISTERED;
				break;
				case "SELECT_USER_ID_FROM_USER_ID":
					$requiredAuth = AUTH_UNREGISTERED;
				break;
				case "SELECT_USER_INFO_FROM_USER_ID":
					$requiredAuth = AUTH_UNREGISTERED;
				break;
				case "SELECT_USER_ID_FROM_CREDENTIALS":
					$requiredAuth = AUTH_UNREGISTERED;
				break;
				case "SELECT_USER_PREFS_FROM_USER_ID_SELF":
					$requiredAuth = AUTH_USER;
				break;
				case "SELECT_USER_PREFS_FROM_USER_ID_OTHER_USER":
					$requiredAuth = AUTH_ADMIN;
				break;
				case "SOFT_DELETE_BY_USER_ID":
					$requiredAuth = AUTH_ADMIN;
				break;
				case "USER_MAKE_ADMIN":
					$requiredAuth = AUTH_ADMIN;
				break;
				case "USER_REMOVE_ADMIN":
					$requiredAuth = AUTH_ADMIN;
				break;
				case "SELECT_USER_ROLE_FROM_USER_ID":
					$requiredAuth = AUTH_UNREGISTERED;
				break;
				case "SELECT_USER_ID_FROM_USER_ID_AND_PASSWORD_HASH":
					$requiredAuth = AUTH_UNREGISTERED;
				break;
				case "SELECT_ALL_USER_INFO":
					$requiredAuth = AUTH_TEACHER;
				break;
				default:
					die("Authorization error: $table - $operation is not valid");
				break;
			}
		break;
		case "SESSION":
			switch($operation){
				case "SELECT_USER_ID_FROM_SESSION_ID":
					$requiredAuth = AUTH_UNREGISTERED;
				break;
				case "INSERT_NEW_SESSION":
					$requiredAuth = AUTH_UNREGISTERED;
				break;
				case "DELETE_SESSION_BY_SESSION_ID":
					$requiredAuth = AUTH_USER;
				break;
				case "SELECT_USER_ID_BY_SESSION_ID":
					$requiredAuth = AUTH_UNREGISTERED;
				break;
				default:
					die("Authorization error: $table - $operation is not valid");
				break;
			}
		break;
		case "CLASS":
			switch($operation){
				case "INSERT_NEW_CLASS":
					$requiredAuth = AUTH_TEACHER;
				break;
				case "SELECT_CLASS_ID_FROM_CLASS_ID":
					$requiredAuth = AUTH_UNREGISTERED;
				break;
				case "UPDATE_CLASS_INFO":
					$requiredAuth = AUTH_TEACHER;
				break;
				case "SELECT_INFO_FOR_ALL_CLASSES":
					$requiredAuth = AUTH_USER;
				break;
				case "SELECT_INFO_FOR_CLASS_BY_ID":
					$requiredAuth = AUTH_USER;
				break;
				case "SOFT_DELETE_CLASS_BY_CLASS_ID":
					$requiredAuth = AUTH_TEACHER;
				break;
				default:
					die("Authorization error: $table - $operation is not valid");
				break;
			}
		break;
		case "STUDENT":
			switch($operation){
				case "ENROLL_STUDENT_INTO_CLASS_SELF":
					$requiredAuth = AUTH_USER;
				break;
				case "ENROLL_STUDENT_INTO_CLASS_OTHER_USER":
					$requiredAuth = AUTH_TEACHER;
				break;
				case "SELECT_STUDENT_FROM_CLASS_ID_AND_USER_ID":
					$requiredAuth = AUTH_USER;
				break;
				case "DELETE_STUDENT_BY_CLASS_ID_AND_USER_ID_SELF":
					$requiredAuth = AUTH_USER;
				break;
				case "DELETE_STUDENT_BY_CLASS_ID_AND_USER_ID_OTHER_USER":
					$requiredAuth = AUTH_TEACHER;
				break;
				case "SELECT_CLASS_INFO_FOR_SELF":
					$requiredAuth = AUTH_USER;
				break;
				case "SELECT_CLASS_INFO_FOR_OTHER_USER":
					$requiredAuth = AUTH_TEACHER;
				break;
				case "SELECT_STUDNET_ROLE_FROM_STUDENT_ID_AND_CLASS_ID":
					$requiredAuth = AUTH_USER;
				break;
				case "SELECT_STUDENT_ROLE_FROM_USER_ID":
					$requiredAuth = AUTH_USER;
				break;
				case "SELECT_STUDENT_ROLE_FROM_USER_ID_AND_CLASS_ID":
					$requiredAuth = AUTH_USER;
				break;
				case "STUDENT_MAKE_TEACHER":
					$requiredAuth = AUTH_TEACHER;
				break;
				case "STUDENT_REMOVE_TEACHER":
					$requiredAuth = AUTH_TEACHER;
				break;
				case "GET_TEACHER_LIST":
					$requiredAuth = AUTH_USER;
				break;
				case "GET_ENROLEE_LIST":
					$requiredAuth = AUTH_USER;
				break;
				default:
					die("Authorization error: $table - $operation is not valid");
				break;
			}
		break;
		case "INSTANCE":
			switch($operation){
				case "SELECT_INSTANCE_ID_FROM_INSTANCE_ID":
					$requiredAuth = AUTH_UNREGISTERED;
				break;
				case "UPDATE_INSTANCE_INFO":
					$requiredAuth = AUTH_TEACHER;
				break;
				case "SELECT_INSTANCES_IN_CLASS":
					$requiredAuth = AUTH_USER;
				break;
				case "SELECT_INSTANCES_FOR_GAME":
					$requiredAuth = AUTH_TEACHER;
				break;
				case "SELECT_INSTANCE_INFO_FROM_ID":
					$requiredAuth = AUTH_USER;
				break;
				case "SOFT_DELETE_INSTANCE_BY_INSTANCE_ID":
					$requiredAuth = AUTH_TEACHER;
				break;
				default:
					die("Authorization error: $table - $operation is not valid");
				break;
			}
		break;
		case "SCORE":
			switch($operation){
				case "INSERT_SCORE":
					$requiredAuth = AUTH_USER;
				break;
				case "SELECT_SCORE_INFO_FROM_INSTANCE_ID":
					$requiredAuth = AUTH_USER;
				break;
				case "SELECT_SCORE_LIST_FOR_INSTANCE_BY_INSTANCE_ID":
					$requiredAuth = AUTH_USER;
				break;
				case "SELECT_SCORE_INFO_FROM_INSTANCE_ID_AND_USER_ID":
					$requiredAuth = AUTH_USER;
				break;
				default:
					die("Authorization error: $table - $operation is not valid");
				break;
			}
		break;
		case "GAME":
			switch($operation){
				case "SELECT_GAME_ID_FROM_GAME_ID":
					$requiredAuth = AUTH_UNREGISTERED;
				break;
				case "SELECT_GAME_VARIABLES_FROM_GAME_ID":
					$requiredAuth = AUTH_USER;
				break;
				case "INSERT_GAME":
					$requiredAuth = AUTH_ADMIN;
				break;
				case "SELECT_ALL_GAMES":
					$requiredAuth = AUTH_USER;
				break;
				case "SELECT_INFO_FOR_GAME_BY_GAME_ID":
					$requiredAuth = AUTH_USER;
				break;
				case "SELECT_MAX_GAME_ID":
					$requiredAuth = AUTH_USER;
				break;
				case "SOFT_DELETE_GAME_BY_GAME_ID":
					$requiredAuth = AUTH_ADMIN;
				break;
				default:
					die("Authorization error: $table - $operation is not valid");
				break;
			}
		break;
		case "VARIABLE":
			switch($operation){
				case "INSERT_VARIABLE":
					$requiredAuth = AUTH_TEACHER;
				break;
				case "SELECT_VARIABLES_FOR_INSTANCE":
					$requiredAuth = AUTH_USER;
				break;
				case "SELECT_VARIABLES_FOR_GAME":
					$requiredAuth = AUTH_TEACHER;
				break;
				case "SELECT_GAME_DEFAULTS":
					$requiredAuth = AUTH_TEACHER;
				break;
				default:
					die("Authorization error: $table - $operation is not valid");
				break;
			}
		break;
		case "CONSTANT":
			switch($operation){
				case "INSERT_CONSTANT":
					$requiredAuth = AUTH_TEACHER;
				break;
				case "SELECT_CONSTANTS_FOR_GAME":
					$requiredAuth = AUTH_USER;
				break;
				default:
					die("Authorization error: $table - $operation is not valid");
				break;
			}
		break;
		case "SCHEMA":
			switch($operation){
				case "SELECT_NEXT_VALID_INSTANCE_ID":
					$requiredAuth = AUTH_TEACHER;
				break;
				default:
					die("Authorization error: $table - $operation is not valid");
				break;
			}
		break;
		default:
			die("Authorization error: $table - $operation is not valid");
		break;
	}
	
	$authStatus = AuthorizeUser($requiredAuth, $table, $operation);	//TODO Remove $table - $operation
	
	if($authStatus){
		return true;
	}else{
		throwErrorAndDie(ERROR_PERMISION_DENIED ." $table - $operation");	//TODO Remove $table - $operation
	}
	
}

//Checks connection, authorization and executes the given sql.
//Parameters: table identifier (string), operation identifier (string), sql to execute.
function ExecuteSQLOrDie($table, $operation, $sql){
	CheckConnectionOrDie();
	CheckAuthorizationOrDie($table, $operation);
	$data = mysql_query($sql) or die(mysql_error());
	return $data;
}

//Each function in this following section is labelled by the table identifier it is most associated with. The function names say what kind of operation of view it is related to.

//USER

//Retrieve list of all users
function DB_UserGetAll(){
	//TODO Pagination
	$result = Array();
	
	$sql = "SELECT * FROM ".DB_TABLE_USER." WHERE user_deleted = 0 ORDER BY user_id";
	$data = ExecuteSQLOrDie("USER", "SELECT_ALL_USER_INFO", $sql);
	$sql = "";
	while($info = mysql_fetch_array($data)){
		$row = Array();
		$row["user_id"] = $info["user_id"];
		$row["username"] = $info["user_username"];
		$row["name"] = $info["user_name"];
		$row["surname"] = $info["user_surname"];
		$row["role"] = $info["user_role"];
		$result["userlist"][] = $row;
	}
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_USER_LIST_RETRIEVED}}";
	return $result;
}

//Insert new user
function DB_UserInsert($username, $password, $name, $surname, $ip, $role, $language, $styles){
	global $userAgent;
	
	ValidateOrDie("username", 	$username, 	"STRING", 	2, 32, "{{VALIDATION_USERNAME_WRONG_LENGTH1}} 2 {{AND}} 32 {{VALIDATION_USERNAME_WRONG_LENGTH2}}");
	ValidateOrDie("password", 	$password, 	"PASSWORD",	2, 32, "{{VALIDATION_PASSWORD_WRONG_LENGTH1}} 2 {{AND}} 32 {{VALIDATION_PASSWORD_WRONG_LENGTH2}}");
	ValidateOrDie("name",		$name, 		"STRING",	2, 255, "{{VALIDATION_NAME_WRONG_LENGTH1}} 2 {{VALIDATION_NAME_WRONG_LENGTH2}}");
	ValidateOrDie("surname", 	$surname, 	"STRING", 	2, 255, "{{VALIDATION_SURNAME_WRONG_LENGTH1}} 2 {{VALIDATION_SURNAME_WRONG_LENGTH2}}");
	ValidateOrDie("ip", 		$ip, 		"IP", 		0, 0, "{{VALIDATION_IP}}");
	ValidateOrDie("role", 		$role, 		"INT", 		AUTH_STUDENT, AUTH_TEACHER, "{{VALIDATION_ROLE}}");
	ValidateOrDie("language", 	$language,	"STRING", 	0, 45, "{{VALIDATION_INVALID_LANGUAGE}}");
	ValidateOrDie("styles",		$styles,	"STRING", 	0, 255, "{{VALIDATION_INVALID_STYLE}}");
	
	$username = mysql_real_escape_string($username);
	$password = mysql_real_escape_string($password);
	$name = mysql_real_escape_string($name);
	$surname = mysql_real_escape_string($surname);
	$ip = mysql_real_escape_string($ip);
	$role = mysql_real_escape_string($role);
	$language = mysql_real_escape_string($language);
	$styles = mysql_real_escape_string($styles);
	
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_username = '$username' AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USERNAME", $sql);
	$sql = "";
	if(mysql_num_rows($data) != 0){
		ThrowErrorAndDie(ERROR_USER_ALREADY_IN_DATABASE);
	}
	
	$sql = "INSERT INTO ".DB_TABLE_USER."
	(user_id, user_ip, user_datetime, user_username, user_password_hash, user_name, user_surname, user_role, user_language, user_styles, user_deleted)
	VALUES
	(null, '$ip', Now(), '$username', '".GeneratePassword($password)."', '$name', '$surname', $role, '$language', '$styles', 0);";
	ExecuteSQLOrDie("USER", "INSERT", $sql); 
	$sql = "";
	
	return Array("result" => "SUCCESS", "resultText" => "{{SUCCESS_USER_REGISTERED}}");
	
	//return DB_LogInUser($username, $password, $ip, $userAgent);
}

//Updates user settings, to change role see DB_UserMakeAdmin, DB_UserRemoveAdmin; For password see DB_UserChangePassword
function DB_UserUpdate($userID, $loggedInUserID, $username = "", $name = "", $surname = "", $ip = "", $language = "sl", $styles = "default"){
	ValidateOrDie("userID", 	$userID,	"INT", 		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	
	$userID = mysql_real_escape_string($userID);
	
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	$sql = "UPDATE ".DB_TABLE_USER." SET ";
	$updateStatements = Array();
	
	$statement = ValidateAndGenerateUpdateStatement("user_username", "VARCHAR",	"username", 	$username, 	"STRING",	2, 32);
	if($statement){$updateStatements[] = $statement;};
	
	$statement = ValidateAndGenerateUpdateStatement("user_name",		"VARCHAR",	"name",		$name, 		"STRING",	2, 255);
	if($statement){$updateStatements[] = $statement;};
	
	$statement = ValidateAndGenerateUpdateStatement("user_surname",	"VARCHAR",	"surname", 		$surname, 	"STRING",	2, 255);
	if($statement){$updateStatements[] = $statement;};
	
	$statement = ValidateAndGenerateUpdateStatement("user_ip", 		"VARCHAR",	"ip", 			$ip, 		"IP",		0, 0);
	if($statement){$updateStatements[] = $statement;};
	
	$statement = ValidateAndGenerateUpdateStatement("user_language","VARCHAR",	"language",		$language,	"STRING",	0, 45);
	if($statement){$updateStatements[] = $statement;};
	
	$statement = ValidateAndGenerateUpdateStatement("user_styles", 	"VARCHAR",		"styles", 		$styles, 		"STRING",		0, 255);
	if($statement){$updateStatements[] = $statement;};
	
	$sql .= implode(", ",$updateStatements);
	$sql .= " WHERE user_id = $userID AND user_deleted = 0";
	
	if($userID == $loggedInUserID){
		//Updating self
		ExecuteSQLOrDie("USER", "UPDATE_SELF", $sql);
	}else{
		//Updating other user
		ExecuteSQLOrDie("USER", "UPDATE_OTHER_USER", $sql);
	}
	$sql = "";
	return Array("result" => "SUCCESS", "resultText" => "{{SUCCESS_USER_UPDATED}}");
}

//Change password for user
function DB_UserChangePassword($userID, $loggedInUserID, $oldPassword, $newPassword){
	ValidateOrDie("userID", 	$userID,	"INT", 		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	ValidateOrDie("old password",	$oldPassword, "PASSWORD",	2, 32, "{{VALIDATION_PASSWORD_WRONG_LENGTH1}} 2 {{AND}} 32 {{VALIDATION_PASSWORD_WRONG_LENGTH2}}");
	ValidateOrDie("new password",	$newPassword, "PASSWORD",	2, 32, "{{VALIDATION_PASSWORD_WRONG_LENGTH1}} 2 {{AND}} 32 {{VALIDATION_PASSWORD_WRONG_LENGTH2}}");
	
	$passwordHashOld = GeneratePassword($oldPassword);
	$passwordHashNew = GeneratePassword($newPassword);
	
	$userID = mysql_real_escape_string($userID);
	$loggedInUserID = mysql_real_escape_string($loggedInUserID);
	$passwordHashOld = mysql_real_escape_string($passwordHashOld);
	$passwordHashNew = mysql_real_escape_string($passwordHashNew);
	
	//Check that the given user exists
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	//Check old password
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0 AND user_password_hash = '$passwordHashOld'";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID_AND_PASSWORD_HASH", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_WRONG_OLD_PASSWORD);
	}
	
	$sql = "UPDATE ".DB_TABLE_USER." SET user_password_hash = '$passwordHashNew' WHERE user_id = $userID AND user_deleted = 0";
	
	if($userID == $loggedInUserID){
		//Updating self
		ExecuteSQLOrDie("USER", "UPDATE_PASSWORD_SELF", $sql);
	}else{
		//Updating other user
		ExecuteSQLOrDie("USER", "UPDATE_PASSWORD_OTHER_USER", $sql);
	}
	$sql = "";
	return Array("result" => "SUCCESS", "resultText" => "{{SUCCESS_PASSWORD_UPDATED}}");
}

//Change user's password, admin version, does not require old password
function DB_UserChangePasswordAdmin($userID, $newPassword){
	ValidateOrDie("userID", 	$userID,	"INT", 		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	ValidateOrDie("new password",	$newPassword, "PASSWORD",	2, 32, "{{VALIDATION_PASSWORD_WRONG_LENGTH1}} 2 {{AND}} 32 {{VALIDATION_PASSWORD_WRONG_LENGTH2}}");
	
	$passwordHashNew = GeneratePassword($newPassword);
	
	$userID = mysql_real_escape_string($userID);
	$passwordHashNew = mysql_real_escape_string($passwordHashNew);
	
	//Check that the given user exists
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	$sql = "UPDATE ".DB_TABLE_USER." SET user_password_hash = '$passwordHashNew' WHERE user_id = $userID AND user_deleted = 0";
	ExecuteSQLOrDie("USER", "UPDATE_PASSWORD_OTHER_USER", $sql);
	$sql = "";
	return Array("result" => "SUCCESS", "resultText" => "{{SUCCESS_PASSWORD_UPDATED}}");
}

function DB_UserDelete($userID){
	ValidateOrDie("userID", 	$userID,	"INT", 		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	
	$userID = mysql_real_escape_string($userID);
	
	//Check that the user exists
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	$sql = "UPDATE ".DB_TABLE_USER." SET user_deleted = 1 WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SOFT_DELETE_BY_USER_ID", $sql);
	$sql = "";
	
	return array("result" => "SUCCESS", "resultText" => "{{SUCCESS_USER_DELETED}}");
}

//Makes the user an admin
function DB_UserMakeAdmin($userID){
	ValidateOrDie("userID", 	$userID,	"INT", 		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	
	$userID = mysql_real_escape_string($userID);
	
	//Check that the user exists
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	//Check that if the user is already an admin
	$sql = "SELECT user_role FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ROLE_FROM_USER_ID", $sql);
	$sql = "";
	if($info = mysql_fetch_array($data)){
		if(intval($info["user_role"]) == 2){
			ThrowErrorAndDie(ERROR_USER_ALREADY_ADMIN);
		}
	}
	
	$sql = "UPDATE ".DB_TABLE_USER." SET user_role = 2 WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "USER_MAKE_ADMIN", $sql);
	$sql = "";
	
	return array("result" => "SUCCESS", "resultText" => "{{SUCCESS_USER_MADE_ADMIN}}");
}

//Removes the user from admins
function DB_UserRemoveAdmin($userID){
	ValidateOrDie("userID", 	$userID,	"INT", 		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	
	$userID = mysql_real_escape_string($userID);
	
	//Check that the user exists
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	//Check that if the user is even an admin
	$sql = "SELECT user_role FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ROLE_FROM_USER_ID", $sql);
	$sql = "";
	if($info = mysql_fetch_array($data)){
		if(intval($info["user_role"]) != 2){
			ThrowErrorAndDie(ERROR_USER_NOT_ADMIN);
		}
	}
	
	$sql = "UPDATE ".DB_TABLE_USER." SET user_role = 1 WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "USER_REMOVE_ADMIN", $sql);
	$sql = "";
	
	return array("result" => "SUCCESS", "resultText" => "{{SUCCESS_USER_ADMIN_REMOVED}}");
}

//returns the user id, username, name, surname, language and styles for the provided user id.
//Returns an array.
function DB_GetUserPreferences($userID, $loggedInUserID){
	//Validate
	ValidateOrDie("userID", 	$userID,	"INT", 		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	
	$userID = mysql_real_escape_string($userID);
	
	$sql = "SELECT * FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	if($loggedInUserID == $userID){
		$data = ExecuteSQLOrDie("USER", "SELECT_USER_PREFS_FROM_USER_ID_SELF", $sql);
	}else{
		$data = ExecuteSQLOrDie("USER", "SELECT_USER_PREFS_FROM_USER_ID_OTHER_USER", $sql);
	}
	$sql = "";
	
	//Return user ID
	if(mysql_num_rows($data) == 1){
		$info = mysql_fetch_array($data);
		$userPrefs = Array();
		$userPrefs["user_id"] = intval($info["user_id"]);
		$userPrefs["username"] = $info["user_username"];
		$userPrefs["name"] = $info["user_name"];
		$userPrefs["surname"] = $info["user_surname"];
		if(intval($info["user_role"]) == 1){
			//This is a not a site admin
			$userPrefs["role"] = "USER";
		}else if(intval($info["user_role"]) == 2){
			//This is a site admin
			$userPrefs["role"] = "ADMIN";
		}
		$userPrefs["language"] = $info["user_language"];
		$userPrefs["style"] = $info["user_styles"];
		$userPrefs["result"] = "SUCCESS";
		$userPrefs["resultText"] = "{{SUCCESS_USER_PREFERENCES_RETRIEVED}}";
		return $userPrefs;
	}else{
		//Not logged in
		ThrowErrorAndDie(ERROR_NOT_LOGGED_IN);
	}
}

//SESSION

//Logs in the user with the given username and password.
function DB_LogInUser($username, $password, $user_ip, $userAgent){
	
	//Validate input
	ValidateOrDie("username",	$username, "STRING",	2, 32, "{{VALIDATION_USERNAME_WRONG_LENGTH1}} 2 {{AND}} 32 {{VALIDATION_USERNAME_WRONG_LENGTH2}}");
	ValidateOrDie("password",	$password, "PASSWORD",	2, 32, "{{VALIDATION_PASSWORD_WRONG_LENGTH1}} 2 {{AND}} 32 {{VALIDATION_PASSWORD_WRONG_LENGTH2}}");
	
	//generate password hash from the password
	$passwordHash = GeneratePassword($password);
	
	$username = mysql_real_escape_string($username);
	$passwordHash = mysql_real_escape_string($passwordHash);
	$user_ip = mysql_real_escape_string($user_ip);
	$userAgent = mysql_real_escape_string($userAgent);
	
	//Check if user exists in database
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_username = '$username' AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USERNAME", $sql);
	$sql = "";
	if(mysql_num_rows($data) != 1){
		ThrowWarningAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	//Check if the password matches a user
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_username = '$username' AND user_password_hash = '$passwordHash' AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_CREDENTIALS", $sql);
	$sql = "";
	
	if(mysql_num_rows($data) != 1){
		//More or fewer than 1 users match username and password
		if(mysql_num_rows($data) > 1){
			ThrowErrorAndDie(ERROR_MORE_THAN_ONE_USER_MACHES_CREDENTIALS);
		}else{
			ThrowErrorAndDie(ERROR_WRONG_USERNAME_AND_PASSWORD);
		}
	}else{
		//Exactly one user matches username and password. Log the user in and return session id.
		$info = mysql_fetch_array($data);
		$userID = intval($info["user_id"]);
		return DB_SessionInsert($userID, $user_ip, $userAgent);
	}
}

//Add a new session (when logging in), returns the sessionID, which is generated in this function
function DB_SessionInsert($userID, $user_ip, $userAgent){
	$result = Array();
	
	//Validation
	ValidateOrDie("userID", 	$userID,	"INT", 		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	ValidateOrDie("user ip", 	$user_ip,	"IP",	 	0, 0, "{{VALIDATION_IP}}");
	ValidateOrDie("user agent",	$userAgent, "STRING",	1, 255, "{{VALIDATION_USER_AGENT}}");
	
	$userID = mysql_real_escape_string($userID);
	$user_ip = mysql_real_escape_string($user_ip);
	$userAgent = mysql_real_escape_string($userAgent);
	
	//Check that the user exists
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	//Generate a session id
	$sessionID = "";
	$infiniteLoopBreaker = 0;
	do{
		if($infiniteLoopBreaker > 1000){
			ThrowErrorAndDie("Possible infinite loop in DB_SessionInsert");
		}
		$sessionID = "".mt_rand(1000000, 9999999);
		$sql = "SELECT session_user_id FROM ".DB_TABLE_SESSION." WHERE session_id = $sessionID";
		$data = ExecuteSQLOrDie("SESSION", "SELECT_USER_ID_FROM_SESSION_ID", $sql);
		$sql = "";
		$infiniteLoopBreaker++;
	}while(mysql_num_rows($data) != 0);
	
	//Validate session ID
	ValidateOrDie("generated session id",	$sessionID, "STRING",	1, 255, "{{VALIDATION_SESSION_ID}}");
	
	//Delete all sessions from this IP
	//How about no.
	//$sql = "DELETE FROM ".DB_TABLE_SESSION." WHERE session_user_ip = '$user_ip';";
	//$data = ExecuteSQLOrDie("SESSION", "DELETE_SESSION_BY_USER_IP", $sql);
	//$sql = "";
	
	//Insert this new session
	$sql = "INSERT INTO ".DB_TABLE_SESSION."
	(session_id, session_user_id, session_user_ip, session_datetime, session_user_agent, session_last_access)
	VALUES
	('$sessionID', $userID, '$user_ip', Now(), '$userAgent', Now());";
	$data = ExecuteSQLOrDie("SESSION", "INSERT_NEW_SESSION", $sql);
	$sql = "";
	
	//Get data about the now logged in user
	$sql = "SELECT user_id, user_name, user_surname, user_username, user_role, user_language, user_styles FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_INFO_FROM_USER_ID", $sql);
	$sql = "";
	if($info = mysql_fetch_array($data)){
		$result["user_id"] = $userID;
		$result["session_id"] = $sessionID;
		$result["name"] = $info["user_name"];
		$result["surname"] = $info["user_surname"];
		$result["username"] = $info["user_username"];
		$result["language"] = $info["user_language"];
		$result["style"] = $info["user_styles"];
		$result["role"] = $info["user_role"];
	}
	
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_USER_LOGGED_IN}}";
	return $result;
}

//Log out function, currently only redirects to SessionDelete. TODO: prune session data.
function DB_LogOut($sessionID){
	$sessionID = mysql_real_escape_string($sessionID);
	
	return DB_SessionDelete($sessionID);
}

//Deletes the session with the session id, effectivelly logging out the user.
function DB_SessionDelete($sessionID){
	ValidateOrDie("generated session id",	$sessionID, "STRING",	1, 255, "{{VALIDATION_SESSION_ID}}");
	
	$sessionID = mysql_real_escape_string($sessionID);
	
	//Delete the session
	$sql = "DELETE FROM ".DB_TABLE_SESSION." WHERE session_id = '$sessionID';";
	$data = ExecuteSQLOrDie("SESSION", "DELETE_SESSION_BY_SESSION_ID", $sql);
	$sql = "";
	
	return Array("result" => "SUCCESS", "resultText" => "{{SUCCESS_LOGGED_OUT}}");
}

//Retutns user ID from Session ID, if exists
function DB_UserIDFromSessionID($sessionID, $user_ip, $userAgent){
	//Validation
	ValidateOrDie("sessionID", 	$sessionID,	"STRING",	1, 255, "{{VALIDATION_SESSION_ID}}");
	ValidateOrDie("user ip", 	$user_ip,	"IP",	 	0, 0, "{{VALIDATION_IP}}");
	ValidateOrDie("user agent",	$userAgent, "STRING",	1, 255, "{{VALIDATION_USER_AGENT}}");
	
	$sessionID = mysql_real_escape_string($sessionID);
	$user_ip = mysql_real_escape_string($user_ip);
	$userAgent = mysql_real_escape_string($userAgent);
	
	//Select user_id based on the session id (the logged in user id)
	$sql = "SELECT session_user_id FROM ".DB_TABLE_SESSION." WHERE session_id = '$sessionID';";
	$data = ExecuteSQLOrDie("SESSION", "SELECT_USER_ID_BY_SESSION_ID", $sql);
	$sql = "";
	
	//Return user ID
	if(mysql_num_rows($data) == 1){
		$info = mysql_fetch_array($data);
		return intval($info["session_user_id"]);
	}else{
		//Not logged in
		return false;
	}
}

//Checks if the person with the sessionID is logged in.
function DB_IsLoggedIn($sessionID, $user_ip, $userAgent){
	//Validation
	ValidateOrDie("sessionID", 	$sessionID,	"STRING",	1, 255, "{{VALIDATION_SESSION_ID}}");
	ValidateOrDie("user ip", 	$user_ip,	"IP",	 	0, 0, "{{VALIDATION_IP}}");
	ValidateOrDie("user agent",	$userAgent, "STRING",	1, 255, "{{VALIDATION_USER_AGENT}}");
	
	$sessionID = mysql_real_escape_string($sessionID);
	$user_ip = mysql_real_escape_string($user_ip);
	$userAgent = mysql_real_escape_string($userAgent);
	
	//Select user_id based on the session id (the logged in user id)
	$sql = "SELECT s.session_user_id, u.user_username, u.user_name, u.user_surname, u.user_role, u.user_language, u.user_styles FROM ".DB_TABLE_SESSION." s, ".DB_TABLE_USER." u WHERE s.session_id = '$sessionID' AND s.session_user_id = u.user_id AND user_deleted = 0;";
	$data = ExecuteSQLOrDie("SESSION", "SELECT_USER_ID_BY_SESSION_ID", $sql);
	$sql = "";
	
	//Return user ID
	if(mysql_num_rows($data) == 1){
		$info = mysql_fetch_array($data);
		$result = Array();
		$result["user_id"] = intval($info["session_user_id"]);
		$result["name"] = $info["user_name"];
		$result["surname"] = $info["user_surname"];
		$result["username"] = $info["user_username"];
		$result["role"] = $info["user_role"];
		$result["language"] = $info["user_language"];
		$result["style"] = $info["user_styles"];
		$result["result"] = "SUCCESS";
		$result["resultText"] = "{{SUCCESS_USER_IS_LOGGED_IN}}";
		return $result;
	}else{
		//Not logged in
		throwErrorAndDie(ERROR_SESSION_USER_NOT_LOGGED_IN);
	}
}

//Retutns whether or not the given user is a teacher in any class. This function DOES NOT check whether the user is an admin.
function DB_UserIsTeacherInAnyClass($userID){
	//Validation
	ValidateOrDie("userID", 	$userID,	"INT", 		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	
	$userID = mysql_real_escape_string($userID);
	
	//Check that the user exists
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	//Check if the user is a teacher in any class
	$sql = "SELECT student_role FROM ".DB_TABLE_STUDENT." WHERE student_user_id = $userID AND student_role = '2'";
	$data = ExecuteSQLOrDie("STUDENT", "SELECT_STUDENT_ROLE_FROM_USER_ID", $sql);
	$sql = "";
	if($info = mysql_fetch_array($data)){
		return Array("result" => "SUCCESS", "resultText" => "{{SUCCESS_STUDENT_ROLE_ANY_CLASS}}", "role" => "TEACHER");
	}
	
	return Array("result" => "SUCCESS", "resultText" => "{{SUCCESS_STUDENT_ROLE_ANY_CLASS}}", "role" => "NOT TEACHER");
	
}

//Checks if the given user is a teacher / student / unenrolled in the given class.
function DB_GetUserRoleInClass($userID, $classID){
	//Validation
	ValidateOrDie("userID", 	$userID,	"INT", 		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	ValidateOrDie("classID", 	$classID,	"INT", 		1, PHP_INT_MAX, "{{VALIDATION_CLASS_ID}}");
	
	$userID = mysql_real_escape_string($userID);
	$classID = mysql_real_escape_string($classID);
	
	//Check that the user exists
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	//Check that the class exists
	$sql = "SELECT class_id FROM ".DB_TABLE_CLASS." WHERE class_id = $classID";
	$data = ExecuteSQLOrDie("CLASS", "SELECT_CLASS_ID_FROM_CLASS_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_CLASS_NOT_IN_DATABASE);
	}
	
	//Check if the user is a teacher in the given class
	$sql = "SELECT student_role FROM ".DB_TABLE_STUDENT." WHERE student_class_id = $classID AND student_user_id = $userID";
	$data = ExecuteSQLOrDie("STUDENT", "SELECT_STUDNET_ROLE_FROM_STUDENT_ID_AND_CLASS_ID", $sql);
	$sql = "";
	if($info = mysql_fetch_array($data)){
		return Array("result" => "SUCCESS", "resultText" => "{{SUCCESS_STUDENT_ROLE_ONE_CLASS}}", "role" => $info["student_role"]);
	}else{
		ThrowErrorAndDie(ERROR_STUDENT_NOT_ENROLLED);
	}
}

//CLASS

//Add a new class
function DB_ClassInsert($userID, $userIP, $className, $classDescription, $classGlyph, $classPublic){
	
	ValidateOrDie("userID", 	$userID,			"INT",		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	ValidateOrDie("user ip", 	$userIP,			"IP",	 	0, 0, "{{VALIDATION_IP}}");
	ValidateOrDie("Class Name",	$className, 		"STRING",	1, 30, "{{VALIDATION_CLASS_NAME}}");
	ValidateOrDie("Class Desc",	$classDescription, 	"STRING",	0, 65535, "{{VALIDATION_CLASS_DESCRIPTION}}");
	ValidateOrDie("Class Glyph",$classGlyph, 		"STRING",	1, 255, "{{VALIDATION_CLASS_GLYPH}}");
	ValidateOrDie("Class Public",$classPublic, 		"INT",		0, 1, "{{VALIDATION_CLASS_PUBLIC}}");
	
	$userID = mysql_real_escape_string($userID);
	$userIP = mysql_real_escape_string($userIP);
	$className = mysql_real_escape_string($className);
	$classDescription = mysql_real_escape_string($classDescription);
	$classGlyph = mysql_real_escape_string($classGlyph);
	$classPublic = mysql_real_escape_string($classPublic);
	
	
	//Select user_id based on the session id (the logged in user id)
	$sql = "INSERT INTO ".DB_TABLE_CLASS."
	(class_id, class_created_user_id, class_created_user_ip, class_created_datetime, class_name, class_description, class_glyph, class_is_public, class_deleted)
	VALUES
	(null, $userID, '$userIP', Now(), '$className', '$classDescription', '$classGlyph', $classPublic, 0);
	";
	$data = ExecuteSQLOrDie("CLASS", "INSERT_NEW_CLASS", $sql);
	$sql = "";
	
	return Array("result" => "SUCCESS", "resultText" => "{{SUCCESS_CLASS_CREATED}}");
}

//Update an existing class
function DB_ClassUpdate($classID, $className, $classDescription, $classGlyph){
	
	ValidateOrDie("classID", 	$classID,			"INT",		1, PHP_INT_MAX, "{{VALIDATION_CLASS_ID}}");
	
	$classID = mysql_real_escape_string($classID);
	
	//Check that the class exists
	$sql = "SELECT class_id FROM ".DB_TABLE_CLASS." WHERE class_id = $classID AND class_deleted = 0";
	$data = ExecuteSQLOrDie("CLASS", "SELECT_CLASS_ID_FROM_CLASS_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_CLASS_NOT_IN_DATABASE);
	}
	
	$sql = "UPDATE ".DB_TABLE_CLASS." SET ";
	$updateStatements = Array();
	
	$statement = ValidateAndGenerateUpdateStatement("class_name", "VARCHAR",	"class name", 	$className, 	"STRING",	1, 255);
	if($statement){$updateStatements[] = $statement;};
	
	$statement = ValidateAndGenerateUpdateStatement("class_description", "VARCHAR",	"class description", 	$classDescription, 	"STRING",	0, 65535);
	if($statement){$updateStatements[] = $statement;};
	
	$statement = ValidateAndGenerateUpdateStatement("class_glyph", "VARCHAR",	"class glyph", 	$classGlyph, 	"STRING",	1, 255);
	if($statement){$updateStatements[] = $statement;};
	
	$sql .= implode(", ",$updateStatements);
	$sql .= " WHERE class_id = $classID";
	
	$data = ExecuteSQLOrDie("CLASS", "UPDATE_CLASS_INFO", $sql);
	$sql = "";
	
	return true;
}

//Returns the ID, Name and description for all classes.
function DB_ClassGetAll(){
	$result = Array();
	

	$sql = "SELECT * FROM ".DB_TABLE_CLASS." WHERE class_deleted = 0";
	$data = ExecuteSQLOrDie("CLASS", "SELECT_INFO_FOR_ALL_CLASSES", $sql);
	$sql = "";
	while($info = mysql_fetch_array($data)){
		$row = Array();	//row row row your boat...
		$row["id"] = intval($info["class_id"]);
		$row["name"] = $info["class_name"];
		$row["description"] = $info["class_description"];
		$row["glyph"] = $info["class_glyph"];
		$row["is_public"] = intval($info["class_is_public"]);
		$result["classlist"][] = $row;
	}
	
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_CLASS_LIST_RETRIEVED}}";
	return $result;
}

//Returns the ID, Name and description for the given class.
function DB_ClassGetOne($classID){
	$result = Array();
	
	//Validate input
	ValidateOrDie("classID", 	$classID,			"INT",		1, PHP_INT_MAX, "{{VALIDATION_CLASS_ID}}");
	
	$classID = mysql_real_escape_string($classID);
	
	//Check that the class exists
	$sql = "SELECT * FROM ".DB_TABLE_CLASS." WHERE class_id = $classID AND class_deleted = 0";
	$data = ExecuteSQLOrDie("CLASS", "SELECT_INFO_FOR_CLASS_BY_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_CLASS_NOT_IN_DATABASE);
	}
	
	//Get class data
	$sql = "SELECT * FROM ".DB_TABLE_CLASS." WHERE clasS_id = $classID AND class_deleted = 0";
	$data = ExecuteSQLOrDie("CLASS", "SELECT_INFO_FOR_CLASS_BY_ID", $sql);
	$sql = "";
	if($info = mysql_fetch_array($data)){
		$result["id"] = intval($info["class_id"]);
		$result["name"] = $info["class_name"];
		$result["description"] = $info["class_description"];
		$result["glyph"] = $info["class_glyph"];
		$result["is_public"] = intval($info["class_is_public"]);
	}
	
	//Get list of teachers
	$result["teacherlist"] = Array();
	$sql = "SELECT u.user_id, u.user_username, u.user_name, u.user_surname FROM ".DB_TABLE_STUDENT." s, ".DB_TABLE_USER." u WHERE s.student_class_id = $classID AND s.student_role = 2 AND s.student_user_id = u.user_id AND u.user_deleted = 0";
	$data = ExecuteSQLOrDie("STUDENT", "GET_TEACHER_LIST", $sql);
	$sql = "";
	while($info = mysql_fetch_array($data)){
		$row = Array();	//row row row your boat...
		$row["user_id"] = intval($info["user_id"]);
		$row["name"] = $info["user_name"];
		$row["surname"] = $info["user_surname"];
		$row["username"] = $info["user_username"];
		$result["teacherlist"][] = $row;
	}
	
	//Get list of enrollees
	$result["enrolleelist"] = Array();
	$sql = "SELECT u.user_id, u.user_username, u.user_name, u.user_surname FROM ".DB_TABLE_STUDENT." s, ".DB_TABLE_USER." u WHERE s.student_class_id = $classID AND s.student_user_id = u.user_id AND u.user_deleted = 0";
	$data = ExecuteSQLOrDie("STUDENT", "GET_ENROLEE_LIST", $sql);
	$sql = "";
	while($info = mysql_fetch_array($data)){
		$row = Array();	//row row row your boat...
		$row["user_id"] = intval($info["user_id"]);
		$row["name"] = $info["user_name"];
		$row["surname"] = $info["user_surname"];
		$row["username"] = $info["user_username"];
		$result["enrolleelist"][] = $row;
	}
	
	$row["result"] = "SUCCESS";
	$row["resultText"] = "{{SUCCESS_CLASS_INFO_RETRIEVED}}";
	
	return $result;
}

//Returns the ID, Name and description for all classes + info if the user belongs to them.
function DB_ClassGetForUser($userID, $loggedInUserID){
	$result = Array();
	$result["classlist"] = Array();
	
	ValidateOrDie("userID", $userID, "INT", 1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	
	$userID = mysql_real_escape_string($userID);
	$loggedInUserID = mysql_real_escape_string($loggedInUserID);
	
	$sql = "SELECT * FROM (SELECT * FROM ".DB_TABLE_CLASS." WHERE class_deleted = 0) c LEFT JOIN (SELECT * FROM ".DB_TABLE_STUDENT." WHERE student_user_id = $userID) s ON (c.class_id = s.student_class_id)";
	if($userID == $loggedInUserID){
		$data = ExecuteSQLOrDie("STUDENT", "SELECT_CLASS_INFO_FOR_SELF", $sql);
	}else{
		$data = ExecuteSQLOrDie("STUDENT", "SELECT_CLASS_INFO_FOR_OTHER_USER", $sql);
	}
	$sql = "";
	while($info = mysql_fetch_array($data)){
		$row = Array();	//row row row your boat...
		$row["id"] = intval($info["class_id"]);
		$row["name"] = $info["class_name"];
		$row["description"] = $info["class_description"];
		$row["glyph"] = $info["class_glyph"];
		$row["is_public"] = intval($info["class_is_public"]);
		if($info["student_role"]){
			$row["enrolled"] = intval($info["student_role"]);
		}else{
			$row["enrolled"] = 0;
		}
		$result["classlist"][] = $row;
	}
	
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_CLASS_LIST_RETRIEVED}}";
	return $result;
}

function DB_ClassDelete($classID){
	
	//Validate inputs
	ValidateOrDie("classID", 	$classID,			"INT",		1, PHP_INT_MAX, "{{VALIDATION_CLASS_ID}}");
	
	$classID = mysql_real_escape_string($classID);
	
	//Check that the class exists
	$sql = "SELECT * FROM ".DB_TABLE_CLASS." WHERE class_id = $classID AND class_deleted = 0";
	$data = ExecuteSQLOrDie("CLASS", "SELECT_INFO_FOR_CLASS_BY_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_CLASS_NOT_IN_DATABASE);
	}
	
	//Delete all instances in the class
	$InstanceList = DB_GetInstancesForClass($classID);
	if(isset($InstanceList["instanceslist"])){
		foreach($InstanceList["instanceslist"] as $index => $Instance){
			DB_InstanceDelete(intval($Instance["id"]));
		}
	}
	
	//soft delete class
	$sql = "UPDATE ".DB_TABLE_CLASS." SET class_deleted = 1 WHERE class_id = $classID AND class_deleted = 0";
	$data = ExecuteSQLOrDie("CLASS", "SOFT_DELETE_CLASS_BY_CLASS_ID", $sql);
	$sql = "";
	
	return Array("result" => "SUCCESS", "resultText" => "{{SUCCESS_CLASS_DELETED}}");
}

//STUDENT

//Enrols the user in the class, assigns them the role (student/teacher)
//Because I cannot spell enrol properly.
function DB_StudentEnroll($classID, $userID, $loggedInUserID, $role){
	$result = Array();
	
	//Validation
	ValidateOrDie("classID", 	$classID,	"INT",	1, PHP_INT_MAX, "{{VALIDATION_CLASS_ID}}");
	ValidateOrDie("userID", 	$userID,	"INT",	1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	ValidateOrDie("role", 		$role,		"INT",	AUTH_STUDENT, AUTH_TEACHER, "{{VALIDATION_ROLE}}");
	
	$classID = mysql_real_escape_string($classID);
	$userID = mysql_real_escape_string($userID);
	$loggedInUserID = mysql_real_escape_string($loggedInUserID);
	$role = mysql_real_escape_string($role);
	
	//Check that the class exists
	$sql = "SELECT class_id FROM ".DB_TABLE_CLASS." WHERE class_id = $classID AND class_deleted = 0";
	$data = ExecuteSQLOrDie("CLASS", "SELECT_CLASS_ID_FROM_CLASS_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_CLASS_NOT_IN_DATABASE);
	}
	
	//Check that the user exists
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	//Check if user is already enrolled in this class
	$sql = "SELECT student_id FROM ".DB_TABLE_STUDENT." WHERE student_class_id = $classID AND student_user_id = $userID";
	$data = ExecuteSQLOrDie("STUDENT", "SELECT_STUDENT_FROM_CLASS_ID_AND_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) > 0){
		ThrowErrorAndDie(ERROR_STUDENT_ALREADY_ENROLLED);
	}
	
	//Insert
	$sql = "INSERT INTO ".DB_TABLE_STUDENT."
	(student_id, student_user_id, student_class_id, student_enroll_datetime, student_role)
	VALUES
	(null, $userID, $classID, Now(), $role);";
	if($userID == $loggedInUserID){
		//Endrolling self
		$data = ExecuteSQLOrDie("STUDENT", "ENROLL_STUDENT_INTO_CLASS_SELF", $sql);
	}else{
		//Endrolling other user
		$data = ExecuteSQLOrDie("STUDENT", "ENROLL_STUDENT_INTO_CLASS_OTHER_USER", $sql);
	}
	$sql = "";
	
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_ENROLLMENT}}";
	return $result;
}

//Because I cannot spell enrol properly.
function DB_StudentUnenroll($classID, $userID, $loggedInUserID){
	$result = Array();
	
	//Validate
	ValidateOrDie("classID", 	$classID,	"INT",	1, PHP_INT_MAX, "{{VALIDATION_CLASS_ID}}");
	ValidateOrDie("userID", 	$userID,	"INT",	1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	
	$classID = mysql_real_escape_string($classID);
	$userID = mysql_real_escape_string($userID);
	
	//Delete user from class.
	$sql = "DELETE FROM ".DB_TABLE_STUDENT." WHERE student_class_id = $classID AND student_user_id = $userID";
	if($userID == $loggedInUserID){
		$data = ExecuteSQLOrDie("STUDENT", "DELETE_STUDENT_BY_CLASS_ID_AND_USER_ID_SELF", $sql);
	}else{
		$data = ExecuteSQLOrDie("STUDENT", "DELETE_STUDENT_BY_CLASS_ID_AND_USER_ID_OTHER_USER", $sql);
	}
	$sql = "";
	
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_UNENROLLMENT}}";
	return $result;
}

function DB_StudentUpdate(){
	return "Not implemented";
}

//Makes the student a teacher
function DB_StudentMakeTeacher($userID, $classID){
	ValidateOrDie("userID", 	$userID,	"INT", 		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	ValidateOrDie("classID", 	$classID,	"INT", 		1, PHP_INT_MAX, "{{VALIDATION_CLASS_ID}}");
	
	$userID = mysql_real_escape_string($userID);
	$classID = mysql_real_escape_string($classID);
	
	//Check that the class exists
	$sql = "SELECT class_id FROM ".DB_TABLE_CLASS." WHERE class_id = $classID AND class_deleted = 0";
	$data = ExecuteSQLOrDie("CLASS", "SELECT_CLASS_ID_FROM_CLASS_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_CLASS_NOT_IN_DATABASE);
	}
	
	//Check that the user exists
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	//Check if user is already enrolled in this class
	$sql = "SELECT student_id FROM ".DB_TABLE_STUDENT." WHERE student_class_id = $classID AND student_user_id = $userID";  
	$data = ExecuteSQLOrDie("STUDENT", "SELECT_STUDENT_FROM_CLASS_ID_AND_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_STUDENT_NOT_ENROLLED);
	}
	
	//Check if the user is already a teacher
	$sql = "SELECT student_role FROM ".DB_TABLE_STUDENT." WHERE student_user_id = $userID AND student_class_id = $classID";
	$data = ExecuteSQLOrDie("STUDENT", "SELECT_STUDENT_ROLE_FROM_USER_ID_AND_CLASS_ID", $sql);
	$sql = "";
	if($info = mysql_fetch_array($data)){
		if(intval($info["student_role"]) == 2){
			ThrowErrorAndDie(ERROR_STUDENT_ALREADY_TEACHER);
		}
	}
	
	$sql = "UPDATE ".DB_TABLE_STUDENT." SET student_role = 2 WHERE student_class_id = $classID AND student_user_id = $userID";
	$data = ExecuteSQLOrDie("STUDENT", "STUDENT_MAKE_TEACHER", $sql);
	$sql = "";
	
	return array("result" => "SUCCESS", "resultText" => "{{SUCCESS_STUDENT_MADE_TEACHER}}");
}

//Makes the student no longer be a teacher
function DB_StudentRemoveTeacher($userID, $classID){
	ValidateOrDie("userID", 	$userID,	"INT", 		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	ValidateOrDie("classID", 	$classID,	"INT", 		1, PHP_INT_MAX, "{{VALIDATION_CLASS_ID}}");
	
	$userID = mysql_real_escape_string($userID);
	$classID = mysql_real_escape_string($classID);
	
	//Check that the class exists
	$sql = "SELECT class_id FROM ".DB_TABLE_CLASS." WHERE class_id = $classID AND class_deleted = 0";
	$data = ExecuteSQLOrDie("CLASS", "SELECT_CLASS_ID_FROM_CLASS_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_CLASS_NOT_IN_DATABASE);
	}
	
	//Check that the user exists
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	//Check if user is already enrolled in this class
	$sql = "SELECT student_id FROM ".DB_TABLE_STUDENT." WHERE student_class_id = $classID AND student_user_id = $userID";
	$data = ExecuteSQLOrDie("STUDENT", "SELECT_STUDENT_FROM_CLASS_ID_AND_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_STUDENT_NOT_ENROLLED);
	}
	
	//Check if the user is a teacher
	$sql = "SELECT student_role FROM ".DB_TABLE_STUDENT." WHERE student_user_id = $userID AND student_class_id = $classID";
	$data = ExecuteSQLOrDie("STUDENT", "SELECT_STUDENT_ROLE_FROM_USER_ID_AND_CLASS_ID", $sql);
	$sql = "";
	if($info = mysql_fetch_array($data)){
		if(intval($info["student_role"]) == 1){
			ThrowErrorAndDie(ERROR_STUDENT_NOT_TEACHER);
		}
	}
	
	$sql = "UPDATE ".DB_TABLE_STUDENT." SET student_role = 1 WHERE student_class_id = $classID AND student_user_id = $userID";
	$data = ExecuteSQLOrDie("STUDENT", "STUDENT_REMOVE_TEACHER", $sql);
	$sql = "";
	
	return array("result" => "SUCCESS", "resultText" => "{{SUCCESS_TEACHER_TEMOVED}}");
}

//GAME
function DB_GameInsert($userID, $userIP, $name, $fileLocation, $description, $signatureVersion, $author, $dateOfBuild, $version, $license, $teacherInstructions, $comment, $stylesAvailable, $languages, $dictionary, $variables, $difficulty, $timeLimit, $levels, $retriesAvailable, $constants){
	
	//Validation
	ValidateOrDie("userID", 			$userID,			"INT",		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}"); 
	ValidateOrDie("userIP", 			$userIP,			"IP",		0, 0, "{{VALIDATION_IP}}");
	ValidateOrDie("name", 				$name,				"STRING",	1, 255, "{{VALIDATION_GAME_NAME}}");
	ValidateOrDie("fileLocation", 		$fileLocation,		"STRING",	1, 255, "{{VALIDATION_GAME_FILE_LOCATION}}");
	ValidateOrDie("description", 		$description,		"STRING",	1, 65535, "{{VALIDATION_GAME_DESCRIPTION}}");
	ValidateOrDie("signatureVersion", 	$signatureVersion,	"STRING",	1, 45, "{{VALIDATION_GAME_SIGNATURE_VERSION}}");
	ValidateOrDie("author", 			$author,			"STRING",	1, 65535, "{{VALIDATION_GAME_AUTHOR}}");
	ValidateOrDie("dateOfBuild", 		$dateOfBuild,		"DATETIME",	1, 255, "{{VALIDATION_GAME_DOB}}");
	ValidateOrDie("version", 			$version,			"STRING",	1, 45, "{{VALIDATION_GAME_VERSION}}");
	ValidateOrDie("license", 			$license,			"STRING",	1, 65535, "{{VALIDATION_GAME_LICENSE}}");
	ValidateOrDie("teacherInstructions",$teacherInstructions,		"STRING",	1, 65535, "{{VALIDATION_GAME_TEACHER_INSTRUCTIONS}}");
	ValidateOrDie("comment", 			$comment,			"STRING",	1, 65535, "{{VALIDATION_GAME_COMMENT}}");
	ValidateOrDie("stylesAvailable",	$stylesAvailable,	"STRING",	1, 65535, "{{VALIDATION_GAME_STYLES}}");
	ValidateOrDie("languages", 			$languages,			"STRING",	1, 65535, "{{VALIDATION_GAME_LANGUAGES}}");
	ValidateOrDie("dictionary", 		$dictionary,		"ARRAY",	1, 65535, "{{VALIDATION_GAME_DICT}}");
	ValidateOrDie("variables", 			$variables,			"ARRAY",	0, 0, "{{VALIDATION_GAME_VARIABLES}}");
	ValidateOrDie("difficulty", 		$difficulty,		"ARRAY",	1, 65535, "{{VALIDATION_GAME_DIFFICULTY}}");
	ValidateOrDie("timeLimit", 			$timeLimit,			"ARRAY",	1, 65535, "{{VALIDATION_GAME_TIME_LIMIT}}");
	ValidateOrDie("levels", 			$levels,			"ARRAY",	1, 65535, "{{VALIDATION_GAME_LEVELS}}");
	ValidateOrDie("retriesAvailable", 	$retriesAvailable,	"ARRAY",	1, 65535, "{{VALIDATION_GAME_RETRIES_AVAILABLE}}");
	ValidateOrDie("constants", 			$constants,			"ARRAY",	0, 0, "{{VALIDATION_GAME_CONSTANTS}}");
	
	//Convert to JSON
	$dictionary = json_encode($dictionary);
	$variables = json_encode($variables);
	$difficulty = json_encode($difficulty);
	$timeLimit = json_encode($timeLimit);
	$levels = json_encode($levels);
	$retriesAvailable = json_encode($retriesAvailable);
	
	$userID = mysql_real_escape_string($userID);
	$userIP = mysql_real_escape_string($userIP);
	$name = mysql_real_escape_string($name);
	$fileLocation = mysql_real_escape_string($fileLocation);
	$description = mysql_real_escape_string($description);
	$signatureVersion = mysql_real_escape_string($signatureVersion);
	$author = mysql_real_escape_string($author);
	$dateOfBuild = mysql_real_escape_string($dateOfBuild);
	$version = mysql_real_escape_string($version);
	$license = mysql_real_escape_string($license);
	$teacherInstructions = mysql_real_escape_string($teacherInstructions);
	$comment = mysql_real_escape_string($comment);
	$stylesAvailable = mysql_real_escape_string($stylesAvailable);
	$languages = mysql_real_escape_string($languages);
	$dictionary = mysql_real_escape_string($dictionary);
	$variables = mysql_real_escape_string($variables);
	$difficulty = mysql_real_escape_string($difficulty);
	$timeLimit = mysql_real_escape_string($timeLimit);
	$levels = mysql_real_escape_string($levels);
	$retriesAvailable = mysql_real_escape_string($retriesAvailable);
	
	print "<p>".$dictionary;
	print "<p>".$variables;
	print "<p>".$difficulty;
	print "<p>".$timeLimit;
	print "<p>".$levels;
	print "<p>".$retriesAvailable;
	
	//Check that the user exists
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	
	
	$gameID = 1;
	$sql = "SELECT MAX(game_id) AS maxid FROM ".DB_TABLE_GAME." ";
	$data = ExecuteSQLOrDie("GAME", "SELECT_MAX_GAME_ID", $sql);
	$sql = "";
	if($info = mysql_fetch_array($data)){
		$gameID = intval($info["maxid"]) +1;
	}
	
	$sql = "INSERT INTO ".DB_TABLE_GAME."
	(game_id, game_upload_user_id, game_upload_user_ip, game_upload_datetime, game_name, game_file_location, game_description, game_signature_version, game_author, game_date_of_build, game_version, game_license, game_teacher_instructions, game_comment, game_styles_available, game_languages, game_dictionary, game_variables, game_difficulty, game_time_limit, game_levels, game_retries_available, game_deleted)
	VALUES
	($gameID, $userID, '$userIP', Now(), '$name', '$fileLocation', '$description', '$signatureVersion', '$author', '$dateOfBuild', '$version', '$license', '$teacherInstructions', '$comment', '$stylesAvailable', '$languages', '$dictionary', '$variables', '$difficulty', '$timeLimit', '$levels', '$retriesAvailable', 0);";
	print $sql;
	$data = ExecuteSQLOrDie("GAME", "INSERT_GAME", $sql);
	$sql = "";
	
	foreach($constants as $i => $const){
		DB_AddConstant($gameID, $const["name"], $const["value"]);
	}
	
	return true;
}

function DB_GetGameDefaults($gameID){
	$result = Array();
	
	//Validation
	ValidateOrDie("gameID", 	$gameID,	"INT",		1, PHP_INT_MAX, "{{VALIDATION_GAME_ID}}"); 
	
	$gameID = mysql_real_escape_string($gameID);
	
	//Check that the game exists
	$sql = "SELECT game_id FROM ".DB_TABLE_GAME." WHERE game_id = $gameID AND game_deleted = 0";
	$data = ExecuteSQLOrDie("GAME", "SELECT_GAME_ID_FROM_GAME_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_GAME_NOT_IN_DATABASE);
	}
	
	$sql = "SELECT * FROM ".DB_TABLE_VARIABLE." WHERE variable_game_id = ".$gameID;
	$data = ExecuteSQLOrDie("VARIABLE", "SELECT_GAME_DEFAULTS", $sql);
	$sql = "";
	while($info = mysql_fetch_array($data)){
		$row = Array();
		
		$row["type"] = $info["variable_type"];
		$row["value"] = $info["variable_value"];
		
		$result["variablelist"][$info["variable_name"]] = $row;
	}
	
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_GAME_DEFAULTS_LOADED}}";
	
	return $result;
}

function DB_GetAllGames(){
	
	$result = Array();
	
	$sql = "SELECT g.*, COUNT(i.instance_id) AS num_instances FROM (SELECT * FROM ".DB_TABLE_GAME." WHERE game_deleted = 0) g LEFT JOIN (SELECT * FROM ".DB_TABLE_INSTANCE." WHERE instance_deleted = 0) i ON g.game_id = i.instance_game_id GROUP BY g.game_id";
	$data = ExecuteSQLOrDie("GAME", "SELECT_ALL_GAMES", $sql);
	$sql = "";
	while($info = mysql_fetch_array($data)){
		$row = Array();	//row row row your boat...
		$row["id"] = $info["game_id"];
		$row["name"] = $info["game_name"];
		$row["description"] = $info["game_description"];
		$row["author"] = $info["game_author"];
		$row["version"] = $info["game_version"];
		$row["styles_available"] = $info["game_styles_available"];
		$row["languages"] = $info["game_languages"];
		$row["difficulty"] = $info["game_difficulty"];
		$row["time_limit"] = $info["game_time_limit"];
		$row["levels"] = $info["game_levels"];
		$row["retries_available"] = $info["game_retries_available"];
		$row["num_instances"] = $info["num_instances"];
		$result["gamelist"][] = $row;
	}
	
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_ALL_GAMES_RETRIEVED}}";
	
	return $result;
}



function DB_GetInfoForOneGame($gameID){
	$result = Array();
	
	//Validation
	ValidateOrDie("gameID", 	$gameID,	"INT",		1, PHP_INT_MAX, "{{VALIDATION_GAME_ID}}"); 
	
	$gameID = mysql_real_escape_string($gameID);
	
	//Check that the game exists
	$sql = "SELECT game_id FROM ".DB_TABLE_GAME." WHERE game_id = $gameID AND game_deleted = 0";
	$data = ExecuteSQLOrDie("GAME", "SELECT_GAME_ID_FROM_GAME_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_GAME_NOT_IN_DATABASE);
	}
	
	//Select game data
	$sql = "SELECT * FROM ".DB_TABLE_GAME." WHERE game_id = $gameID AND game_deleted = 0";
	$data = ExecuteSQLOrDie("GAME", "SELECT_INFO_FOR_GAME_BY_GAME_ID", $sql);
	$sql = "";
	if($info = mysql_fetch_array($data)){
		$result["id"] = $info["game_id"];
		$result["datetime"] = $info["game_upload_datetime"];
		$result["name"] = $info["game_name"];
		$result["file_location"] = $info["game_file_location"];
		$result["description"] = $info["game_description"];
		$result["signature_version"] = $info["game_signature_version"];
		$result["author"] = $info["game_author"];
		$result["date_of_build"] = $info["game_date_of_build"];
		$result["version"] = $info["game_version"];
		$result["license"] = $info["game_license"];
		$result["teacher_instructions"] = $info["game_teacher_instructions"];
		$result["comment"] = $info["game_comment"];
		$result["styles_available"] = $info["game_styles_available"];
		$result["languages"] = $info["game_languages"];
		$result["dictionary"] = json_decode($info["game_dictionary"]);
		$result["variables"] = json_decode($info["game_variables"]);
		$result["default_difficulty"] = json_decode($info["game_difficulty"]);
		$result["default_time_limit"] = json_decode($info["game_time_limit"]);
		$result["default_levels"] = json_decode($info["game_levels"]);
		$result["default_retries_available"] = json_decode($info["game_retries_available"]);
	}
	
	//Select constants
	$sql = "SELECT * FROM ".DB_TABLE_CONSTANT." WHERE constant_game_id = $gameID";
	$data = ExecuteSQLOrDie("CONSTANT", "SELECT_CONSTANTS_FOR_GAME", $sql);
	$sql = "";
	while($info = mysql_fetch_array($data)){
		$result["constants"][$info["constant_name"]] = $info["constant_value"];
	}
	
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_GAME_DATA_RETRIEVED}}";
	
	return $result;
}

function DB_GameUpdate(){
	return "Not implemented";
}

//Soft delete a game. To delete a game it must exist, not be deleted and not have any instances
function DB_GameDelete($gameID){
	$result = Array();
	
	//Validation
	ValidateOrDie("gameID", 	$gameID,	"INT",		1, PHP_INT_MAX, "{{VALIDATION_GAME_ID}}"); 
	
	$gameID = mysql_real_escape_string($gameID);
	
	//Check that the game exists
	$sql = "SELECT game_id FROM ".DB_TABLE_GAME." WHERE game_id = $gameID AND game_deleted = 0";
	$data = ExecuteSQLOrDie("GAME", "SELECT_GAME_ID_FROM_GAME_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_GAME_NOT_IN_DATABASE);
	}
	
	//Check that there are no instances for this game
	$sql = "SELECT * FROM ".DB_TABLE_INSTANCE." WHERE instance_game_id = $gameID AND instance_deleted = 0";
	$data = ExecuteSQLOrDie("INSTANCE", "SELECT_INSTANCES_FOR_GAME", $sql);
	$sql = "";
	if(mysql_num_rows($data) > 0){
		ThrowErrorAndDie(ERROR_CANNOT_DELETE_GAME_HAS_INSTANCES);
	}
	
	//Soft delete game
	$sql = "UPDATE ".DB_TABLE_GAME." SET game_deleted = 1 WHERE game_id = $gameID AND game_deleted = 0";
	$data = ExecuteSQLOrDie("GAME", "SOFT_DELETE_GAME_BY_GAME_ID", $sql);
	$sql = "";
	
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_GAME_DELETED}}";
	
	return $result;
}

//API
function GetApiInfo(){
	$api = Array();
	$api["version"] = "1.0";
	$api["version_date"] = "2015-01-17";
	$api["instance_properties"] = "api/v1/instance/properties.php?id=100";
	$api["user_preferences"] = "api/v1/instance/properties.php?id=100";
	$api["instance_highscore"] = "api/v1/instance/properties.php?id=100";
	$api["instance_score"] = "api/v1/instance/properties.php?id=100";
	return $api;
}

//INSTANCE
function DB_GetInstanceProperties($instanceID){
	ValidateOrDie("instanceID", $instanceID, "INT", 0, PHP_INT_MAX, "{{VALIDATION_INSTANCE_ID}}");
	ThrowErrorAndDie("not implemented");
}


function DB_GetInfoForInstance($instanceID){
	$result = Array();
	ValidateOrDie("instanceID", $instanceID, "INT", 0, PHP_INT_MAX, "{{VALIDATION_INSTANCE_ID}}");
	
	$instanceID = mysql_real_escape_string($instanceID);
	
	//Check that the instance exists
	$sql = "SELECT instance_id FROM ".DB_TABLE_INSTANCE." WHERE instance_id = $instanceID AND instance_deleted = 0";
	$data = ExecuteSQLOrDie("INSTANCE", "SELECT_INSTANCE_ID_FROM_INSTANCE_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		//Instance not in database
		throwErrorAndDie(ERROR_INSTANCE_NOT_IN_DATABASE);
	}
	
	$gameID = -1;
	
	$sql = "SELECT * FROM ". DB_TABLE_INSTANCE ." i, ". DB_TABLE_USER ." u, ". DB_TABLE_CLASS ." c, 
	". DB_TABLE_GAME ." g WHERE i.instance_id = $instanceID AND i.instance_created_user_id = u.user_id AND 
	i.instance_class_id = c.class_id AND i.instance_game_id = g.game_id AND g.game_deleted = 0 AND i.instance_deleted = 0 AND c.class_deleted = 0 AND u.user_deleted = 0";
	$data = ExecuteSQLOrDie("INSTANCE", "SELECT_INSTANCE_INFO_FROM_ID", $sql);
	$sql = "";
	if($info = mysql_fetch_array($data)){
		$result = Array();	//row row row your boat...
		$result["app_id"] = $info["game_id"];
		$result["name"] = $info["game_name"];
		$result["file_location"] = $info["game_file_location"];
		$result["author"] = $info["game_author"];
		$result["date_of_build"] = $info["game_date_of_build"]; 
		$result["version"] = $info["game_version"];
		$result["instance_id"] = $info["instance_id"];
		$result["instance_name"] = $info["instance_name"];
		$result["instance_description"] = $info["instance_description"];
		$result["instance_author"] = $info["user_name"] . " " . $info["user_surname"];
		$result["class_id"] = $info["class_id"];
		$result["class_name"] = $info["class_name"];
		$result["difficulty"] = $info["instance_difficulty"];
		$result["time_limit"] = $info["instance_time_limit"];
		$result["levels"] = $info["instance_levels"];
		$result["retries_available"] = $info["instance_retries_available"];
		$lang = json_decode($info["game_dictionary"]);
		/*
		foreach($lang as $lng => $lngarray){
			foreach($lngarray["dict"] as $key => $val){
				$lang[$lng]["dict"][$key] = htmlentities($val);
			}
		}
		*/
		$result["lang"] = $lang;
		
		$gameID = intval($info["game_id"]);
	}else{
		//Instance does not exist
		throwErrorAndDie(ERROR_INSTANCE_NOT_IN_DATABASE);
	}
	
	$result["api"] = GetApiInfo();
	
	$sql = "select * from ". DB_TABLE_SCORE ." s, ". DB_TABLE_USER ." u WHERE s.score_instance_id = $instanceID AND
	u.user_id = s.score_user_id AND user_deleted = 0 ORDER BY score_value DESC LIMIT 0, 20";
	$data = ExecuteSQLOrDie("SCORE", "SELECT_SCORE_INFO_FROM_INSTANCE_ID", $sql);
	$sql = "";
	$rank = 1;
	$scores = Array();
	while($info = mysql_fetch_array($data)){
		$row = Array();	//row row row your boat...
		$row["player"] = $info["user_name"] ." ". $info["user_surname"];
		$row["score"] = $info["score_value"];
		$row["explanation"] = $info["score_description"];
		$row["datetime"] = $info["score_datetime"];
		
		$scores[$rank] = $row;
		$rank++;
	}
	$result["score"] = $scores;
	
	$sql = "select * from ". DB_TABLE_VARIABLE ." v WHERE v.variable_instance_id = $instanceID AND variable_type NOT IN ('file', 'filelist')";
	$data = ExecuteSQLOrDie("VARIABLE", "SELECT_VARIABLES_FOR_INSTANCE", $sql);
	$sql = "";
	$vars = Array();
	while($info = mysql_fetch_array($data)){
		$row = Array();	//row row row your boat...
		$name = $info["variable_name"];
		$varType = $info["variable_type"];
		$row["type"] = $varType;
		$varVal = $info["variable_value"];
		switch(strtolower($varType)){
			case "string":
				$row["value"] = $varVal;
			break;
			case "stringlist":
				$valList = explode("|", $varVal);
				$row["value"] = $valList;
			break;
			case "int":
				$row["value"] = intVal($varVal);
			break;
			case "float":
				$row["value"] = floatVal($varVal);
			break;
			default:
				$row["value"] = "UNKNOWN VARTYPE ($varVal) $valList";
			break;
		}
		
		$vars[$name] = $row;
	}
	
	$sql = "SELECT * FROM ". DB_TABLE_VARIABLE ." v WHERE v.variable_instance_id = $instanceID AND variable_type IN ('file', 'filelist')";
	$data = ExecuteSQLOrDie("VARIABLE", "SELECT_VARIABLES_FOR_INSTANCE", $sql);
	$sql = "";
	while($info = mysql_fetch_array($data)){
		$row = Array();	//row row row your boat...
		$name = $info["variable_name"];
		$varType = $info["variable_type"];
		$row["type"] = $varType;
		$varVal = $info["variable_value"];
		switch(strtolower($varType)){
			case "file":
				$row["value"] = $varVal;
			break;
			case "filelist":
				$valList = explode("|", $varVal);
				$row["value"] = $valList;
			break;
			default:
				$row["value"] = "UNKNOWN VARTYPE ($varVal) $valList";
			break;
		}
		
		$vars[$name] = $row;
	}
	$result["variables"] = $vars;
	
	$sql = "select * from ". DB_TABLE_CONSTANT ." v WHERE v.constant_game_id = $gameID";
	$data = ExecuteSQLOrDie("CONSTANT", "SELECT_CONSTANTS_FOR_GAME", $sql);
	$sql = "";
	$const = Array();
	while($info = mysql_fetch_array($data)){
		$name = $info["constant_name"];
		$const[$name] = $info["constant_value"];
	}
	$result["constants"] = $const;
	
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_INSTANCE_GET_INFO}}";
	
	return $result;
}

function DB_GetInstancesForClass($classID){
	$result = Array();
	ValidateOrDie("classID", 			$classID,			"INT",		0, PHP_INT_MAX, "{{VALIDATION_CLASS_ID}}");
	
	$classID = mysql_real_escape_string($classID);
	
	//Check that the class exists
	$sql = "SELECT class_id FROM ".DB_TABLE_CLASS." WHERE class_id = $classID AND class_deleted = 0";
	$data = ExecuteSQLOrDie("CLASS", "SELECT_CLASS_ID_FROM_CLASS_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_CLASS_NOT_IN_DATABASE);
	}
	
	$sql = "SELECT * FROM ".DB_TABLE_INSTANCE." WHERE instance_class_id = $classID AND instance_deleted = 0 ORDER BY instance_id";
	$data = ExecuteSQLOrDie("INSTANCE", "SELECT_INSTANCES_IN_CLASS", $sql);
	$sql = "";
	$result["instanceslist"] = Array();
	while($info = mysql_fetch_array($data)){
		$row = Array();	//row row row your boat...
		$row["id"] = $info["instance_id"];
		$row["name"] = $info["instance_name"];
		$row["description"] = $info["instance_description"];
		$row["glyph"] = $info["instance_glyph"]; 
		$row["datetime"] = $info["instance_created_datetime"];
		$row["difficulty"] = $info["instance_difficulty"];
		$row["time_limit"] = $info["instance_time_limit"];
		$row["levels"] = $info["instance_levels"];
		$row["retries_available"] = $info["instance_retries_available"];
		$result["instanceslist"][] = $row;
	}
	
	$result["class_id"] = $classID;
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_CLASS_INSTANCE_LIST_RETRIEVED}}";
	return $result;
}

function DB_GetInstancesForGame($gameID){
	$result = Array();
	ValidateOrDie("gameID", 			$gameID,			"INT",		0, PHP_INT_MAX, "{{VALIDATION_GAME_ID}}");
	
	$gameID = mysql_real_escape_string($gameID);
	
	//Check that the class exists
	$sql = "SELECT game_id FROM ".DB_TABLE_GAME." WHERE game_id = $gameID AND game_deleted = 0 ORDER BY game_id";
	$data = ExecuteSQLOrDie("GAME", "SELECT_GAME_ID_FROM_GAME_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_GAME_NOT_IN_DATABASE);
	}
	
	$sql = "SELECT * FROM ".DB_TABLE_INSTANCE." WHERE instance_game_id = $gameID AND instance_deleted = 0 ORDER BY instance_id";
	$data = ExecuteSQLOrDie("INSTANCE", "SELECT_INSTANCES_FOR_GAME", $sql);
	$sql = "";
	while($info = mysql_fetch_array($data)){
		$row = Array();	//row row row your boat...
		$row["id"] = $info["instance_id"];
		$row["name"] = $info["instance_name"];
		$row["description"] = $info["instance_description"];
		$row["glyph"] = $info["instance_glyph"]; 
		$row["datetime"] = $info["instance_created_datetime"];
		$row["difficulty"] = $info["instance_difficulty"];
		$row["time_limit"] = $info["instance_time_limit"];
		$row["levels"] = $info["instance_levels"];
		$row["retries_available"] = $info["instance_retries_available"];
		$result["instanceslist"][] = $row;
	}
	
	$result["game_id"] = $gameID;
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_GAME_INSTANCE_LIST_RETRIEVED}}";
	return $result;
}

function DB_InstanceInsert($classID, $userID, $gameID, $name, $description, $glyph, $difficulty, $timeLimit, $levels, $retriesAvailable, $variables){ 
	ValidateOrDie("classID", 			$classID,			"INT",		0, PHP_INT_MAX, "{{VALIDATION_CLASS_ID}}");
	ValidateOrDie("userID", 			$userID,			"INT",		0, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	ValidateOrDie("gameID", 			$gameID,			"INT",		0, PHP_INT_MAX, "{{VALIDATION_GAME_ID}}");
	ValidateOrDie("instance name", 		$name,				"STRING",	1, 30, "{{VALIDATION_INSTANCE_NAME1}} 1 {{AND}} 30 {{VALIDATION_INSTANCE_NAME2}}");
	ValidateOrDie("description", 		$description,		"STRING",	0, 65535, "{{VALIDATION_INSTANCE_DESCRIPTION}}");
	ValidateOrDie("glyph", 				$glyph,				"FILE",		0, 255, "{{VALIDATION_INSTANCE_GLYPH}}");
	ValidateOrDie("difficulty", 		$difficulty,		"INT",		0, PHP_INT_MAX, "{{VALIDATION_INSTANCE_DIFFICULTY}}");
	ValidateOrDie("timeLimit", 			$timeLimit,			"INT",		0, PHP_INT_MAX, "{{VALIDATION_INSTANCE_TIME_LIMIT}}");
	ValidateOrDie("levels", 			$levels,			"INT",		0, PHP_INT_MAX, "{{VALIDATION_INSTANCE_LEVELS}}");
	ValidateOrDie("retriesAvailable", 	$retriesAvailable,	"INT",		0, PHP_INT_MAX, "{{VALIDATION_INSTANCE_RETRIES}}");
	
	$classID = mysql_real_escape_string($classID);
	$userID = mysql_real_escape_string($userID);
	$gameID = mysql_real_escape_string($gameID);
	$name = mysql_real_escape_string($name);
	$description = mysql_real_escape_string($description);
	$glyph = mysql_real_escape_string($glyph);
	$difficulty = mysql_real_escape_string($difficulty);
	$timeLimit = mysql_real_escape_string($timeLimit);
	$levels = mysql_real_escape_string($levels);
	$retriesAvailable = mysql_real_escape_string($retriesAvailable);
	
	//Check that the class exists
	$sql = "SELECT class_id FROM ".DB_TABLE_CLASS." WHERE class_id = $classID AND class_deleted = 0";
	$data = ExecuteSQLOrDie("CLASS", "SELECT_CLASS_ID_FROM_CLASS_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_CLASS_NOT_IN_DATABASE);
	}
	
	//Check that the user exists
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	//Check that the game exists and fetch its variable list
	$sql = "SELECT game_variables FROM ".DB_TABLE_GAME." WHERE game_id = $gameID AND game_deleted = 0";
	$data = ExecuteSQLOrDie("GAME", "SELECT_GAME_VARIABLES_FROM_GAME_ID", $sql);
	$sql = "";
	if($i = mysql_fetch_array($data)){
		$gameVariables = json_decode($i["game_variables"], true);
	}else{
		ThrowErrorAndDie(ERROR_GAME_NOT_IN_DATABASE);
	}
	
	//Fill variable values into gameVariables array.
	foreach($variables as $varname => $varvalue){
		if(!isset($gameVariables[$varname])){
			ThrowErrorAndDie(ERROR_GAME_DOES_NOT_CONTAIN_VARIABLE);
		}
		$gameVariables[$varname]["value"] = $varvalue;
	}
	
	//Check that all variables have had a value assigned to them
	foreach($gameVariables as $varname => $var){
		if(!isset($var["value"])){
			ThrowErrorAndDie(ERROR_GAME_VARIABLE_HAS_NOT_HAD_VALUE_SET);
		}
	}
	
	//Select next ID.
	$sql = "SELECT Auto_Increment as ai FROM information_schema.tables WHERE table_name = '".DB_TABLE_INSTANCE."'";
	$data = ExecuteSQLOrDie("SCHEMA", "SELECT_NEXT_VALID_INSTANCE_ID", $sql);
	$sql = "";
	$instanceID = -1;
	if($i = mysql_fetch_array($data)){
		$instanceID = intVal($i["ai"]);
	}
	
	//Insert into the database
	$sql = "INSERT INTO ".DB_TABLE_INSTANCE."
	(instance_id, instance_name, instance_glyph, instance_description, instance_class_id, instance_created_datetime, instance_created_user_id, instance_game_id, instance_difficulty, instance_time_limit, instance_levels, instance_retries_available, instance_deleted)
	VALUES
	($instanceID, '$name', '$glyph', '$description', $classID, Now(), $userID, $gameID, $difficulty, $timeLimit, $levels, $retriesAvailable, 0);";
	$data = ExecuteSQLOrDie("GAME", "SELECT_GAME_ID_FROM_GAME_ID", $sql);
	$sql = "";
	
	//Insert variable into 
	foreach($gameVariables as $varname => $var){
		DB_AddVariable($instanceID, 0, $varname, $var["type"], $var["value"]);
	}
	//print "SUCCESS!"; //TODO REMOVE
	
	return Array("result" => "SUCCESS", "resultText" => "{{SUCCESS_INSTANCE_CREATED}}");
}

function DB_InstanceUpdate($instanceID, $name, $description, $difficulty, $timeLimit, $levels, $retriesAvailable){
	
	ValidateOrDie("instanceID", 		$instanceID,		"INT",		1, PHP_INT_MAX, "{{VALIDATION_INSTANCE_ID}}");
	
	$instanceID = mysql_real_escape_string($instanceID);
	$name = mysql_real_escape_string($name);
	$description = mysql_real_escape_string($description);
	$difficulty = mysql_real_escape_string($difficulty);
	$timeLimit = mysql_real_escape_string($timeLimit);
	$levels = mysql_real_escape_string($levels);
	$retriesAvailable = mysql_real_escape_string($retriesAvailable);
	
	//Check that the instance exists
	$sql = "SELECT instance_id FROM ".DB_TABLE_INSTANCE." WHERE instance_id = $instanceID AND instance_deleted = 0";
	$data = ExecuteSQLOrDie("INSTANCE", "SELECT_INSTANCE_ID_FROM_INSTANCE_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_INSTANCE_NOT_IN_DATABASE);
	}
	
	$sql = "UPDATE ".DB_TABLE_INSTANCE." SET ";
	$updateStatements = Array();
	
	$statement = ValidateAndGenerateUpdateStatement("instance_name", "VARCHAR",	"instance name", 	$name, 	"STRING",	1, 255);
	if($statement){$updateStatements[] = $statement;};
	
	$statement = ValidateAndGenerateUpdateStatement("instance_description", "VARCHAR",	"description", 	$description,		"STRING",	0, 65535);
	if($statement){$updateStatements[] = $statement;};
	
	$statement = ValidateAndGenerateUpdateStatement("instance_difficulty", "INT",	"difficulty", 	$difficulty,		"INT",		0, PHP_INT_MAX);
	if($statement){$updateStatements[] = $statement;};
	
	$statement = ValidateAndGenerateUpdateStatement("instance_time_limit", "INT",	"time limit", 	$timeLimit,		"INT",		0, PHP_INT_MAX);
	if($statement){$updateStatements[] = $statement;};
	
	$statement = ValidateAndGenerateUpdateStatement("instance_levels", "INT",	"levels", 	$levels,		"INT",		0, PHP_INT_MAX);
	if($statement){$updateStatements[] = $statement;};
	
	$statement = ValidateAndGenerateUpdateStatement("instance_retries_available", "INT",	"retries available", 	$retriesAvailable,		"INT",		0, PHP_INT_MAX);
	if($statement){$updateStatements[] = $statement;};
	
	$sql .= implode(", ",$updateStatements);
	$sql .= " WHERE instance_id = $instanceID";
	
	$data = ExecuteSQLOrDie("INSTANCE", "UPDATE_INSTANCE_INFO", $sql);
	$sql = "";
	
	return true;
}

function DB_InstanceDelete($instanceID){
	$result = Array();
	
	//Validation
	ValidateOrDie("instanceID", 	$instanceID,	"INT",		1, PHP_INT_MAX, "{{VALIDATION_INSTANCE_ID}}"); 
	
	$instanceID = mysql_real_escape_string($instanceID);
	
	//Check that the instance exists
	$sql = "SELECT instance_id FROM ".DB_TABLE_INSTANCE." WHERE instance_id = $instanceID AND instance_deleted = 0";
	$data = ExecuteSQLOrDie("INSTANCE", "SELECT_INSTANCE_ID_FROM_INSTANCE_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_INSTANCE_NOT_IN_DATABASE);
	}
	
	//soft delete instance
	$sql = "UPDATE ".DB_TABLE_INSTANCE." SET instance_deleted = 1 WHERE instance_id = $instanceID AND instance_deleted = 0";
	$data = ExecuteSQLOrDie("INSTANCE", "SOFT_DELETE_INSTANCE_BY_INSTANCE_ID", $sql);
	$sql = "";
	
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_INSTANCE_DELETED}}";
	
	return $result;
}

//SCORE

//Select score for instance id, returns list of scores
function DB_ScoreForInstance($instanceID, $orderBy){
	//Validate
	ValidateOrDie("instanceID",	$instanceID,"INT",		1, PHP_INT_MAX, "{{VALIDATION_INSTANCE_ID}}");
	
	$instanceID = mysql_real_escape_string($instanceID);
	
	//Create order by statement
	$orderBySQL = "ORDER BY id DESC";
	switch(strtolower($orderBy)){
		case "time":
			$orderBySQL = "ORDER BY score_datetime ASC";
		break;
		case "user":
			$orderBySQL = "ORDER BY score_user_id ASC";
		break;
		case "score":
			$orderBySQL = "ORDER BY score_value DESC";
		break;
		default:
			ThrowErrorAndDie(ERROR_INVALID_ORDER_BY_PARAMETER);
		break;
	}
	
	//Check that the instance exists
	$sql = "SELECT instance_id FROM ".DB_TABLE_INSTANCE." WHERE instance_id = $instanceID AND instance_deleted = 0";
	$data = ExecuteSQLOrDie("INSTANCE", "SELECT_INSTANCE_ID_FROM_INSTANCE_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_INSTANCE_NOT_IN_DATABASE);
	}
	
	//Retrieve all scores for given instance, order by value descending.
	$sql = "select * from ". DB_TABLE_SCORE ." s, ". DB_TABLE_USER ." u WHERE s.score_instance_id = $instanceID AND
	u.user_id = s.score_user_id AND u.user_deleted = 0 $orderBySQL";
	$data = ExecuteSQLOrDie("SCORE", "SELECT_SCORE_INFO_FROM_INSTANCE_ID", $sql);
	$sql = "";
	$rank = 1;
	$scores = Array();
	while($info = mysql_fetch_array($data)){
		$row = Array();	//row row row your boat...
		$row["player"] = $info["user_name"] ." ". $info["user_surname"];
		$row["score"] = intval($info["score_value"]);
		$row["explanation"] = $info["score_description"];
		$row["datetime"] = $info["score_datetime"];
		
		$scores[$rank] = $row;
		$rank++;
	}
	$result["scorelist"] = $scores;
	$result["instance_id"] = $instanceID;
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_SCORES_RETRIEVED}}";
	return $result;
}

//Select score for instance id and user id, returns list of scores
function DB_ScoreForInstanceAndUser($instanceID, $userID, $orderBy){
	//Validate
	ValidateOrDie("instanceID",	$instanceID,"INT",		1, PHP_INT_MAX, "{{VALIDATION_INSTANCE_ID}}");
	ValidateOrDie("userID",	$userID,"INT",		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	
	$instanceID = mysql_real_escape_string($instanceID);
	$userID = mysql_real_escape_string($userID);
	
	//Create order by statement
	$orderBySQL = "ORDER BY id DESC";
	switch(strtolower($orderBy)){
		case "time":
			$orderBySQL = "ORDER BY score_datetime ASC";
		break;
		case "user":
			$orderBySQL = "ORDER BY score_user_id ASC";
		break;
		case "score":
			$orderBySQL = "ORDER BY score_value DESC";
		break;
		default:
			ThrowErrorAndDie(ERROR_INVALID_ORDER_BY_PARAMETER);
		break;
	}
	
	//Check that the user exists
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	//Check that the instance exists
	$sql = "SELECT instance_id FROM ".DB_TABLE_INSTANCE." WHERE instance_id = $instanceID AND instance_deleted = 0";
	$data = ExecuteSQLOrDie("INSTANCE", "SELECT_INSTANCE_ID_FROM_INSTANCE_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_INSTANCE_NOT_IN_DATABASE);
	}
	
	//Retrieve all scores for given instance, order by value descending.
	$sql = "select * from ". DB_TABLE_SCORE ." s, ". DB_TABLE_USER ." u WHERE s.score_instance_id = $instanceID AND u.user_id = $userID AND
	u.user_id = s.score_user_id AND u.user_deleted = 0 $orderBySQL";
	$data = ExecuteSQLOrDie("SCORE", "SELECT_SCORE_INFO_FROM_INSTANCE_ID_AND_USER_ID", $sql);
	$sql = "";
	$rank = 1;
	$scores = Array();
	while($info = mysql_fetch_array($data)){
		$row = Array();	//row row row your boat...
		$row["player"] = $info["user_name"] ." ". $info["user_surname"];
		$row["score"] = intval($info["score_value"]);
		$row["explanation"] = $info["score_description"];
		$row["datetime"] = $info["score_datetime"];
		
		$scores[$rank] = $row;
		$rank++;
	}
	$result["scorelist"] = $scores;
	$result["instance_id"] = $instanceID;
	$result["user_id"] = $userID;
	$result["result"] = "SUCCESS";
	$result["resultText"] = "{{SUCCESS_SCORES_RETRIEVED}}";
	return $result;
}

function DB_ScoreInsert($userID, $userIP, $instanceID, $value, $text){
	
	//Validation
	ValidateOrDie("userID", 	$userID,	"INT",		1, PHP_INT_MAX, "{{VALIDATION_USER_ID}}");
	ValidateOrDie("userIP", 	$userIP,	"IP",		0, 0, "{{VALIDATION_IP}}");
	ValidateOrDie("instanceID",	$instanceID,"INT",		1, PHP_INT_MAX, "{{VALIDATION_INSTANCE_ID}}");
	ValidateOrDie("value",		$value,		"INT",		PHP_INT_MIN, PHP_INT_MAX, "{{VALIDATION_SCORE_VALUE}}");
	ValidateOrDie("text", 		$text,		"STRING",	0, 65535, "{{VALIDATION_SCORE_TEXT}}");
	
	$userID = mysql_real_escape_string($userID);
	$userIP = mysql_real_escape_string($userIP);
	$instanceID = mysql_real_escape_string($instanceID);
	$value = mysql_real_escape_string($value);
	$text = mysql_real_escape_string($text);
	
	//Check that the instance exists
	$sql = "SELECT instance_id FROM ".DB_TABLE_INSTANCE." WHERE instance_id = $instanceID AND instance_deleted = 0";
	$data = ExecuteSQLOrDie("INSTANCE", "SELECT_INSTANCE_ID_FROM_INSTANCE_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_INSTANCE_NOT_IN_DATABASE);
	}
	
	//Check that the user exists
	$sql = "SELECT user_id FROM ".DB_TABLE_USER." WHERE user_id = $userID AND user_deleted = 0";
	$data = ExecuteSQLOrDie("USER", "SELECT_USER_ID_FROM_USER_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_USER_NOT_IN_DATABASE);
	}
	
	//Insert
	$sql = "INSERT INTO ".DB_TABLE_SCORE."
	(score_id, score_datetime, score_user_id, score_user_ip, score_instance_id, score_value, score_description)
	VALUES
	(null, Now(), $userID, '$userIP', $instanceID, $value, '$text');";
	$data = ExecuteSQLOrDie("SCORE", "INSERT_SCORE", $sql);
	$sql = "";
	
	return true;
}

function DB_ScoreUpdate(){
	return "Not implemented";
}

function DB_ScoreDelete(){
	return "Not implemented";
}

//VARIABLE
function DB_AddVariable($instanceID, $gameID, $name, $type, $value){
	
	//Validation
	ValidateOrDie("instanceID",	$instanceID,"INT",		0, PHP_INT_MAX, "{{VALIDATION_INSTANCE_ID}}");
	ValidateOrDie("gameID", 	$gameID,	"INT",		0, PHP_INT_MAX, "{{VALIDATION_GAME_ID}}");
	ValidateOrDie("name", 		$name,		"STRING",	0, 32, "{{VALIDATION_VARIABLE_NAME}}");
	ValidateOrDie("type", 		$type,		"STRING",	0, 32, "{{VALIDATION_VARIABLE_TYPE}}");
	
	$instanceID = mysql_real_escape_string($instanceID);
	$gameID = mysql_real_escape_string($gameID);
	$name = mysql_real_escape_string($name);
	$type = mysql_real_escape_string($type);
	
	if($type == "filelist" || $type == "stringlist"){
		if(is_array($value)){
			$value = implode("|",$value);
			//print "VALUE: $value ENDVALUE";
		}
		ValidateOrDie("value", 		$value,		"STRING",	0, 65535, "{{VALIDATION_VARIABLE_VALUE}}");
	}else{
		ValidateOrDie("value", 		$value,		"STRING",	0, 65535, "{{VALIDATION_VARIABLE_VALUE}}");
	}
	
	//Check that the instance exists
	$sql = "SELECT instance_id FROM ".DB_TABLE_INSTANCE." WHERE instance_id = $instanceID AND instance_deleted = 0";
	$data = ExecuteSQLOrDie("INSTANCE", "SELECT_INSTANCE_ID_FROM_INSTANCE_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		//Check that the game exists
		$sql = "SELECT game_id FROM ".DB_TABLE_GAME." WHERE game_id = $gameID AND game_deleted = 0";
		$data = ExecuteSQLOrDie("GAME", "SELECT_GAME_ID_FROM_GAME_ID", $sql);
		$sql = "";
		if(mysql_num_rows($data) == 0){
			ThrowErrorAndDie(ERROR_GAME_OR_INSTANCE_NOT_IN_DATABASE);
		}
	}
	
	//Insert
	$sql = "INSERT INTO ".DB_TABLE_VARIABLE."
	(variable_id, variable_instance_id, variable_game_id, variable_name, variable_type, variable_value)
	VALUES
	(null, $instanceID, $gameID, '$name', '$type', '$value');";
	$data = ExecuteSQLOrDie("VARIABLE", "INSERT_VARIABLE", $sql);
	$sql = "";
	
	return true;
}

function DB_GetVariablesForInstance($instanceID){
	
	$result = Array();
	ValidateOrDie("instanceID", $instanceID, "INT", 0, PHP_INT_MAX, "{{VALIDATION_INSTANCE_ID}}");
	
	$instanceID = mysql_real_escape_string($instanceID);
	
	//Check that the instance exists
	$sql = "SELECT instance_id FROM ".DB_TABLE_INSTANCE." WHERE instance_id = $instanceID AND instance_deleted = 0";
	$data = ExecuteSQLOrDie("INSTANCE", "SELECT_INSTANCE_ID_FROM_INSTANCE_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_INSTANCE_NOT_IN_DATABASE);
	}
	
	$sql = "SELECT * FROM ".DB_TABLE_VARIABLE." WHERE variable_instance_id = $instanceID";
	$data = ExecuteSQLOrDie("VARIABLE", "SELECT_VARIABLES_FOR_INSTANCE", $sql);
	$sql = "";
	while($info = mysql_fetch_array($data)){
		$row = Array();	//row row row your boat...
		$row["ID"] = $info["variable_id"];
		$row["NAME"] = $info["variable_name"];
		$row["TYPE"] = $info["variable_type"];
		$row["VALUE"] = $info["variable_value"];
		$result[] = $row;
	}
	
	return $result;
}

function DB_GetVariablesForGame($gameID){
	
	$result = Array();
	ValidateOrDie("gameID", $gameID, "INT", 0, PHP_INT_MAX, "{{VALIDATION_GAME_ID}}");
	
	$gameID = mysql_real_escape_string($gameID);
	
	//Check that the game exists
	$sql = "SELECT game_id FROM ".DB_TABLE_GAME." WHERE game_id = $gameID AND game_deleted = 0";
	$data = ExecuteSQLOrDie("GAME", "SELECT_GAME_ID_FROM_GAME_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_GAME_NOT_IN_DATABASE);
	}
	
	$sql = "SELECT * FROM ".DB_TABLE_VARIABLE." WHERE variable_game_id = $gameID";
	$data = ExecuteSQLOrDie("VARIABLE", "SELECT_VARIABLES_FOR_GAME", $sql);
	$sql = "";
	while($info = mysql_fetch_array($data)){
		$row = Array();	//row row row your boat...
		$row["ID"] = $info["variable_id"];
		$row["NAME"] = $info["variable_name"];
		$row["TYPE"] = $info["variable_type"];
		$row["VALUE"] = $info["variable_value"];
		$result[] = $row;
	}
	
	return $result;
}

function DB_VariableUpdate(){
	return "Not implemented";
}

function DB_VariableDelete(){
	return "Not implemented";
}

//CONSTANT
function DB_AddConstant($gameID, $name, $value){
	
	//Validation
	ValidateOrDie("gameID", 	$gameID,	"INT",		1, PHP_INT_MAX, "{{VALIDATION_GAME_ID}}");
	ValidateOrDie("name", 		$name,		"STRING",	0, 32, "{{VALIDATION_CONSTANT_NAME}}");
	ValidateOrDie("value", 		$value,		"STRING",	0, 65535, "{{VALIDATION_CONSTANT_VALUE}}");
	
	$gameID = mysql_real_escape_string($gameID);
	$name = mysql_real_escape_string($name);
	$value = mysql_real_escape_string($value);
	
	//Check that the game exists
	$sql = "SELECT game_id FROM ".DB_TABLE_GAME." WHERE game_id = $gameID AND game_deleted = 0";
	$data = ExecuteSQLOrDie("GAME", "SELECT_GAME_ID_FROM_GAME_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_GAME_NOT_IN_DATABASE);
	}
	
	//Insert
	$sql = "INSERT INTO ".DB_TABLE_CONSTANT."
	(constant_id, constant_game_id, constant_name, constant_value) 
	VALUES
	(null, $gameID, '$name', '$value');";
	$data = ExecuteSQLOrDie("CONSTANT", "INSERT_CONSTANT", $sql);
	$sql = "";
	
	return true;
}

function DB_GetConstantsForGame($gameID){
	
	$result = Array();
	ValidateOrDie("gameID", $gameID, "INT", 0, PHP_INT_MAX, "{{VALIDATION_GAME_ID}}");
	
	$gameID = mysql_real_escape_string($gameID);
	
	//Check that the game exists
	$sql = "SELECT game_id FROM ".DB_TABLE_GAME." WHERE game_id = $gameID AND game_deleted = 0";
	$data = ExecuteSQLOrDie("GAME", "SELECT_GAME_ID_FROM_GAME_ID", $sql);
	$sql = "";
	if(mysql_num_rows($data) == 0){
		ThrowErrorAndDie(ERROR_GAME_NOT_IN_DATABASE);
	}
	
	$sql = "SELECT * FROM ".DB_TABLE_CONSTANT." WHERE constant_game_id = $gameID";
	$data = ExecuteSQLOrDie("CONSTANT", "SELECT_CONSTANTS_FOR_GAME", $sql);
	$sql = "";
	while($info = mysql_fetch_array($data)){
		$row = Array();	//row row row your boat...
		$row["ID"] = $info["constant_id"];
		$row["NAME"] = $info["constant_name"];
		$row["VALUE"] = $info["constant_value"];
		$result[] = $row;
	}
	
	return $result;
}

function DB_ConstantUpdate(){
	return "Not implemented";
}

function DB_ConstantDelete(){
	return "Not implemented";
}

//FILE
function DB_FileInsert(){
	return "Not implemented";
}

function DB_FileUpdate(){
	return "Not implemented";
}

function DB_FileDelete(){
	return "Not implemented";
}

//CATEGORY
function DB_CategoryInsert(){
	return "Not implemented";
}

function DB_CategoryUpdate(){
	return "Not implemented";
}

function DB_CategoryDelete(){
	return "Not implemented";
}

//CATEGORY MEMBER
function DB_CategoryMemberInsert(){
	return "Not implemented";
}

function DB_CategoryMemberUpdate(){
	return "Not implemented";
}

function DB_CategoryMemberDelete(){
	return "Not implemented";
}



?>
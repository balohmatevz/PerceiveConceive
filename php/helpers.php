<?php
	//Helper functions

	//Returns $_POST["postParam"] if it exists, returns $alternative otherwise
	function FromPostIfExist($postParam, $alternative){
		if(isset($_POST[$postParam])){
			return $_POST[$postParam];
		}else{
			if(isset($alternative)){
				return $alternative;
			}else{
				return null;
			}
		}
	}

	//Returns $_GET["getParam"] if it exists, returns $alternative otherwise
	function FromGetIfExist($getParam, $alternative){
		if(isset($_GET[$getParam])){
			return $_GET[$getParam];
		}else{
			if(isset($alternative)){
				return $alternative;
			}else{
				return null;
			}
		}
	}

	//Returns $_COOKIE["getParam"] if it exists, returns $alternative otherwise
	function FromCookieIfExist($cookieParam, $alternative){
		if(isset($_COOKIE[$cookieParam])){
			return $_COOKIE[$cookieParam];
		}else{
			if(isset($alternative)){
				return $alternative;
			}else{
				return null;
			}
		}
	}
	
	function ThrowError($error){
		print json_encode(Array("result" => "DANGER", "resultText" => "".$error));
	}
	
	function ThrowWarning($error){
		print json_encode(Array("result" => "WARNING", "resultText" => "".$error));
	}
	
	//Error reporting and handling
	function ThrowWarningAndDie($error){
		ThrowWarning($error);
		die();
	}
	
	//Error reporting and handling
	function ThrowErrorAndDie($error){
		ThrowError($error);
		die();
	}
	
	//Generats a password hash from the plain text password
	function GeneratePassword($plainTextPassword){
		return MD5($plainTextPassword);
	}
	
	
	//Form generating micro-functions
	function InputHTML($displayName, $name, $type, $value = "", $postfix = ""){
		if($displayName){ 
			print "<b>$displayName:</b> ";
		}
		print "<input type='$type' name='$name' value='$value' />$postfix";
	}
	function InputText($displayName, $name, $value = "", $postfix = ""){
		InputHTML($displayName, $name, "text", $value, $postfix);
	}
	function InputPassword($displayName, $name, $value = "", $postfix = ""){
		InputHTML($displayName, $name, "password", $value, $postfix);
	}
	function InputNumber($displayName, $name, $value = "", $postfix = ""){
		InputHTML($displayName, $name, "number", $value, $postfix);
	}
	function InputHidden($name, $value = ""){
		InputHTML("", $name, "hidden", $value, "");
	}
	function InputSubmit($displayName, $name, $value = "", $postfix = ""){
		InputHTML($displayName, $name, "submit", $value, $postfix);
	}

?>
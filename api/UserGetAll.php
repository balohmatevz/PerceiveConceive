<?php

	include("../php/init.php");
	
	print json_encode(DB_UserGetAll($userID));
	
?>
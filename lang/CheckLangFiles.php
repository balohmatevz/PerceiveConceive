<?php
//Debug file to check languages files for missing values

print "<head><meta charset='utf-8'></head>";

$data = Array();
$fileList = Array();

$files = scandir(".");
foreach($files as $i => $file){
	if($file == "." || $file == ".."){
		continue;
	}
	
	if(strtolower(pathinfo($file, PATHINFO_EXTENSION)) != "js"){
		continue;
	}
	
	$fileList[] = $file;
	$linesText = file_get_contents($file);
	$lines = explode("\n", $linesText);
	foreach($lines as $j => $line){
		$line = trim($line);
		$pair = explode(":", $line);
		
		if(count($pair) < 2){
			continue;
		}
		
		$key = trim(str_replace("\"", "", $pair[0]));
		$value = trim(str_replace("\",", "", $pair[1]));
		$value = trim(str_replace("\"", "", $value));
		if(count($pair) > 2){
			for($k = 2; $k < count($pair); $k++){
				$value .= trim(str_replace("\",", "", $pair[$k]));
				$value .= trim(str_replace("\"", "", $value));
			}
		}
		$data[$key][$file] = $value;
	}
}

print "<h1>Errors</h1>";
foreach($data as $key => $valList){
	$first = true;
	foreach($fileList as $i => $file){
		if(!isset($valList[$file])){
			if($first){
				print "<b>ERROR: $key</b><br />";
				$first = false;
			}
			print "<font color='red'>Missing in $file</font><br />";
		}
	}
}

print "<h1>Data</h1>";

print "<table style='width: 100%'>";
print "<tr><td></td>";
foreach($fileList as $key => $file){
	print "<th>$file</th>";
}
print "</tr>";
foreach($data as $key => $valList){
	print "<tr><th>$key</th>";
	foreach($fileList as $i => $file){
		if(!isset($valList[$file])){
			print "<td><font color='red'>Missing</font></td>";
		}else{
			print "<td>$valList[$file]</td>";
		}
	}
	print "</tr>";
}
print "</table>";

?>
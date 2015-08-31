<?php
header("content-type:text/html; charset=utf8");
//require_once("global.php");
$fileName = '1.txt';
$handle = fopen($fileName, "r");
//while(!feof($handle)){
	$line = fgets($handle);
$arr = explode("\t",$line);
//}
print_r( $arr );
fclose($handle );





?>
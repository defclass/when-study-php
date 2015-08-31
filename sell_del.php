<?php
require_once('global.php');
$vid = $_GET['vid'];
$db = DB::instance();
$cond = " id = $vid ";
$rt = $db->delete('zf_baifang', $cond);

if($rt){echo success($msg);}else{echo  error($msg);}
exit;



?>
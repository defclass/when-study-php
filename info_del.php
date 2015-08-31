<?php
require_once('global.php');
$cid = $_GET['uid'];
$db = DB::instance();
$cond = " id = $cid ";
$rt = $db->delete('zf_customer', $cond);

if($rt){echo success($msg);}else{echo  error($msg);}
exit;



?>
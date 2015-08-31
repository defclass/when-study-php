<?php
require_once('global.php');

$admin_id  = $_SESSION['id'];
$level_id = $_SESSION['level_id']; 

if( $_GET['action'] == "huoqu" ){

	$arr['admin_id'] = $admin_id ;
	$arr['pd_id']= 10;//自动为待邀约客户
	$arr['br_last_visit'] = 0;	  
}

if( $_GET['action'] == "fangqi" ){
	$arr['br_last_visit'] = 0;	  
	$arr['admin_id'] = 0 ;
}

$cond = "id =".$_GET['cid'];

$db=DB::instance();
$rt= $db->update("zf_customer", $arr, $cond);
file_put_contents("text",$rt);

if($rt){echo success($msg);}else{echo  error($msg);}
exit;	

?>

<?php
require_once('global.php');
$admin_id  = $_SESSION['id'];
$level_id = $_SESSION['level_id']; 


if($_GET['action'] =="add"){
	foreach( $_POST as  &$v ){
		$v = addslashes(trim(stripslashes($v)));
	}
	$arr = array();
	/* 给自己提醒 */
	if( empty($_GET['admin_id'])){ //用 $_GET['admin_id'] 这个参数来判断是上级提醒，还是自己提醒自己
		$arr['admin_id'] = $admin_id;
		$arr['cust_id'] = $_GET['uid'];
	}

	/* 给下属提醒 */
	if( !empty($_GET['admin_id'])){
		$arr['admin_id'] = $_GET['admin_id'];
		$arr['cust_id'] = $_GET['cust_id'];
		$arr['send_id'] = $admin_id ;
	}

	$arr['id'] = f_id();
	$arr['msg'] = $_POST['msg'];
	$arr['time'] = strtotime($_POST['time']);

	$db = DB::instance();
	$rt = $db->insert('zf_alert',$arr);
	if($rt){echo success($msg);}else{echo  error($msg);}
	exit;
}

if($_GET['action'] =="del"){
	foreach( $_POST as  &$v ){
		$v = addslashes(trim(stripslashes($v)));
	}
	$id = $_POST['id'];
	$arr['status'] = 1;
	$cond = " id = ".$id;
	$db = DB::instance();
	$rt = $db->update('zf_alert',$arr, $cond );
	if($rt){echo "删除成功";}else{echo "删除不成功" ;}
	exit;
}

if($_GET['action'] =="new"){
	$arr['cust_id'] = $_GET['cust_id'];
	$arr['staff_id'] = $_GET['staff_id'];
	$tp->set_file('alert_new');
	$tp->p();
}












?>

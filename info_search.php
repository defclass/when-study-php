<?php
require_once('global.php');

$user_name = $_POST['user_name'];
$user_tel = $_POST['user_tel'];

$where = "";
   

if (!empty($user_name)){
	$where .= " and user_name = '$user_name'";
}

if (!empty($user_tel)){
	$where .= " and (user_tel = '$user_tel' or user_tel2 = '$user_tel')";
}

if($where =="" ){
	echo "请输入搜索条件";
	exit;
}

$sql = "select * from zf_customer where 1=1";

$sql .= $where;

$db = DB::instance();
$custs = $db->get_one( $sql );
if( $custs ){
	echo "该客户已经存在";
}else{
	echo "该客户不存在，可以录入。";
}


?>

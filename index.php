<?php
require_once 'global.php';
//执行每日脚本
$a = file_get_contents("drsfzx.txt");//当日是否执行
$today =  strtotime(date('Y-m-d'));

//if($a < $today){
//	include('mrgx.php');
//	file_put_contents('drsfzx.txt',$today);
//}

if ($_GET['action']=='login') {
	foreach( $_POST as  &$v ){
		$v = addslashes(trim(stripslashes($v)));
	}

	$username = $_POST['username'];
	$password = $_POST['password'];
	$checkWord = $_POST['code'];

	/* 简单验证 */
	$msg = "";
	if (!$_POST['username']&& !$_POST['password']) {
		$msg = '登陆名或密码错误';
	} elseif (strlen($_POST['username'])<4) {
		$msg = '登陆名或密码错误';
	} elseif (strlen($_POST['username'])>30) {
		$msg = '登陆名或密码错误';
	} elseif (!preg_match('/^(?![_0-9])[\w\x{4e00}-\x{9fa5}]{4,16}$/u',$_POST['username'])) {
		$msg ='登陆名或密码错误';
	} 

	if( !empty( $msg ) ){
		echo  $msg;
		exit;
	}

	/* 验证验证码和账号密码 */
	if( $checkWord == $_SESSION['checkWord'] ){

		$db = DB::instance();
		$sql = "select * from zf_admin where user = '$username' ";

		$rt = $db->get_one( $sql );

		if ($rt){
			//print_r( $rt );
			if($rt['banned'] > 0 ){
				echo "您目前不能登陆，请联系管理员";
				exit();
			}
			$salt = $rt['salt'];
			$db_password = $rt['password'];
	
		        /* 计算加盐值 */
			$salt1 = substr( $salt, 0,3);
			$salt2 = substr( $salt, 3,3);

			$password = md5($salt1.md5( $password ).$salt2);
		
			if( $db_password == $password ){ 
				$msg = "登陆成功";
				/**
				 * 获取管理员ID
				 * 管理员 真实姓名
				 */
				$_SESSION['id'] = $rt['id'];
				$_SESSION['name'] = $rt['name'];
				$_SESSION['level_id'] = $rt['level_id'];
				
				/* 更新最后登陆时间，IP */
				$arr['lasttime'] = time();
				$arr['lastip'] = GetIP();
				$cond = "id = ".$rt['id'];
				$db = DB::instance();
				$db->update('zf_admin', $arr, $cond );

			}else{
				$msg = "用户名或密码错误";
			}
		}else{
			$msg = "用户名或密码错误";
		}
	}else{ 	$msg= "验证码错误"; }
	
	echo $msg;
	exit;
}


if($_GET['action'] == 'logout'){
	session_destroy();
	header("location:index.php");

}

if($_GET['action'] == ''){
	
	$tp->set_file('user_login');
	$tp->p();
}

?>

<?php
require_once('global.php');
	
$admin_id  = $_SESSION['id'];
$level_id = $_SESSION['level_id']; 


if( $_GET['action'] == '' ){
	$sql = "select * from zf_admin where id = '$admin_id'";
	$db= DB::instance();
	$user = $db->get_one( $sql );
	$tp->set_file('passwd_edit');
	$tp->p();
}

if( $_GET['action'] == 'submit' ){

	$msg = "";
	if ($_POST['origin_pwd'] && $_POST['password'] && $_POST['password_repeat']) { 	/* 如果要修改密码 ，则判断是否符合规则 */
        
		if (strlen($_POST['password'])<6) {
			$msg = '新密码长度不能小于6个字符。';
		} elseif (strlen($_POST['password'])>30) {
			$msg = '新密码长度不能超过30个字符。';
		} elseif (!preg_match('/^[A-Za-z0-9!@#\\$%\\^&\\*_]{4,16}$/',$_POST['password'])) {
			$msg = '密码是4-16个字符的数字、字母或!@#$%^&*_等字符的组合。';
		} elseif ($_POST['password'] != $_POST['password_repeat']) {
			$msg = '新密码两次输入不一致。';
		} 
	
	}else{
		$msg="原密码，新密码，重复输入三个字段不能为空";
	}

	
	if(!empty( $msg ) ){
		echo error( $msg );
		exit ;
	}


	$sql = "select * from zf_admin where id = '$admin_id'";
	$db= DB::instance();
	$user = $db->get_one( $sql );

	/* 设置密码  */
	$salt1 = substr( $user['salt'], 0,3);
	$salt2 = substr( $user['salt'], 3,3);
	$origin_pwd = md5($salt1.md5( $_POST['origin_pwd'] ).$salt2);

	if( $origin_pwd !== $user['password'] ){
		$msg = "原密码不正确，请重新输入"; 
		echo error( $msg );
		exit ;
	}


	/* 设置新密码 */
	$arr['password'] = $password ;

	$salt = rand(100000,999999);
	$salt1 = substr( $salt, 0,3);
	$salt2 = substr( $salt, 3,3);
	$arr['salt'] = $salt; 
	$arr['password'] = md5($salt1.md5( $_POST['password'] ).$salt2);

	$cond = 'id = '.$admin_id;
	$db = DB::instance();
	$rt = $db->update('zf_admin', $arr, $cond);
	if($rt){echo success($msg);}else{echo  error($msg);}
	exit;
	

}	
		
        



?>

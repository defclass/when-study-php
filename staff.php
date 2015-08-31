<?php
require_once('global.php');

$admin_id  = $_SESSION['id'];
$admin_name = $_SESSION['name'] ; 
$level_id = $_SESSION['level_id']; 


if(isset($_GET['action']))
{
	$dongzuo=$_GET['action'];
	switch ($dongzuo)
	{
	case $dongzuo=='query'://查询
		/* 获取权限数字 */
		$module_per_obj = modulePermission::instance();
		$per_num = $module_per_obj->getPermission($level_id, 'staff', 'query');
		if( $per_num == 0 ){
			echo error("您没有权限");
		}

		$sql = "select admin.*,
			level.level_name
			from zf_admin as admin
			inner join zf_admin_level as level on level.id = admin.level_id
			where admin.banned = 0 and admin.fid = '$admin_id'
			";


		/*增加分页功能*/
		$db = DB::instance();
		$total =count($db->get_all($sql));
		$numPerPage=22;//每页显示条数
		$pageNum=!empty($_POST['pageNum'])?$_POST['pageNum']:1;//当前第几页
		$begin=!empty($_POST['pageNum'])?(($_POST['pageNum']-1)*$numPerPage):0;//开始显示条数

		$sql .= " limit ".$begin.','.$numPerPage;
		$db = DB::instance();
		$staffs = $db->get_all( $sql );

		foreach( $staffs as &$staff ){
			foreach( $zf_admin_all as $v ){

				if( $staff['fid'] == $v['id']){
					$staff['f_name'] = $v['name']; 

				}
			}
			$staff['lasttime'] = date('Y-m-d H:i:s', $staff['lasttime']);
		}


		$param = 'uid={uid_user}';
		$tp->set_file('staff_list');
		$tp->p();
		break;

	case $dongzuo=='add'://新增
		if( $_GET['do'] == 'commit' ){

			$msg = "";
			if (!$_POST['user']) {
				$msg = '登陆名不能为空。';
			} elseif(!$_POST['name']){
				$msg = '姓名不能为空。';
			} elseif(strlen($_POST['name'])<4){
				$msg = '姓名不能少于2个字。';
			} elseif(strlen($_POST['name'])>30){
				$msg = '姓名不能多于10个字。';
			} elseif (strlen($_POST['user'])<4) {
				$msg = '登陆名长度不能小于4个字符。';
			} elseif (strlen($_POST['user'])>30) {
				$msg = '登陆名长度不能超过16个字符。';
			} elseif (!preg_match('/^(?![_0-9])[\w\x{4e00}-\x{9fa5}]{4,16}$/u',$_POST['user'])) {
				$msg ='登陆名是4-16个字符的中英文下划线_组成且第一个字符只能为中英文';

			} elseif (!$_POST['password']) {
				$msg = '密码不能为空。';
			} elseif (strlen($_POST['password'])<6) {
				$msg = '密码长度不能小于6个字符。';
			} elseif (strlen($_POST['password'])>30) {
				$msg = '密码长度不能超过30个字符。';
			} elseif (!preg_match('/^[A-Za-z0-9!@#\\$%\\^&\\*_]{4,16}$/',$_POST['password'])) {
				$msg = '密码是4-16个字符的数字、字母或!@#$%^&*_等字符的组合。';
			} elseif ($_POST['password'] != trim($_POST['repeat_pw'])) {
				$msg = '两次输入密码不一致。';
			} 

			if( !empty($msg)  ){
				echo  error($msg);
				exit;
			}

			if($_POST['level_id'] <= $level_id ){
				echo  error("创建人的级别必须高于被创建人的级别");
				exit;
			}

			//查询上级的分公司
			$sql = "select fgs_id  from zf_admin where id = ".$_POST['fid'];
			$db = DB::instance();
			$shangji = $db->get_one( $sql ); //上级信息

			$arr = array();
			$arr['user'] =$_POST['user']  ;
			$arr['name'] = $_POST['name'];
			$arr['password'] = $_POST['password'];
			$arr['fid'] = $_POST['fid'] ;
			$arr['tel'] = $_POST['tel'] ;
			$arr['phone'] = $_POST['phone'] ;
			$arr['ruzhi_time'] = strtotime($_POST['ruzhi_time']);
			$arr['level_id'] = $_POST['level_id'] ;
			$arr['lasttime'] = time();
			$arr['fgs_id'] = $shangji['fgs_id'];//新增的员工的分公司ID和他上级的分公司ID一样
			if(in_array($level_id,array(1,2))){//如果是分公司和总公司增加则不需要审核
				$arr['banned'] = 0;
			}else{
				$arr['banned'] = 1;
			}
			$arr['id'] = f_id();



			$salt = rand(100000,999999);
			$salt1 = substr( $salt, 0,3);
			$salt2 = substr( $salt, 3,3);
			$arr['salt'] = $salt; 
			$arr['password'] = md5($salt1.md5( $_POST['password'] ).$salt2);


			/* 设置IP  */
			$arr['lastip'] = GetIP();

			$db = DB::instance();
			$rt = $db->insert('zf_admin',$arr);
			if($rt){echo success($msg);}else{echo  error($msg);}
			exit;	

		}
		if( $_GET['do'] == '' ){
			$admin_id  = $_SESSION['id'];
			$level_id = $_SESSION['level_id']; 
			/* 获取权限数字 */
			$module_per_obj = modulePermission::instance();
			$per_num = $module_per_obj->getPermission($level_id, 'staff', 'query');

			/*
			 * 下属名字
			 */
			$sql = "select * from zf_admin ";
			$db = DB::instance();
			$zf_admin_all = $db->get_all( $sql );

			if(in_array($level_id,array(1,2,3,4,5))) {
				$id_array = array();
				$xs_name = array();
				/* $id_array  指的是所有的 下属的id */
				scanNodeOfTree( $zf_admin_all, $id_array, $admin_id);

				/* 加入自已的ID */
				array_unshift($id_array,$admin_id);
				foreach( $id_array as $id ){
					foreach( $zf_admin_all as $row){
						if( $id == $row['id'] ){
							$xs_name[] =array('id'=> $id,'name' => $row['name']);
						}
					}
				}

			}


			/* 该员工权限等级的下属等级 */
			$sql = "select id, level_name from zf_admin_level where id > $level_id";
			$db = DB::instance();
			$staff_level = $db->get_all( $sql );



			$tp->set_file('staff_new');
			$tp->p();
		}

		break;

	case $dongzuo=='del'://新增
		$uid = $_GET['uid'];
		$db = DB::instance();
		$cond = " id = $uid ";
		$rt = $db->delete('zf_admin', $cond);
		if($rt){echo success($msg);}else{echo  error($msg);}exit;
		break;

	case $dongzuo=='update'://更新
		if( $_GET['do'] == 'commit' ){

			foreach( $_POST as  &$v ){
				$v = addslashes(trim(stripslashes($v)));
			}


			$msg = "";
			if (!$_POST['user']) {
				$msg = '登陆名不能为空。';
			} elseif(!$_POST['name']){
				$msg = '姓名不能为空。';
			} elseif(strlen($_POST['name'])<4){
				$msg = '姓名不能少于2个字。';
			} elseif(strlen($_POST['name'])>30){
				$msg = '姓名不能多于10个字。';
			} elseif (strlen($_POST['user'])<4) {
				$msg = '登陆名长度不能小于4个字符。';
			} elseif (strlen($_POST['user'])>30) {
				$msg = '登陆名长度不能超过16个字符。';
			} elseif (!preg_match('/^(?![_0-9])[\w\x{4e00}-\x{9fa5}]{4,16}$/u',$_POST['user'])) {
				$msg ='登陆名是4-16个字符的中英文下划线_组成且第一个字符只能为中英文';

			} elseif ($_POST['password'] || $_POST['repeat_pw']) { 	/* 如果要修改密码 ，则判断是否符合规则 */

				if (strlen($_POST['password'])<6) {
					$msg = '密码长度不能小于6个字符。';
				} elseif (strlen($_POST['password'])>30) {
					$msg = '密码长度不能超过30个字符。';
				} elseif (!preg_match('/^[A-Za-z0-9!@#\\$%\\^&\\*_]{4,16}$/',$_POST['password'])) {
					$msg = '密码是4-16个字符的数字、字母或!@#$%^&*_等字符的组合。';
				} elseif ($_POST['password'] != trim($_POST['repeat_pw'])) {
					$msg = '两次输入密码不一致。';
				} 

			}

			/* 判断输入是否合法 */
			if( !empty( $msg ) ){
				echo  error($msg);
				exit;
			}
			if($_POST['level_id'] <= $level_id ){
				echo  error("修改信息的员工级别必须高于被修改信息的员工级别");
				exit;
			}

			//查询上级的分公司
			$sql = "select fgs_id  from zf_admin where id = ".$_POST['fid'];
			$db = DB::instance();
			$shangji = $db->get_one( $sql ); //上级信息


			$arr = array();
			if( $_POST['password'] ) {

				$arr['password'] = $_POST['password'];
				/* 设置密码  */
				$salt = rand(100000,999999);
				$salt1 = substr( $salt, 0,3);
				$salt2 = substr( $salt, 3,3);
				$arr['salt'] = $salt; 
				$arr['password'] = md5($salt1.md5( $_POST['password'] ).$salt2);
			}

			$arr['user'] =$_POST['user']  ;
			$arr['name'] = $_POST['name'];
			$arr['fid'] = $_POST['fid'] ;
			$arr['tel'] = $_POST['tel'] ;
			$arr['phone'] = $_POST['phone'] ;
			$arr['level_id'] = $_POST['level_id'] ;
			$arr['ruzhi_time'] = strtotime($_POST['ruzhi_time']);
			$arr['fgs_id'] = $shangji['fgs_id'];//新增的员工的分公司ID和他上级的分公司ID一样

			$db = DB::instance();
			$cond = "id = ".$_GET['cid']; 

			$rt = $db->update('zf_admin',$arr, $cond );
			if($rt){echo success($msg);}else{echo  error($msg);}
			exit;
		}

		if( $_GET['do'] == '' ){
			$admin_id  = $_SESSION['id'];
			$level_id = $_SESSION['level_id']; 

			/* 传递过来该客户的ID ，查询该客户的信息 */
			$staff_id = $_GET['uid'] ;

			$sql = "select * from zf_admin where id = '$staff_id' ";
			$db = DB::instance();
			$staff = $db->get_one($sql);

			$staff['ruzhi_time'] = date("Y-m-d",$staff['ruzhi_time']);




			/* 获取权限数字 */
			$module_per_obj = modulePermission::instance();
			$per_num = $module_per_obj->getPermission($level_id, 'staff', 'query');


			/*
			 * 下属名字
			 */

			$sql = "select * from zf_admin ";
			$db = DB::instance();
			$zf_admin_all = $db->get_all( $sql );
			if( in_array($level_id,array(1,2,3,4,5))){
				$xs_name = array();
				/* $id_array  指的是所有的 下属的id */
				scanNodeOfTree( $zf_admin_all, $id_array, $admin_id);

				/* 加入自已的ID */
				array_unshift($id_array,$admin_id);

				/* 将当前用户的fid排第一位 */
				array_unshift($id_array,$staff['fid']);

				foreach( $id_array as $id ){
					foreach( $zf_admin_all as $row){
						if( $id == $row['id'] ){
							$xs_name[] =array('id'=> $id,'name' => $row['name']);
						}
					}
				}
			}




			/* 该员工权限等级的下属等级 */
			$sql = "select id, level_name from zf_admin_level where id > '$level_id'  ";
			$db = DB::instance();
			$staff_level = $db->get_all( $sql );

			/* 找到该用户的权限，并将其放在第一位 */
			foreach( $staff_level as $v ){
				if( $v['id'] ==  $staff['level_id'] ) {

					$current_level = array('id'=> $v['id'] ,'level_name'=>$v['level_name']);

				}
			}

			array_unshift($staff_level,$current_level);


			$param = 'cid='.$staff['id'];
			$tp->set_file('staff_edit');
			$tp->p();
		}
		break;

	case $dongzuo=='search'://搜索
		$module_per_obj = modulePermission::instance();
		$per_num = $module_per_obj->getPermission($level_id, 'staff', 'search');
		if( $per_num == 0 ){
			echo error("您没有权限");
		}

		$sql = "select admin.*,
			level.level_name
			from zf_admin as admin
			inner join zf_admin_level as level on level.id = admin.level_id
			where admin.fid = '$admin_id'
			";
		foreach( $_POST as  $k=>&$v ){
			$v = addslashes(trim(stripslashes($v)));
		}
		$search_admin_name = $_POST['admin_name'];
		if (!empty($search_admin_name)){

			$sql .= " and admin.name = '$search_admin_name'";
		}

		/*增加分页功能*/
		$db = DB::instance();
		$total =count($db->get_all($sql));
		$numPerPage=22;//每页显示条数
		$pageNum=!empty($_POST['pageNum'])?$_POST['pageNum']:1;//当前第几页
		$begin=!empty($_POST['pageNum'])?(($_POST['pageNum']-1)*$numPerPage):0;//开始显示条数

		$db = DB::instance();
		$staffs = $db->get_all( $sql );

		foreach( $staffs as &$staff ){

			foreach( $zf_admin_all as $v ){

				if( $staff['fid'] == $v['id']){
					$staff['f_name'] = $v['name']; 

				}
			}
			$staff['lasttime'] = date('Y-m-d H:i:s', $staff['lasttime']);
		}


		$param = 'uid={uid_user}';
		$tp->set_file('staff_list');
		$tp->p();
		break;
	default:
		echo 'other';
		break;
	}
}




?>

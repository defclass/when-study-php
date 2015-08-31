<?php
require_once('global.php');
if( $_GET['action'] == '' ){
	$admin_id  = $_SESSION['id'];
 	$level_id = $_SESSION['level_id']; 
	/* 传递过来该客户的ID ，查询该客户的信息 */
	$cust_id = $_GET['uid'] ;
	$sql = "select * from zf_customer where id = $cust_id ";
	$db = DB::instance();
	$cust = $db->get_one($sql);
	/* 最后拜访时间，时间戳转成字符串 */
	$cust['last_visit_time'] = date('Y-m-d',$cust['last_visit_time']);
/* 获取权限数字 */
	$module_per_obj = modulePermission::instance();
	$per_num = $module_per_obj->getPermission($level_id, 'customer', 'query');
	/*
	 * 下属名字
	 */
	$sql = "select * from zf_admin ";
	$zf_admin_all = $db->get_all( $sql );
	$xs_name = array();
	if( $per_num == 2 || $per_num ==3 ) {
		/* $id_array  指的是所有的 下属的id */
		scanNodeOfTree( $zf_admin_all, $id_array, $admin_id);
		/* 加入自已的ID */
		array_unshift($id_array,$admin_id);
		/* 将原来的所属业务员默认放第一位 */
		array_unshift($id_array,$cust['admin_id']);
		foreach( $id_array as $id ){
			foreach( $zf_admin_all as $row){
				if( $id == $row['id'] ){
					$xs_name[] =array('id'=> $id,'name' => $row['name']);
				}
			}
		}
	}
	/* 传递页面参数 */
	if( $_GET['public_info'] == 1 ) $public_info = 1;
	else $public_info = 0;
	$param = 'cid='.$cust['id'];
	$tp->set_file('info_edit');
	$tp->p();
}
if( $_GET['action'] == 'edit_cust' ){
	foreach( $_POST as  &$v ){
		$v = addslashes(trim(stripslashes($v)));
	}
	/* $arr = arrary(); */
	$arr = array();
	$arr['user_name'] = $_POST['zs_name'];
	$arr['user_sex'] = $_POST['sex'] ;
	$arr['user_address'] = $_POST['address'] ;
	$arr['profession'] = $_POST['zy'] ;
	$arr['income_level'] = $_POST['nsr'];
	$arr['sf_tz'] = $_POST['sf_tz'] ;
	$arr['user_tel'] = $_POST['mobile'] ;
	$arr['user_tel2'] = $_POST['mobile'] ;
	$arr['user_phone'] =$_POST['tel']  ;
	$arr['user_fax'] = $_POST['fax'];
	$arr['user_email'] = $_POST['e-mail'];
	$arr['qq'] = $_POST['qq'] ;
	$arr['msn'] = $_POST['msn'] ;
	$arr['bir'] = $_POST['birthday'] ;
	$arr['favorite'] = $_POST['ah'] ;
	$arr['last_visit_time'] = strtotime($_POST['time_bf']);
	$arr['br_last_visit'] = strtotime($_POST['time_bf']);
	$arr['remark'] = $_POST['remark'] ;
	if( !empty($_POST['admin_id']) ){ 
		$arr['admin_id'] = $_POST['admin_id'] ;
	}
	$db = DB::instance();
	$cond = "id = ".$_GET['cid']; 
	$rt = $db->update('zf_customer',$arr, $cond );
	if($rt){echo success($msg);}else{echo  error($msg);}
	exit;
}
?>

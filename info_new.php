<?php
require_once('global.php');

if( $_GET['action'] == 'add_cust' ){

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
	$arr['bir'] = strtotime($_POST['birthday']);
	
	$arr['favorite'] = $_POST['ah'] ;
	$arr['remark'] = $_POST['remark'] ;
	$arr['creat_time'] = time();
	$arr['id'] = f_id();

	if( isset($_POST['admin_id'])){
		$arr['admin_id'] = $_POST['admin_id'];
	}elseif($public_info !== 1){
		$arr['admin_id'] = $_SESSION['id'];
	}

	$db = DB::instance();
	$rt = $db->insert('zf_customer',$arr);
	if($rt){echo success($msg);}else{echo  error($msg);}
	exit;
	
}

if( $_GET['action'] == '' ){
	$admin_id  = $_SESSION['id'];
 	$level_id = $_SESSION['level_id']; 
         
	/* 获取查的权限数字 */
	$module_per_obj = modulePermission::instance();
	$per_num = $module_per_obj->getPermission($level_id, 'customer', 'query');

	/*
	 * 下属名字
	 */
	$sql = "select * from zf_admin ";
	$db =DB::instance();
 	$zf_admin_all = $db->get_all( $sql );
	

	if( $per_num == 2 ||  $per_num == 3 ) {
		/* 下属名字的 */
		$xs_name = array();
	
		
		/* $id_array  指的是所有的 下属的id */
		$id_array = array();
		scanNodeOfTree( $zf_admin_all, $id_array, $admin_id);

		
		/* 加入自已的ID */
		array_unshift($id_array,$admin_id);


		$xs_name[] = array('id'=> 0, 'name'=>"无");
		foreach( $id_array as $id ){
			foreach( $zf_admin_all as $row){
				if( $id == $row['id'] ){
					$xs_name[] =array('id'=> $id,'name' => $row['name']);
				}
			}
		}
	}

        if(!empty($_GET['public_info'])) $public_info = 1;
	else $public_info = 0;

	$tp->set_file('info_new');
	$tp->p();
}









?>
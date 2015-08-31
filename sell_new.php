<?php
require_once("global.php");
$admin_id  = $_SESSION['id'];
$level_id = $_SESSION['level_id']; 

if( $_GET['action'] == 'add_visit' ){

	foreach( $_POST as  &$v ){
		$v = addslashes(trim(stripslashes($v)));
	}
	$arr = array();
	$arr['user_id'] = $_POST['user_id'];

	if( isset($_POST['admin_id'])){
		$arr['admin_id'] = $_POST['admin_id'];
	} else{
		$arr['admin_id'] = $_SESSION['id'];
	}
	$arr['cp_id'] = $_POST['cp_id'];

	$arr['visit_type'] = $_POST['visit_type'];

	$arr['visit_time'] = strtotime($_POST['visit_time']);

	$arr['remark'] = $_POST['remark'];

	 $arr['place'] = $_POST['place'];
	
	$arr['create_time'] = time();

	$arr['id'] = f_id();

	

	/* 更新客户的最后拜访时间 */
	$arr_1['last_visit_time'] = $arr['visit_time'];

	$db = DB::instance();
	$rt = $db->insert('zf_baifang',$arr);

	$cond = " id = ".$_POST['user_id'];

	$rt1 = $db->update('zf_customer',$arr_1,$cond);
	if($rt && $rt1){echo success($msg);}else{echo  error($msg);}
	exit;
}else{

/* 获取权限数字 */
	$module_per_obj = modulePermission::instance();
	$per_num = $module_per_obj->getPermission($level_id, 'visit', 'query');


/* 查询用户表  */
	$sql = "select * from zf_admin ";
	$db = DB::instance();
	$zf_admin_all = $db->get_all( $sql );
	

/* 查询顾客表 */

	$sql_cust = "select id,user_name from zf_customer where";

	if( $per_num == 1){

		/* 找到该用户下的所有顾客  */
		$sql_cust .= "admin_id = '$admin_id'" ;
	}
	if( $per_num == 2 || $per_num ==3 ) {
		/* 下属ID与姓名 的数组 */
		$xs_name = array();

		/* $sql_cust 语句的where 子句的组件 */
		$where = array();

		/* $id_array  指的是所有的 下属的id */
		scanNodeOfTree( $zf_admin_all, $id_array, $admin_id);

						
		/* 加入自已的ID */
		array_unshift($id_array,$admin_id);

		foreach( $id_array as $id ){
		
			/* 组装下属与ID 的数组  */
			foreach( $zf_admin_all as $row){
				if( $id == $row['id'] ){
					$xs_name[] =array('id'=> $id,'name' => $row['name']);
				}
			}

			/* 组装 $sql_cust 语句 where 子句组件 */
			$where[] = " admin_id = " .$id." ";
		
		}
	}

/* 组装 $sql_cust 语句 where 子句组件 */
	$sql_cust .= implode(' or ' ,$where );

/* 查询该员工下的所有顾客 */
	$db = DB::instance();
	$custs = $db->get_all( $sql_cust );



	$visit_time = date('Y-m-d H:i', time());


	$sql = "select * from zf_product";
	$db = DB::instance();
	$products = $db->get_all( $sql );


	$tp->set_file('sell_new');
	$tp->p();
}
?>
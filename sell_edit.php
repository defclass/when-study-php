<?php
require_once('global.php');

$admin_id  = $_SESSION['id'];
$level_id = $_SESSION['level_id']; 

if( $_GET['action'] == 'edit_commit'){
	
	$vid = $_GET['vid'];

	foreach( $_POST as  &$v ){
		$v = addslashes(trim(stripslashes($v)));
	}
	
	if( !isset($_POST['admin_id'] ) ){
		 $_POST['admin_id'] = $admin_id ;
	}
	
	$_POST['visit_time'] = strtotime($_POST['visit_time']); 
	
	$cond = "id =".$vid;
        
	reset( $_POST );

	$db = DB::instance();
	$rt = $db->update('zf_baifang', $_POST, $cond);
	if($rt){echo success($msg);}else{echo  error($msg);}
	exit;


}

if( $_GET['action'] == '' ){
         
/* 传递过来该条拜访的ID ，查询该客户的信息 */
	$bf_id = $_GET['vid'] ;

	$sql = "select product.name as pro_name, 
             product.id as pro_id,
             admin.name as admin_name,
             admin.id as admin_id,
             cust.user_name as cust_name,
             cust.id as cust_id,
             baifang.visit_type,
             baifang.place,
             baifang.visit_time,
             baifang.remark ,
             baifang.is_success
       from zf_baifang as baifang,
             zf_customer as cust,
             zf_product as product,
             zf_admin as admin
       where baifang.id = '$bf_id' 
             and baifang.cp_id = product.id
             and baifang.admin_id = admin.id
             and baifang.user_id = cust.id  ";

	$db = DB::instance();
	$baifang = $db->get_one($sql);
	$baifang['visit_time'] = date('Y-m-d',$baifang['visit_time']);



/* /\* 获取权限数字 *\/ */
/* 	$module_per_obj = modulePermission::instance(); */
/* 	$per_num = $module_per_obj->getPermission($level_id, 'visit', 'query'); */


/* /\* 查询用户表  *\/ */
/* 	$sql = "select * from zf_admin "; */
/* 	$db = DB::instance(); */
/* 	$zf_admin_all = $db->get_all( $sql ); */
	

/* /\* 查询顾客表 *\/ */
/* 	$sql_cust = "select id,user_name from zf_customer where"; */
/* 	if( $per_num == 1){ */

/* 		/\* 找到该用户下的所有顾客  *\/ */
/* 		$sql_cust .= "admin_id = '$admin_id'" ; */
/* 	} */
/* 	if( $per_num == 2 || $per_num ==3 ) { */
/* 		/\* 下属ID与姓名 的数组 *\/ */
/* 		$xs_name = array(); */

/* 		/\* $sql_cust 语句的where 子句的组件 *\/ */
/* 		$where = array(); */

/* 		/\* $id_array  指的是所有的 下属的id *\/ */
/* 		scanNodeOfTree( $zf_admin_all, $id_array, $admin_id); */

						
/* 		/\* 加入自已的ID *\/ */
/* 		array_unshift($id_array,$admin_id); */

/* 		foreach( $id_array as $id ){ */
		
/* 			/\* 组装下属与ID 的数组  *\/ */
/* 			foreach( $zf_admin_all as $row){ */
/* 				if( $id == $row['id'] ){ */
/* 					$xs_name[] =array('id'=> $id,'name' => $row['name']); */
/* 				} */
/* 			} */

/* 			/\* 组装 $sql_cust 语句 where 子句组件 *\/ */
/* 			$where[] = " admin_id = " .$id." "; */
		
/* 		} */
/* 	} */

/* /\* 组装 $sql_cust 语句 where 子句组件 *\/ */
/* 	$sql_cust .= implode(' or ' ,$where ); */



/* /\* 查询该员工下的所有顾客 *\/ */
/* 	$db = DB::instance(); */
/* 	$custs = $db->get_all( $sql_cust ); */
/* 	$arr = array('id' => $baifang['cust_id'], 'user_name'=> $baifang['cust_name']); */
/* 	array_unshift( $custs, $arr); */


/* 	$visit_time = date('Y-m-d H:i', time()); */

/* 查询所有 product */
	$sql = "select id, name from zf_product";
	$db = DB::instance();
	$products = $db->get_all( $sql );
	$arr = array('id' => $baifang['pro_id'], 'name'=> $baifang['pro_name']);
	array_unshift( $products, $arr);

/* /\* 查找所有 员工 *\/ */
/* 	$arr = array('id' => $baifang['admin_id'], 'name'=> $baifang['admin_name']); */
/* 	array_unshift( $xs_name, $arr); */


	$bf_id = 'vid='.$bf_id;
	$tp->set_file('sell_edit');
	$tp->p();

}
?>
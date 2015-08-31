<?php
require_once("global.php");


$admin_id  = $_SESSION['id'];
$name = $_SESSION['name'] ; 
$level_id = $_SESSION['level_id']; 

/* 获取权限数字 */
$module_per_obj = modulePermission::instance();
$per_num = $module_per_obj->getPermission($level_id, 'visit', 'query');


$sql="select baifang.id,
             cust.id as cust_id ,
             cust.user_name,
             admin.id as admin_id,
             admin.name as admin_name, 
             product.name as product_name,
             baifang.visit_type, 
             baifang.place, 
             baifang.visit_time,
             baifang.remark          
      from zf_customer as cust, 
           zf_admin as admin,
           zf_product as product, 
           zf_baifang as baifang
      where cust.id = baifang.user_id 
      and admin.id = baifang.admin_id 
      and product.id = baifang.cp_id ";


/* 只能查询本人 */
if( $per_num == 1 ){
	$sql .= "and baifang.admin_id = '$admin_id'";

	
}
/* 能查询下属 */
if( $per_num == 2 ||  $per_num ==3){
	$sql_admin = "select * from zf_admin ";
	$db = DB::instance();
	$zf_admin_all = $db->get_all( $sql_admin );
	$array = array();


	$id_array = array();
	/* $id_array  指的是所有的 下属的id */
	scanNodeOfTree( $zf_admin_all, $id_array, $admin_id);

		
	/* 加入自已的ID */
	array_unshift($id_array, $admin_id);

	$sql .= "and (" ;

	foreach( $id_array as &$v ){
		$v = "baifang.admin_id = $v";
	}
	

	/* 形如（ id = 2 or id =6 ） */
	$sql .= implode(' or ', $id_array);

	$sql .= ")";
		
}


if($_GET['action'] == "search"){
       
	foreach( $_POST as  $k=>&$v ){
		$v = addslashes(trim(stripslashes($v)));

		/* 时间转换成时间戳 */
		if($k == 'time_start' || $k == 'time_over') $v = strtotime ($v);
	}


	$admin_name = $_POST['admin_name'];
	$user_name = $_POST['user_name'];
	$user_tel = $_POST['user_tel'];
	$time_start = $_POST['time_start'];
	$time_over = $_POST['time_over'];


	if (!empty($admin_name)){
		$sql .= " and admin.name = '$admin_name'";
	}


	if (!empty($user_name)){
		$sql .= " and cust.user_name = '$user_name'";
	}

	if (!empty($user_tel)){
		$sql .= " and cust.user_tel = '$user_tel'";
	}

	if (!empty($time_start)){
		$sql .= " and cust.last_visit_time > '$time_start'";
	}

	if (!empty($time_over)){
		$sql .= " and cust.last_visit_time < '$time_over'";
	}

}

$sql .= "order by baifang.visit_time desc";
$db = DB::instance();
$bf_recs = $db->get_all( $sql );

/*============================== 分页  =======================================  */
//$sql .= "limit 0, 22";



/* 总记录数 */
$total =  count( $bf_recs );

/* 每页显示数 */
$per_num = 22;

/* 总页数  */
$page_total = ceil($total/$per_num);


/* 请求页，默认1  */
if( isset($_POST['pageNum'])){
	$current_page = $_POST['pageNum'] ;
}else{ $current_page = 1; }

$start_rec = ($current_page -1) * $per_num;

if(  $current_page < $page_total ) {
	$bf_recs = array_slice($bf_recs, $start_rec, $per_num);
}else{
	$bf_recs = array_slice($bf_recs,$start_rec);
}

/*======================== 处理数值  ===============================================  */


foreach( $bf_recs as &$v ){
	/* 转换时间戳 */
	$v['visit_time'] = date('Y-m-d ', $v['visit_time']);

	/* 限制字符串 为40个字 */
       	$v['remark'] = mb_substr($v['remark'],0,20,'utf-8');
}


$param = 'vid={sid_user}';
$tp->set_file('sell_list');
$tp->p();

?>
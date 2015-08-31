<?php
require_once('global.php');
$admin_id  = $_SESSION['id'];
$name = $_SESSION['name'] ; 
$level_id = $_SESSION['level_id']; 


$sql = "select name from zf_product";
$db = DB::instance();
$products = $db->get_all( $sql );


$module_per_obj = modulePermission::instance();
$per_num = $module_per_obj->getPermission($level_id, 'visit', 'query');


$sql="select cust.user_name,
              bf.visit_time,
              bf.place,
              pd.name as pd_name,
              pd.jf   
       from  zf_customer as cust,
             zf_admin as admin,
             zf_baifang  as bf,
             zf_product as pd
       where  bf.admin_id = admin.id
             and  bf.user_id = cust.id
             and  bf.cp_id = pd.id ";


/* 只能查询本人 */
if( $per_num == 1 ){
	$sql .= "and bf.admin_id = '$admin_id'";

	
}
/* 能查询下属 */
if( $per_num == 2 || $per_num ==3){
	$sql_admin = "select * from zf_admin ";
	$db = DB::instance();
	$zf_admin_all = $db->get_all( $sql_admin );

	/* $id_array  指的是所有的 下属的id */
	scanNodeOfTree( $zf_admin_all, $id_array, $admin_id);

	
	$id_array = array();
	/* 加入自已的ID */
	array_unshift($id_array, $admin_id);

	$sql .= "and (" ;

	foreach( $id_array as &$v ){
		$v = "bf.admin_id = $v";
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

$db = DB::instance();
$list_raw = $db->get_all( $sql );



$gk_lists = array();


/* 组装成模板可用的数组 */
foreach( $list_raw as $raw ){
	$name = $raw['user_name'];
	

	/* 如果数组中没有该顾客，则初始化 */
	if ( !isset( $gk_lists[$name] ) ){
		$gk_lists[$name] = array();
		$gk = &$gk_lists[$name];

		foreach( $products as $pd ){
			$pd_name = $pd['name'];
			$gk[$pd_name] = 0;
		}

		$gk['jf'] = 0;
	}
	
	if( !isset( $gk ) ) $gk = &$gk_lists[$name];
	
	/* 添加该产品的情况 */
	$pd_name = $raw['pd_name'];
	$gk[$pd_name] = 1;
	
	
	if( !isset( $gk['visit_time'] ) || $raw['visit_time'] > $gk['visit_time'] ){
		$gk['visit_time'] = $raw['visit_time'];
		$gk['place'] = $raw['place'];
	}  
	
	$gk['jf'] += $raw['jf'];
      
	
}



foreach( $gk_lists as &$v ){
	$v['visit_time'] = date('Y-m-d H:i');
}


/*============================== 分页  =======================================  */
//$sql .= "limit 0, 22";



/* 总记录数 */
$total =  count( $gk_lists );

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
	$gk_lists = array_slice($gk_lists, $start_rec, $per_num);

}else{
	$gk_lists = array_slice($gk_lists,$start_rec);
}


$tp->set_file('sell_gaikuang');
$tp->p();

?>
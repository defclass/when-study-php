<?php
require_once( 'global.php ');

$admin_id  = $_SESSION['id'];
$name = $_SESSION['name'] ; 
$level_id = $_SESSION['level_id']; 


/* 获取权限数字 */
$module_per_obj = modulePermission::instance();
$per_num = $module_per_obj->getPermission($level_id, 'customer', 'query');

if( $per_num == 0){
	die("您没有权限查看");
	exit;
}

$sql = "select cust.id,
               cust.user_name,
               cust.user_sex,
               cust.profession,
               cust.income_level,
               cust.sf_tz,
               cust.user_tel,
               cust.user_email,
               cust.qq,
               cust.creat_time,
               cust.last_visit_time,
               cust.remark
        from  zf_customer as cust
        where cust.admin_id = 0
         ";


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


$sql .= " order by cust.last_visit_time desc ";

/*============================== 分页  =======================================  */

/*增加分页功能*/
$db = DB::instance();
$total =count($db->get_all($sql));
$numPerPage=22;//每页显示条数
$pageNum=!empty($_POST['pageNum'])?$_POST['pageNum']:1;//当前第几页
$begin=!empty($_POST['pageNum'])?(($_POST['pageNum']-1)*$numPerPage):0;//开始显示条数

$sql .= " limit ".$begin.','.$numPerPage;

////$sql .= "limit 0, 22";

//[> 总记录数 <]
//$total =  count( $custs );

//[> 每页显示数 <]
//$per_num = 20;

//[> 总页数  <]
//$page_total = ceil($total/$per_num);


//[> 请求页，默认1  <]
//if( isset($_POST['pageNum'])){
	//$current_page = $_POST['pageNum'] ;
//}else{ $current_page = 1; }

//$start_rec = ($current_page -1) * $per_num;

//if(  $current_page < $page_total ) {
	//$custs = array_slice($custs, $start_rec, $per_num);
//}else{
	//$custs = array_slice($custs,$start_rec);
//}

/*======================== 处理数值  ===============================================  */


$db = DB::instance();
$custs = $db->get_all( $sql );

foreach( $custs as &$cust ){
	
	/* 最后拜访时间，时间戳转成字符串 */
	$cust['last_visit_time'] = date('Y-m-d',$cust['last_visit_time']);

	/* 创建时间，时间戳转成字符串 */
	$cust['creat_time'] = date('Y-m-d',$cust['creat_time']);


	/* 限制字符串 为8个字 */
       	$cust['remark'] = mb_substr($cust['remark'],0,8,'utf-8');

	/* 收入水平，加万元 */
	$cust['income_level'] .= "万元";
}

/* 该页面要设置抢客户按纽 */
$public_info =  1;

$param = 'uid={sid_user}&public_info=1';
$tp->set_file('publicCust_list');
$tp->p();





?>

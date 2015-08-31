<?php
require_once( 'global.php ');

$admin_id  = $_SESSION['id'];
$name = $_SESSION['name'] ; 
$level_id = $_SESSION['level_id']; 

/* 获取权限数字 */
$module_per_obj = modulePermission::instance();
$per_num = $module_per_obj->getPermission($level_id, 'customer', 'query');




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
	admin.id as admin_id,
	admin.name as admin_name,
	cust.last_visit_time,
	cust.remark
	from zf_admin as admin,
	zf_customer as cust
	where cust.admin_id = admin.id ";

/* 查出相应库的 名单 */

if( !empty($_GET['ku_id'])){

	$sql .= " and cust.pd_id = " .$_GET['ku_id'] ;
}

/*
 *        [> 选出属于该库的顾客名单 <]
 *        $sql_ku = "select bf.user_id,
 *                pd.sort_id
 *                from zf_baifang as bf,
 *                zf_product as pd 
 *                where bf.cp_id = pd.id 
 *                and bf.is_success = 1
 *                and bf.admin_id = '$admin_id'
 *                ";
 *
 *        $db = DB::instance();
 *        $user_raw = $db->get_all( $sql_ku );
 *
 *        [> user_arr 记录了所有拜访记录中该客户的最高产品ID <]
 *        $user_arr = array();
 *        $inner_arr = array();
 *        foreach( $user_raw as $user){
 *                $key = $user['user_id'] ;
 *                [> 假如为空，则初始化该值；假如当前的sort_id大于目前的sort_id 则将目前的sort_id替换原来的值 <]
 *                if(empty($user_arr[$key]) ||  $user_arr[$key] < $user['sort_id']) $user_arr[$key] = $user['sort_id'];
 *
 *        }
 *        $ku_id = $_GET['ku_id'];
 *
 *        [> cust_ids 是装有成功记录的最高产品ID <]
 *        $cust_ids = array();
 *        foreach($user_arr as $cu=>$pid ){
 *                if($pid == $ku_id ) $cust_ids[] = $cu ;
 *
 *        }	
 *   
 *        if( !empty( $cust_ids )){
 *                $sql .= " and ( " ;
 *
 *                foreach( $cust_ids as &$cust_id ){
 *                        $cust_id = "cust.id = ". $cust_id;
 *                }
 *
 *                [> 形如（ id = 2 or id =6 ） <]
 *                $sql .= implode(' or ', $cust_ids);
 *
 *                $sql .= ")";
 *
 *        
 *        }elseif($ku_id == 1 ){//选出待邀约的客户
 *                $dyy = "select bf.user_id,
 *                        pd.sort_id
 *                        from zf_baifang as bf,
 *                        zf_product as pd 
 *                        where bf.cp_id = pd.id 
 *                        and bf.admin_id = '$admin_id'  ";
 *                $db = DB::instance();
 *        	$dyy_raw = $db->get_all( $dyy );	
 *       
 *        
 *        }else{		[> 如果该ku_id 没有记录，则没有记录了  <]
 *                
 *                $param = 'uid={sid_user}';
 *                $tp->set_file('info_list');
 *                $tp->p();
 *                exit;
 *        }
 */



/* 只能查询本人 */
if( $per_num == 1 ){
	$sql .= " and admin_id = '$admin_id' ";

	/* 能查询下属 */
}

if( $per_num ==2 || $per_num ==3 ){
	$sql_admin = "select * from zf_admin ";
	$db = DB::instance();
	$zf_admin_all = $db->get_all( $sql_admin );
	$array = array();


	$id_array = array();

	/* $id_array  指的是所有的 下属的id */
	scanNodeOfTree( $zf_admin_all, $id_array, $admin_id);

	/* 加入自已的ID */
	array_unshift($id_array, $admin_id);


	$sql .= " and (" ;

	foreach( $id_array as &$v ){
		$v = "admin.id = $v";
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
		$sql .= " and (cust.user_tel = '$user_tel' or cust.user_tel2 = '$user_tel') ";
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
//$sql .= "limit 0, 22";
/*增加分页功能*/
$db = DB::instance();
$total =count($db->get_all($sql));
$numPerPage=22;//每页显示条数
$pageNum=!empty($_POST['pageNum'])?$_POST['pageNum']:1;//当前第几页
$begin=!empty($_POST['pageNum'])?(($_POST['pageNum']-1)*$numPerPage):0;//开始显示条数
$sql .= " limit ".$begin.','.$numPerPage;


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




$param = 'uid={sid_user}';
$tp->set_file('info_list');
$tp->p();





?>

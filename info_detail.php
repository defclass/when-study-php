<?php
require_once('global.php');

$admin_id  = $_SESSION['id'];
$cid = $_GET['uid'];

/* 添加拜访记录  */
if( $_GET['action'] == 'add_baifang' ){

	foreach( $_POST as  &$v ){
		$v = addslashes(trim(stripslashes($v)));
	}
	$arr = array();

	$arr['user_id'] = $cid ;

	$arr['admin_id'] = $_SESSION['id'];

	/*
	 * 数据库中的sort_id 与 目前拜访记录中的sort_id进行比较，目前拜访记录中的sort_id是否比数据库中的大
	 */
	list($arr['cp_id'], $new_sort_id, $origin_id, $origin_sort_id) = explode('_', $_POST['cp_id']);

	$arr['visit_type'] = $_POST['visit_type'];

	$arr['visit_time'] = strtotime($_POST['visit_time']);

	$arr['remark'] = $_POST['remark'];

	$arr['is_success'] = $_POST['is_success'];

	$arr['place'] = $_POST['place'];


	$arr['id'] = f_id();
	

	/* 插入新记录 */
	$db = DB::instance();
	$rt = $db->insert('zf_baifang',$arr);

	
	/* 更新客户表 */

	/* 更新客户的最后拜访时间 */
	$arr_1['last_visit_time'] = $arr['visit_time'];
	$arr_1['br_last_visit'] = $arr['visit_time'];

	$cond = " id = ".$cid;

	//目前拜访记录中的sort_id是否比数据库中的大,且is_success为成功时
	if( $new_sort_id > $origin_sort_id && $_POST['is_success'] == 1){
		$arr_1['pd_id'] =  $arr['cp_id'];
	}

	
	$rt1 = $db->update('zf_customer',$arr_1,$cond);


	if($rt && $rt1){echo success($msg);}else{echo  error($msg);}
	exit;
}

if($_GET['action'] == ""){
	if( !empty($_GET['public_info'])) {
		$public_info = 1; /* 公开库标志 */
	}else{
		$public_info = 0;
	}
	/*=============================== 客户详情 ================================ */
	$sql = "select admin.name as admin_name,
            cust.id,
            cust.user_email,
            cust.user_name,
            cust.user_sex,
            cust.user_address,
            cust.qq,
            cust.msn,
            cust.user_tel,
            cust.user_tel2,
            cust.user_phone,
            cust.user_fax,
            cust.profession,
            cust.income_level,
            cust.sf_tz,
            cust.bir,
            cust.favorite,
            cust.creat_time,
            cust.remark,
            cust.last_visit_time,
	    cust.pd_id,
	    pd.sort_id
	 from zf_customer as cust
	left join zf_product as pd on pd.id = cust.pd_id	
	left join zf_admin as admin on cust.admin_id = admin.id
        where cust.id = $cid ";

	$db=DB::instance();
	$details = $db->get_one( $sql );
	$details['last_visit_time'] = date('Y-m-d',$details['last_visit_time']);
	$details['bir'] = date('Y-m-d',$details['bir']);
	$details['income_level'] = $details['income_level']."万元";

	

	/*============================ 概况 =====================================*/
	/* 获取所有的产品 */
	$sql_all_pd = "select id,name, sort_id, color from zf_product order by sort_id ";
	$db = DB::instance();
	$pds = $db->get_all( $sql_all_pd );
	/* 传递一些参数 */
	$param = "cust_id=".$cid;				    /* 客户参数 */
	$tp->set_file('info_detail');
	$tp->p();
}


?>

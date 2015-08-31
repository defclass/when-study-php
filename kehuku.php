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

		/* 只能查询本人 */
		if( $per_num == 1 ){
			$sql .= " and admin_id = '$admin_id' ";

		}

		//能查询下属的
		if( $per_num ==2 ){
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

		$sql .= " order by cust.last_visit_time desc ";


		/*============================== 分页  =======================================  */
		$db = DB::instance();
		$total =count($db->get_all($sql));
		$numPerPage=16;//每页显示条数
		$pageNum=!empty($_POST['pageNum'])?$_POST['pageNum']:1;//当前第几页
		$begin=!empty($_POST['pageNum'])?(($_POST['pageNum']-1)*$numPerPage):0;//开始显示条数
		$sql .= " limit ".$begin.','.$numPerPage;


		/*======================== 处理数值  ===============================================  */
		$db = DB::instance();
		$custs = $db->get_all( $sql );
		foreach( $custs as &$cust ){

			/* 最后拜访时间，时间戳转成字符串 */
			if($cust['last_visit_time'] ==0 ){
				$cust['last_visit_time'] = "还未拜访";
			}else
				$cust['last_visit_time'] = date('Y-m-d',$cust['last_visit_time']);

			/* 创建时间，时间戳转成字符串 */
			$cust['creat_time'] = date('Y-m-d',$cust['creat_time']);

			/* 限制字符串 为8个字 */
			$cust['remark'] = mb_substr($cust['remark'],0,8,'utf-8');

			/* 收入水平，加万元 */
			$cust['income_level'] .= "万元";
			$cust['user_sex'] = $user_sex_array[$cust['user_sex']];
			if(empty($cust['remark'])){
				$cust['remark'] = "无备注";
			}
			

		}
		$param = 'uid={sid_user}';
		$tp->set_file('kehuku_list');
		$tp->p();

		break;
	case $dongzuo=='add'://新增客户

		if( $_GET['flag'] == 'add_cust' ){

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
			$arr['tzsj_ph'] = $_POST['tzsj_ph'];
			$arr['tzgj_ph'] = $_POST['tzgj_ph'];
			$arr['tzph'] = $_POST['tzph'];
			

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

		if( $_GET['flag'] == '' ){
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

		
			$tp->set_file('kehuku_add');
			$tp->p();
		}




		break;

	case $dongzuo=='update'://更新
		break;

	case $dongzuo=='xiangqing'://添加拜访记录
		$cid = $_GET['cust_id'];
	
		/* 添加拜访记录  */
		if( $_GET['flag'] == 'add_baifang' ){
			foreach( $_POST as  &$v ){
				$v = addslashes(trim(stripslashes($v)));
			}
			$arr = array();
			$arr['user_id'] = $cid ;
			$arr['admin_id'] = $_SESSION['id'];
		
			// 数据库中的sort_id 与 目前拜访记录中的sort_id进行比较，目前拜访记录中的sort_id是否比数据库中的大
			 
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

		if($_GET['flag'] == ""){
				
			//客户详情
			$sql = "select admin.name as admin_name,
			        cust.*,
				pd.sort_id
				from zf_customer as cust
				left join zf_product as pd on pd.id = cust.pd_id	
				left join zf_admin as admin on cust.admin_id = admin.id
				where cust.id = ".$_GET['uid'];

			$db=DB::instance();
			$details = $db->get_one( $sql );
			$details['last_visit_time'] = date('Y-m-d',$details['last_visit_time']);
			$details['bir'] = date('Y-m-d',$details['bir']);
			$details['income_level'] = $details['income_level']."万元";

			$details['tzph'] = $tzph[$details['tzph']];
			$details['tzsj_ph'] = $tzsj_ph[$details['tzsj_ph']];
			$details['tzgj_ph'] = $tzgj_ph[$details['tzgj_ph']];



			/*============================ 概况 =====================================*/
			/* 获取所有的产品 */
			$sql_all_pd = "select id,name, sort_id, color from zf_product order by sort_id ";
			$db = DB::instance();
			$pds = $db->get_all( $sql_all_pd );
			/* 传递一些参数 */
			$param = "cust_id=".$_GET['uid'];				    /* 客户参数 */
			$tp->set_file('kehuku_add_baifang');
			$tp->p();
		}

		break;
	case $dongzuo=='search'://搜索
		/* 获取权限数字 */
		$module_per_obj = modulePermission::instance();
		$per_num = $module_per_obj->getPermission($level_id, 'customer', 'search');
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

		/* 只能查询本人 */
		if( $per_num == 1 ){
			$sql .= " and admin_id = '$admin_id' ";

		}

		//能查询下属的
		if( $per_num ==2 ){
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

		foreach( $_POST as  $k=>&$v ){
			$v = addslashes(trim(stripslashes($v)));

			/* 时间转换成时间戳 */
			if($k == 'time_start' || $k == 'time_over') $v = strtotime ($v);
		}


		$ygxm = $_POST['admin_name'];
		$user_name = $_POST['user_name'];
		$user_tel = $_POST['user_tel'];
		$time_start = $_POST['time_start'];
		$time_over = $_POST['time_over'];


		if (!empty($ygxm)){
			$sql .= " and admin.name = '$ygxm'";
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


		$sql .= " order by cust.last_visit_time desc ";


		/*============================== 分页  =======================================  */
		$db = DB::instance();
		$total =count($db->get_all($sql));
		$numPerPage=16;//每页显示条数
		$pageNum=!empty($_POST['pageNum'])?$_POST['pageNum']:1;//当前第几页
		$begin=!empty($_POST['pageNum'])?(($_POST['pageNum']-1)*$numPerPage):0;//开始显示条数
		$sql .= " limit ".$begin.','.$numPerPage;

		/*======================== 处理数值  ===============================================  */
		$db = DB::instance();
		$custs = $db->get_all( $sql );
		foreach( $custs as &$cust ){

			/* 最后拜访时间，时间戳转成字符串 */
			if($cust['last_visit_time'] ==0 ){
				$cust['last_visit_time'] = "还未拜访";
			}else
				$cust['last_visit_time'] = date('Y-m-d',$cust['last_visit_time']);

			/* 创建时间，时间戳转成字符串 */
			$cust['creat_time'] = date('Y-m-d',$cust['creat_time']);

			/* 限制字符串 为8个字 */
			$cust['remark'] = mb_substr($cust['remark'],0,8,'utf-8');

			/* 收入水平，加万元 */
			$cust['income_level'] .= "万元";

			$cust['user_sex'] = $user_sex_array[$cust['user_sex']];
			if(empty($cust['remark'])){
				$cust['remark'] = "无备注";
			}

		}
		$param = 'uid={sid_user}';
		$tp->set_file('kehuku_list');
		$tp->p();

		break;
	default:
		echo 'other';
		break;
	}
}

?>


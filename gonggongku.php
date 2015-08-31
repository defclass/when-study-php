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

		if( $per_num == 0){
			die("您没有权限查看");
			exit;
		}

		$sql = "select cust.* from  zf_customer as cust   where cust.admin_id = 0 ";
		$sql .= " order by cust.last_visit_time desc ";

/*============================== 分页  =======================================  */

/*增加分页功能*/
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
         

		$param = 'uid={sid_user}&public_info=1';
		$tp->set_file('gonggongku');
		$tp->p();

	case $dongzuo=='add'://添加新客户
		
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

		
			$tp->set_file('gonggongku_add');
			$tp->p();
		}




		break;

	
	case $dongzuo=='search'://搜索
	
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


	
       
		foreach( $_POST as  $k=>&$v ){
			$v = addslashes(trim(stripslashes($v)));

			/* 时间转换成时间戳 */
			if($k == 'time_start' || $k == 'time_over') $v = strtotime ($v);
		}


		$ygxm = $_POST['admin_name']; //员工姓名
		$user_name = $_POST['user_name'];
		$user_tel = $_POST['user_tel'];
		$time_start = $_POST['time_start'];
		$time_over = $_POST['time_over'];


		if (!empty($ygxm)){
			$sql .= " and admin.name = '$ygxm'";//员工姓名
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

	

		$sql .= " order by cust.last_visit_time desc ";

/*============================== 分页  =======================================  */

/*增加分页功能*/
		$db = DB::instance();
		$total =count($db->get_all($sql));
		$numPerPage=16;//每页显示条数
		$pageNum=!empty($_POST['pageNum'])?$_POST['pageNum']:1;//当前第几页
		$begin=!empty($_POST['pageNum'])?(($_POST['pageNum']-1)*$numPerPage):0;//开始显示条数

		$sql .= " limit ".$begin.','.$numPerPage;
//处理数值
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
                /* 该页面要设置抢客户按纽 */
		$public_info =  1;

		$param = 'uid={sid_user}';
		$tp->set_file('gonggongku');
		$tp->p();


		break;
	case $dongzuo=='xiangqing'://查询
		/*=============================== 客户详情 ================================ */


		$sql = "select admin.name as admin_name,cust.*, pd.sort_id
                        from zf_customer as cust
	left join zf_product as pd on pd.id = cust.pd_id	
	left join zf_admin as admin on cust.admin_id = admin.id
        where cust.id = ".$_GET['cust_id'];
	
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
		$param = "cust_id=".$_GET['cust_id'];				    /* 客户参数 */
		$tp->set_file('gonggongku_xiangqing');
		$tp->p();
		break;

	default:
		echo 'other';
		break;
	}
}

?>


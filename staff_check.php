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
		$per_num = $module_per_obj->getPermission($level_id, 'yuangongshenhe', 'query');

		if( $per_num == 0 ){
			echo error("您没有权限操作");
			exit;
		}


		$sql = " select admin.id as admin_id ,admin.* , level.* from zf_admin as admin 
			left join  zf_admin_level as level on admin.level_id = level.id
			where admin.banned = 1
			";


		if(in_array($level_id,array(1))){
			$sql .= "   ";
		}

		if(in_array($level_id,array(2))){
			$sql .= " and admin.fgs_id = '$admin_id' ";
		}
                //增加分页功能
		$db = DB::instance();
		$total =count($db->get_all($sql));
		$numPerPage=22;//每页显示条数
		$pageNum=!empty($_POST['pageNum'])?$_POST['pageNum']:1;//当前第几页
		$begin=!empty($_POST['pageNum'])?(($_POST['pageNum']-1)*$numPerPage):0;//开始显示条数

		$db = DB::instance();
		$staffs = $db->get_all( $sql );
		file_put_contents("text",var_export($total,true));
		foreach( $staffs as &$staff ){
			foreach( $zf_admin_all as $v ){
				if( $staff['fid'] == $v['id']){
					$staff['f_name'] = $v['name']; 
				}
			}
			$staff['lasttime'] = date('Y-m-d H:i:s', $staff['lasttime']);
		}

		$param = 'uid={uid_user}';
		$tp->set_file('daishenheyuangong');
		$tp->p();

		break;
	case $dongzuo=='sftg'://新增

		if(!isset($_GET['sftg'])){
			echo error('参数错误');
		}

		if($_GET['sftg'] == 'tg'){//通过审核
			$arr['banned'] = 0;
		}elseif($_GET['sftg'] == 'btg'){//不通过审核
			$arr['banned'] = 2;
		}
		$db = DB::instance();
		$cond = "id =".$_GET['uid'];
		$rt = $db->update('zf_admin',$arr,$cond);
		if( $rt ){
			echo success($msg);
		}else{
			echo error($msg);
		}


		break;
	case $dongzuo=='update'://更新

		break;
	case $dongzuo=='search'://搜索
		$module_per_obj = modulePermission::instance();
		$per_num = $module_per_obj->getPermission($level_id, 'yuangongshenhe', 'query');

		if( $per_num  == 0 ){
			echo error("您没有权限操作");
			exit;
		}


		$sql = " select admin.id as admin_id ,admin.* , level.* from zf_admin as admin 
			left join  zf_admin_level as level on admin.level_id = level.id
			where admin.banned = 1
			";


		if(in_array($level_id,array(1))){
			$sql .= "   ";
		}

		if(in_array($level_id,array(2))){
			$sql .= " and admin.fgs_id = '$admin_id' ";
		}

		$search_admin_name = $_POST['admin_name'];
		if (!empty($search_admin_name)){

			/* $sql .= " and admin.name = '$search_admin_name'"; */
			$sql .= " and admin.name like '%".$search_admin_name."%'";
		}

		$db = DB::instance();
		$staffs = $db->get_all( $sql );
		foreach( $staffs as &$staff ){
			foreach( $zf_admin_all as $v ){
				if( $staff['fid'] == $v['id']){
					$staff['f_name'] = $v['name']; 
				}
			}
			$staff['lasttime'] = date('Y-m-d H:i:s', $staff['lasttime']);
		}

		$param = 'uid={uid_user}';
		$tp->set_file('daishenheyuangong');
		$tp->p();

		break;
	default:
		echo 'other';
		break;
	}

}

?>

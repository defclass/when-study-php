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
		$sql = " select msg.* ,admin.name,lv.level_name from zf_message msg
			inner join zf_admin as admin on admin.id = msg.send_id
			inner join zf_admin_level as lv on admin.level_id = lv.id
			where m_id = 3 ";
		$db= DB::instance();

		/*增加分页功能*/
		$db = DB::instance();
		$total =count($db->get_all($sql));
		$numPerPage=22;//每页显示条数
		$pageNum=!empty($_POST['pageNum'])?$_POST['pageNum']:1;//当前第几页
		$begin=!empty($_POST['pageNum'])?(($_POST['pageNum']-1)*$numPerPage):0;//开始显示条数


		$fkjy = $db->get_all( $sql );

		foreach( $fkjy as &$f ){
			$f['s_time'] = date("Y-m-d H:i:s", $f['s_time']);
			list($f['headline'],$f['content'])= explode('@@@',$f['message']);
			$f['headline'] = cut_str($f['headline'],10);
			$f['content'] = cut_str($f['content'],30);
		}
		//传递参数
		$param = 'uid={uid_user}';
		$tp->set_file('fkjy');
		$tp->p();
		break;
	case $dongzuo=='xiangqing'://详情
		//消息ID
		$msg_id = $_GET['uid'];
		//选出这一条建议
		$db= DB::instance();
		$sql = "select msg.*, admin.name,lv.level_name from zf_message  as msg
			inner join zf_admin as admin on admin.id = msg.send_id
			inner join zf_admin_level as lv on lv.id = admin.level_id
			where msg.rec_id = 0  and msg.status = 0 and msg.id = '".$msg_id."'"; 
		$jy = $db->get_one( $sql );
		list($jy['headline'],$jy['content']) = explode("@@@",$jy['message']);
		$jy['s_time'] = date('Y-m-d  H:i:s',$jy['s_time']);
		

		$tp->set_file('xsfkjy');
		$tp->p();
		break;

	case $dongzuo=='update'://更新
		break;

	case $dongzuo=='search'://搜索
		$module_per_obj = modulePermission::instance();
		$per_num = $module_per_obj->getPermission($level_id, 'yuangongshenhe', 'query');

		$sql = " select msg.* ,admin.name,lv.level_name from zf_message msg
			inner join zf_admin as admin on admin.id = msg.send_id
			inner join zf_admin_level as lv on admin.level_id = lv.id
			where rec_id = 0 ";
		$sql .= !empty($_POST['admin_name'])?" AND  admin.name LIKE '%".$_POST['admin_name']."%' ":' ';
		$db= DB::instance();

		/*增加分页功能*/
		$db = DB::instance();
		$total =count($db->get_all($sql));
		$numPerPage=22;//每页显示条数
		$pageNum=!empty($_POST['pageNum'])?$_POST['pageNum']:1;//当前第几页
		$begin=!empty($_POST['pageNum'])?(($_POST['pageNum']-1)*$numPerPage):0;//开始显示条数

		$fkjy = $db->get_all( $sql );

		foreach( $fkjy as &$f ){
			$f['s_time'] = date("Y-m-d H:i:s", $f['s_time']);
			list($f['headline'],$f['content'])= explode('@@@',$f['message']);
			$f['headline'] = cut_str($f['headline'],10);
			$f['content'] = cut_str($f['content'],30);
		}

		$tp->set_file('fkjy');
		$tp->p();
		break;
		break;
	default:
		echo 'other';
		break;
	}
}

?>


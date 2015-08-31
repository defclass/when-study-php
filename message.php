<?php
require_once( 'global.php ');
$admin_id  = $_SESSION['id'];
$admin_name = $_SESSION['name'] ; 
$level_id = $_SESSION['level_id']; 
if(isset($_GET['action']))
{
	$dongzuo=$_GET['action'];
	switch ($dongzuo)
	{
	case $dongzuo=='add'://查询
		/* 获取权限数字 */
		$module_per_obj = modulePermission::instance();
		$per_num = $module_per_obj->getPermission($level_id, 'znx', 'add');
		foreach( $_POST as  &$v ){
			$v = addslashes(trim(stripslashes($v)));
		}
		$db = DB::instance();
		$sql = "select * from zf_customer where id = ".$_GET['cust_id'];
		$cust = $db->get_one($sql);
		$arr = array();
		/* 给自己提醒 */
		if(!isset($_GET['admin_id']) || $_GET['admin_id'] == $_SESSION['name']){ //用 $_GET['admin_id'] 这个参数来判断是上级提醒，还是自己提醒自己
			$arr['send_id'] = $admin_id;
			$arr['rec_id'] = $admin_id;
			$arr['m_id'] = 1;
		}else{ /* 给下属提醒 */
			$arr['rec_id'] = $_GET['admin_id'];
			$arr['send_id'] = $admin_id ;
			$arr['m_id'] = 2;
		}
		$arr['id'] = f_id();
		$arr['message'] = $cust['user_name']."%%".$_POST['time']."%%".$_POST['msg'];
		$arr['s_time'] = time();
		$db = DB::instance();
		$rt = $db->insert('zf_message',$arr);
		if($rt){ echo success(""); }else{ echo  error("");}
		break;
	case $dongzuo=='del'://删除一条消息
		foreach( $_POST as  &$v ){
			$v = addslashes(trim(stripslashes($v)));
		}
		$id = $_POST['id'];
		$arr['status'] = 1;
		$cond = " id = ".$id;
		$db = DB::instance();
		$rt = $db->update('zf_message',$arr, $cond );
		if($rt){echo "删除成功";}else{echo "删除不成功" ;}
		exit;
		break;
	case $dongzuo=='update'://更新
		break;
	case $dongzuo=='new': //显示一个添加提醒的框
		$arr['cust_id'] = $_GET['cust_id'];
		$arr['staff_id'] = $_GET['staff_id'];
		$tp->set_file('alert_new');
		$tp->p();
		break;
	case $dongzuo=='syxsxx': //首页显示信息
		//判断是否有记录
		$sql = "SELECT * FROM zf_sfyd WHERE wz_id = '".$_GET['mid']."' AND user_id = '".$_SESSION['id']."'";

		$db=DB::instance();
		$rt = $db->get_one( $sql );
		if(!$rt){/* 该消息标记为已读 */
			$sfyd['sfyd_id'] = f_id();
			$sfyd['user_id'] = $_SESSION['id'];
			$sfyd['wz_id'] = $_GET['mid'];
			$sfyd['sfyd'] = 1;
			$sfyd['time'] = time();
			$db=DB::instance();
			$db->insert("zf_sfyd",$sfyd);
		}

		//显示该条提醒
		$sql = " SELECT m.*,a.name AS name  FROM zf_message  AS m INNER JOIN  zf_admin AS a ON m.send_id = a.id  WHERE m.id = ".$_GET['mid'];
		$db=DB::instance();
		$tx = $db->get_one($sql);
		$tx['s_time'] = date("Y-m-d",$tx['s_time']);
		list($tx['txkh'],$tx['yysj'],$tx['content']) = explode("%%",$tx['message']);
		$tp->set_file('index_xstx');
		$tp->p();

		break;
	  
	default:
		echo 'other';
		break;
	}
}
?>

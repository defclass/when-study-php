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
	case $dongzuo=='query'://查询
		if( $_GET['do'] == "" ) {
			$tp->set_file('tjfkjy');
			$tp->p();
		}

		if( $_GET['do'] == "commit") {
			$com->format($_POST);			
			$arr['id'] = f_id();
			$arr['send_id'] = $admin_id ;
			$arr['rec_id'] = 0 ;
			$arr['message'] = $_POST['headline']."@@@".$_POST['content'];
			$arr['m_id'] = 3 ;
			$arr['status'] = 0 ;
			$arr['s_time'] = time() ;
			$db=DB::instance();
			$rt = $db->insert("zf_message",$arr);
			if( $rt ){echo success($msg);}else{echo error($msg);}
			

		}

		break;
	case $dongzuo=='add'://新增
		break;

	case $dongzuo=='update'://更新
		break;

	case $dongzuo=='search'://搜索
		$module_per_obj = modulePermission::instance();
		$per_num = $module_per_obj->getPermission($level_id, 'yuangongshenhe', 'query');
		break;
	default:
		echo 'other';
		break;
	}
}

?>


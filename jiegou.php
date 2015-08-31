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


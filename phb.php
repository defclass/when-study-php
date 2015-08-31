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
		$per_num = $module_per_obj->getPermission($level_id, 'phb', 'query');

		$sql = "SELECT p.*, a.name  FROM `zf_paihangbang` as p
                        inner join zf_admin as a on a.id = p.fgs_id
                        WHERE  p.riqi = curdate()";
		$db= DB::instance();
		$jrjl = $db->get_all( $sql );//今日所有的记录

		$phb_raw = array(); //排行榜数组
		foreach( $jrjl as $j ){
			$fgs_id = $j['fgs_id'];
			if(!isset($phb_raw[$fgs_id])){
				$phb_raw[$fgs_id] = array();
				$phb_raw[$fgs_id]['name'] = $j['name'];
			}
			$fgs = &$phb_raw[$fgs_id];
			$fgs[$j['flyj']] = $j['shuliang'];
			
		}
	
		$flyj_array = array(3,4,5,10,20,30,40,50,60,70,80);

		foreach($phb_raw as &$p){
			foreach($flyj_array as $fl){
				if(!isset($p[$fl])) $p[$fl]=0;
			}
		}

		$total = count($phb_raw);
		

		$tp->set_file('phb');
		$tp->p();

		break;
	case $dongzuo=='add'://新增
		break;

	case $dongzuo=='update'://更新
		break;

	case $dongzuo=='search'://搜索
		$module_per_obj = modulePermission::instance();
		$per_num = $module_per_obj->getPermission($level_id, 'phb', 'query');
		break;
	default:
		echo 'other';
		break;
	}
}

?>


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
		$module_per_obj = modulePermission::instance();
		$per_num = $module_per_obj->getPermission($level_id, 'lhb', 'query');
		if( $_GET['do'] == "" ){
			if( $per_num == 0){
				echo error("您无权限操作");
			}

			$sql = "select id, name, lhb 
				from  zf_admin 			
				where level_id in (2,3,4,5,6)
				";

			$db = DB::instance();
			$lhb_list= $db->get_all( $sql );

			//龙虎榜的前三名
			$lhb['1'] = array(array());
			$lhb['2'] = array(array());
			$lhb['3'] = array(array());

			foreach( $lhb_list as $l ){
				if($l['lhb'] == 1 ){
					$lhb['1'] = array(array('id'=>$l['id'],'name'=>$l['name']));
				}elseif($l['lhb'] == 2 ){
					$lhb['2'] = array(array('id'=>$l['id'],'name'=>$l['name']));
				}elseif($l['lhb'] == 3 ){
					$lhb['3'] = array(array('id'=>$l['id'],'name'=>$l['name']));
				}
			}


			$first = array_merge( $lhb['1'],$lhb_list);
			$second= array_merge( $lhb['2'],$lhb_list);
			$third = array_merge( $lhb['3'],$lhb_list);
			$tp->set_file('lhb');
			$tp->p();
			break;
		}

		if( $_GET['do'] == "commit" ){
			$sql = "update zf_admin  set lhb = 0 where lhb <> 0";
			//清空龙虎榜的内容
			$sql_sw []= $sql;

			//第一名
			$arr['lhb'] = 1;
			$cond = "id = '".$_POST['dym']."'";
			$db = DB::instance();
			$sql_sw[] = $db->update("zf_admin", $arr,$cond,1);


			//第二名
			$arr['lhb'] = 2;
			$cond = "id = '".$_POST['dem']."'";
			$db = DB::instance();
			$sql_sw[] = $db->update("zf_admin", $arr,$cond,1);


			//第三名
			$arr['lhb'] = 3;
			$cond = "id = '".$_POST['dsm']."'";
			$db = DB::instance();
			$sql_sw[] = $db->update("zf_admin", $arr,$cond,1);

			//开始执行事务
			$db = DB::instance();
			foreach( $sql_sw as $row ){
				$rt = $db->query($row);
			}
			//print_r($sql_sw);
			echo success("提交成功");

		}

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


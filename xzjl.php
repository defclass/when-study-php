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
		$sql = " select admin.name,
			t1.down_time,
			t2.name as file_name,
			t2.comp_id 
			from zf_file as t1 
			inner join zf_file as t2
			on t1.file_id = t2.id
			inner join zf_admin as admin
			on t1.admin_id = admin.id
			where  t1.down_time > 0  
		";

		$sql .=" order by t1.down_time desc  ";

		/*增加分页功能*/
		$db = DB::instance();
		$total =count($db->get_all($sql));
		$numPerPage=14;//每页显示条数
		$pageNum=!empty($_POST['pageNum'])?$_POST['pageNum']:1;//当前第几页
		$begin=!empty($_POST['pageNum'])?(($_POST['pageNum']-1)*$numPerPage):0;//开始显示条数

		$sql .= " limit ".$begin.','.$numPerPage;

		$db = DB::instance();
		$down_rec = $db->get_all( $sql);

		foreach( $down_rec as &$r ){
			$r['down_time'] = date('Y-m-d', $r['down_time']);
			switch( $r['comp_id']){
			case 1:  $r['comp_name'] = "集团公司"; break;
			case 2:  $r['comp_name'] = "融易投"; break;
			case 3:  $r['comp_name'] = "智富东方"; break;
			case 4:  $r['comp_name'] = "智富泰达"; break;
			}
		}	

		$tp->set_file('xzjl_list');
		$tp->p();
		break;
	case $dongzuo=='add'://新增
		break;

	case $dongzuo=='update'://更新
		break;

	case $dongzuo=='search'://搜索
		$sql = " select admin.name,
			t1.down_time,
			t2.name as file_name,
			t2.comp_id 
			from zf_file as t1 
			inner join zf_file as t2
			on t1.file_id = t2.id
			inner join zf_admin as admin
			on t1.admin_id = admin.id
			where  t1.down_time > 0  
		";

		$sql .= !empty($_POST['wjm'])?" AND admin.name LIKE '%".$_POST['wjm']."%'":'';	

		$sql .=" order by t1.down_time desc  ";
		/*增加分页功能*/
		$db = DB::instance();
		$total =count($db->get_all($sql));
		$numPerPage=14;//每页显示条数
		$pageNum=!empty($_POST['pageNum'])?$_POST['pageNum']:1;//当前第几页
		$begin=!empty($_POST['pageNum'])?(($_POST['pageNum']-1)*$numPerPage):0;//开始显示条数
		$sql .= " limit ".$begin.','.$numPerPage;
		file_put_contents("text",var_export($sql,true));

		$db = DB::instance();
		$down_rec = $db->get_all( $sql);

		foreach( $down_rec as &$r ){
			$r['down_time'] = date('Y-m-d', $r['down_time']);
			switch( $r['comp_id']){
			case 1:  $r['comp_name'] = "集团公司"; break;
			case 2:  $r['comp_name'] = "融易投"; break;
			case 3:  $r['comp_name'] = "智富东方"; break;
			case 4:  $r['comp_name'] = "智富泰达"; break;
			}
		}	

		$tp->set_file('xzjl_list');
		$tp->p();
		break;
	default:
		echo 'other';
		break;
	}
}

?>


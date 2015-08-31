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
		$comp_id = $_GET['comp_id'];
		$sql = "select admin.name as admin_name,
			file.id,
			file.name as file_name,
			file.size,
			file.upload_time,
			comp_id
			from   zf_file as file
			inner join  zf_admin as admin on admin.id = file.admin_id
			where  file_id = 0 and comp_id = ".$comp_id;

		/*增加分页功能*/
		$db = DB::instance();
		$total =count($db->get_all($sql));
		$numPerPage=22;//每页显示条数
		$pageNum=!empty($_POST['pageNum'])?$_POST['pageNum']:1;//当前第几页
		$begin=!empty($_POST['pageNum'])?(($_POST['pageNum']-1)*$numPerPage):0;//开始显示条数


		$sql .= " order by upload_time desc";

		$sql .= " limit ".$begin.','.$numPerPage;

		$db = DB::instance();
		$files = $db->get_all( $sql );

		foreach( $files as &$v ){
			$v['upload_time'] = date("Y-d-y H:i:s",$v['upload_time']);
			$v['size'] .= "M" ;
		}

		$tp->set_file('gxzq_list');
		$tp->p();

		break;
	case $dongzuo=='add'://新增
		if($_GET['do']==""){
			$tp->set_file('gxzq_add');
			$tp->p();
		}
		if($_GET['do']=="commit"){
			include("libs/uploadfile.php"); 
			$file = $_FILES['file'];
			list($wjm,$hz) = explode(".",$file['name']);
			$store_name = md5($wjm)."_".time();
			$state = upload($file,$store_name,"uploads/",array('zip','rar','doc','xls','pdf'),10*1024*1024);
			if ($state){

				$arr['name'] = $file['name'];
				$arr['store_name'] = $store_name.".".$hz;
				$arr['upload_time'] = time();
				$arr['admin_id'] = $admin_id;
				$arr['comp_id'] = $_GET['comp_id'];
				$arr['size']  = round($file['size'] / (1024*1024),2);
				$db =DB::instance();
				$rt = $db->insert('zf_file',$arr);
				if($rt){echo success($msg);}else{echo  error($msg);}
				exit;
			}
		}
		break;

	case $dongzuo=='down'://更新
		$id = $_GET['id'];
		$sql = " select store_name from zf_file where id = '$id' ";
		$db = DB::instance();
		$rt = $db->get_one( $sql );
		if( $rt ){

			$path = $config['website']."/uploads/".$rt['store_name'];
			header("Location: $path"); 


			$arr['down_time'] = time();
			$arr['admin_id'] = $admin_id;
			$arr['file_id'] = $id;
			$db = DB::instance();
			$db->insert('zf_file', $arr );
		}

		break;

	case $dongzuo=='search'://搜索
		$sql = "select admin.name as admin_name,
			file.id,
			file.name as file_name,
			file.size,
			file.upload_time,
			comp_id
			from   zf_file as file
			inner join  zf_admin as admin on admin.id = file.admin_id
			where  file_id = 0 ";

		if(empty($_POST['wjm'])){
			echo error("请输入搜索条件");
			exit;
		}
		$sql .= !empty($_POST['wjm'])?" AND file.name LIKE '%".$_POST['wjm']."%'":'';	

		$sql .= " order by upload_time desc";

		$db = DB::instance();
		$files = $db->get_all( $sql );

		foreach( $files as &$v ){
			$v['upload_time'] = date("Y-d-y H:i:s",$v['upload_time']);
			$v['size'] .= "M" ;
		}

		$tp->set_file('gxzq_list');
		$tp->p();
		break;
	default:
		echo 'other';
		break;
	}
}

?>


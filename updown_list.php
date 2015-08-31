<?php
require_once('global.php');
$admin_id  = $_SESSION['id'];
$level_id = $_SESSION['level_id']; 

if( $_GET['action'] == '' ){
	$sql = "select admin.name as admin_name,
                       file.id,
                       file.name as file_name,
                       file.size,
                       file.upload_time,
                       comp_id
                from   zf_file as file
                inner join  zf_admin as admin on admin.id = file.admin_id
                where  file_id = 0
";
	$db = DB::instance();
	$files = $db->get_all( $sql );


	$jt_files = array();
	$ryt_files = array();
	$zfdf_files = array();
	$zftd_files = array();
	$scb_files = array();
	foreach( $files as $v ){
		$v['upload_time'] = date("Y-d-y H:i:s",$v['upload_time']);
		$v['size'] .= "M" ;
		switch($v['comp_id']){
		case 1:  $jt_files[] = $v; break;
		case 2:  $ryt_files[] = $v; break;
                case 3:  $zfdf_files[] = $v; break;
                case 4:  $zftd_files[] = $v; break;
		case 5:  $scb_files[] = $v; break;
		}
	}

	//控制显示隐藏
	if(!isset($_SESSION['no'])){
		$no = "no1";
	}else{
		$no = $_SESSION['no'];
	}
	$yincang = "style=\"display:none\" class = \"dianji\"";//隐藏的css
	$yc = array();
	for($i=1;$i<=5;$i++){
		$sy = "no".$i; //索引
		$yc[$sy] = $yincang;
	}
	unset($yc[$no]);


	$tp->set_file('updown_list');
	$tp->p();
}

if( $_GET['action'] == 'down' ){
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
}

if( $_GET['action'] == 'del' ){
	$id = $_GET['id'];
	
	if ( $level_id !== '1' ){ 
		echo $level_id;
		die("您无权进行此操作");
		exit;
	}

	$cond = "id = ".$id;
	$db = DB::instance();
	$rt = $db->delete( 'zf_file',$cond );
	if($rt){echo success("删除成功");}else{echo  error("删除失败");}
	exit;
}



if( $_GET['action'] == 'upload' ){
 	include("libs/uploadfile.php"); 
	$file = $_FILES['file'];
	list($wjm,$hz) = explode(".",$file['name']);
	$store_name = md5($wjm)."_".time();
	$state = upload($file,$store_name,"uploads/",array('zip','rar','doc','xls'),10*1024*1024);
	if(!isset($_GET['no'])){
		$_SESSION['no'] = "no1";
	}else{
		$_SESSION['no'] = $_GET['no'];
	}
       		
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







?>

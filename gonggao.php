<?php
require_once('global.php');
if(isset($_GET['action']))
{
	$dongzuo=$_GET['action'];
	switch ($dongzuo)
	{
	case $dongzuo=='query'://查询公告列表
		$sql = " SELECT * FROM `zf_article` WHERE `fl_id` = 1 ";

		/*增加分页功能*/
		$db = DB::instance();
		$total =count($db->get_all($sql));
		$numPerPage=22;//每页显示条数
		$pageNum=!empty($_POST['pageNum'])?$_POST['pageNum']:1;//当前第几页
		$begin=!empty($_POST['pageNum'])?(($_POST['pageNum']-1)*$numPerPage):0;//开始显示条数

		$gg = $db->get_all($sql);
		foreach($gg as &$g){
			$g['date'] = date("Y-m-d",$g['date']);
			$g['headline'] = cut_str($g['headline'],15);
			$g['content'] = cut_str($g['content'],15);
		}
		$param = 'uid={sid_user}';
		$tp->set_file('gonggao');
		$tp->p();
		break;

	case $dongzuo=='edit'://编辑公告
		if($_GET['flag'] == "commit" ){//提交
			if(!is_numeric($_GET['gid'])){
				echo error("参数错误");
				exit;
			}

			$db=DB::instance();
			$cond = "wz_id = ".$_GET['gid'];
			$rt=$db->update("zf_article",$_POST,$cond);

			if($rt){echo success($msg);}else{echo  error($msg);}
			exit;
				
		}
		if($_GET['flag'] == "" ){//显示界面
			$sql = " SELECT * FROM `zf_article` WHERE `wz_id` =  ".$_GET['uid'];
			$db=DB::instance();
			$gonggao = $db->get_one($sql);
			$gonggao['date'] = date("Y-m-d",$gonggao['date']);
			$tp->set_file('gonggao_edit');
			$tp->p();
		}
		break;

	case $dongzuo=='add'://发公告
		if($_GET['flag'] == ""){//显示发公告
			/* 获取权限数字 */
			$module_per_obj = modulePermission::instance();
			$per_num = $module_per_obj->getPermission($level_id, 'fgg', 'query');
			if($per_num == 0 ) error("您没有权限");
		
			$tp->set_file('gonggao_add');
			$tp->p();	
		}

		if($_GET['flag'] == "commit"){//发公告的提交
			$module_per_obj = modulePermission::instance();
			$per_num = $module_per_obj->getPermission($level_id, 'fgg', 'add');	
			if($per_num == 0 ) error("您没有权限");
			$arr = array();
			$arr = $_POST;
			$arr['wz_id'] = f_id();
			$arr['fl_id'] = 1;
			$arr['author'] = $_SESSION['name'];
			$arr['date'] = time();
			$db=DB::instance();
			$rt = $db->insert("zf_article",$arr);
			if($rt){echo success($msg);}else{echo  error($msg);}
			exit;
		}
		break;
	case $dongzuo=='xsgg'://显示公告
		$sql = " SELECT * FROM `zf_article` WHERE `wz_id` = ".$_GET['wz_id'] ;
		$db=DB::instance();
		$gonggao = $db->get_one($sql);
		$gonggao['date'] = date("Y-m-d",$gonggao['date']);


		//判断是否已读
		$sql = " SELECT * FROM `zf_sfyd` WHERE `user_id` = ".$_SESSION['id']." AND `wz_id` = ".$_GET['wz_id'];
		$db=DB::instance();
		$sfyd = $db->get_one($sql);
		if(empty($sfyd)){
			$arr['sfyd_id'] = f_id();
			$arr['user_id'] = $_SESSION['id'];
			$arr['wz_id'] = $_GET['wz_id'];
			$arr['sfyd'] = 1;
			$arr['time'] = time();
			$db=DB::instance();
			$db->insert('zf_sfyd',$arr);
		}
		
		$tp->set_file('gonggao_xsgg');
		$tp->p();

		break;

	case $dongzuo=='search'://搜索
		$sql = " SELECT * FROM `zf_article` WHERE `fl_id` = 1 ";
		$sql .= !empty($_POST['headline'])?" AND headline LIKE '%".$_POST['headline']."%'":"";
		file_put_contents("text",var_export($sql,true));
		/*增加分页功能*/
		$db = DB::instance();
		$total =count($db->get_all($sql));
		$numPerPage=22;//每页显示条数
		$pageNum=!empty($_POST['pageNum'])?$_POST['pageNum']:1;//当前第几页
		$begin=!empty($_POST['pageNum'])?(($_POST['pageNum']-1)*$numPerPage):0;//开始显示条数
	
		$gg = $db->get_all($sql);
		foreach($gg as &$g){
			$g['date'] = date("Y-m-d",$g['date']);
			$g['headline'] = cut_str($g['headline'],15);
			$g['content'] = cut_str($g['content'],15);
		}
		$param = 'uid={sid_user}';
		$tp->set_file('gonggao');
		$tp->p();
		break;
	default:
		echo 'other';
		break;
	}
}

?>


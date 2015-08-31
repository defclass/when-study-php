<?php
require_once 'global.php';
if( $_SESSION['id'] ){
	$admin_id  = $_SESSION['id'];
	$name = $_SESSION['name'] ; 
	$level_id = $_SESSION['level_id']; 
	//switch( $level_id) {
	//case 1:
		//$name =  mb_substr( $name,0, 1, 'utf-8' );
		//$name = $name."总";
		//break;
	//case 2:
		//$name .= "老总";
        //case 3:  $name .= "老总";
        //case 4: $name =  mb_substr( $name,0, 1, 'utf-8' );
		//$name = $name."总";
		//break;
	//case 5:	$name =  mb_substr( $name,0, 1, 'utf-8' );
		//$name = $name."经理";
		//break;
	//case 6:	$name =  mb_substr( $name,0, 1, 'utf-8' );
		//$name = $name."顾问";
	//}
	//公告
	$sql = " SELECT a.* ,s.time as ydsj FROM `zf_article` as a 
                 LEFT JOIN (select * from  zf_sfyd where user_id = ".$_SESSION['id']." ) as s on a.wz_id = s.wz_id
                 WHERE fl_id = 1 order by s.time ";
	$db=DB::instance();
	$gg =$db-> get_all($sql); 
	foreach($gg as &$g ){
		$g['date'] = date("Y-m-d",$g['date']);
		$g['headline'] = cut_str($g['headline'],15);
		if(mb_strlen($g['headline'],'UTF8')>15 )$g['headline'] .= " ... ";
		if($g['ydsj'] == NULL ) $g['headline'] .= ' &nbsp;&nbsp;<img src=" '.$config['website'].'/tpl/images/new_b.gif"> ';
	}


	//读取消息
	$sql = "select msg.id as msg_id, msg.s_time, msg.message,  admin.name, s.time from zf_message  as msg
		inner join zf_admin as admin on admin.id = msg.send_id
                left join (select * from zf_sfyd where user_id = '".$_SESSION['id']."') as s on msg.id = s.wz_id
		where msg.status = 0 and msg.rec_id = ".$_SESSION['id']." and m_id in (1,2) order by s.time  ";


        $db = DB::instance();
	$msg = $db->get_all($sql);
	foreach( $msg as &$m ){
		$arr = explode("%%",$m['message']);
		$m['message'] = $arr[2];
		$m['message'] = cut_str($m['message'],15);
		if(mb_strlen($m['message'],'UTF8')>15 )$m['message'] .= " ... ";
		if($m['time'] == null) $m['message'] .= ' &nbsp;&nbsp;<img src=" '.$config['website'].'/tpl/images/new_b.gif"> ';
		$m['s_time'] = date("Y-m-d",$m['s_time']);
	}	
	//龙虎榜
	$sql = " select * from zf_admin where  lhb > 0  ";
	$db= DB::instance();
	$lhb = $db->get_all( $sql );
	foreach( $lhb as $l ){
		if( $l['lhb'] == 1 ){
			$first = $l['name'];
		}elseif( $l['lhb'] == 2 ){
			$second = $l['name'];
		}elseif( $l['lhb'] == 3 ){
			$third= $l['name'];
		}
	}
	$tp->set_file('index');
	$tp->p();
}else{
	header('location:index.php');
}
?>

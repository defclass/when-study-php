<?php
/**
 * 此脚本必须在凌晨执行（超过晚上12点执行）
 */
require_once("global.php");
/**
 *1.执行客户更新操作
 */
$day30 = 60*60*24*30;
$gxljsj = strtotime(date("Y-m-d",time()))-$day30;//更新时间的临界点，小于这个时间就更新
$sql = "select  * from zf_customer where br_last_visit <= $gxljsj and br_last_visit >0"; //查询所有客户
$db = DB::instance();
$ydkh = $db->get_all( $sql );//移动的客户
$collect = array(); //收集客户名
foreach( $ydkh as $y ){
	$key = strval($y['admin_id']);
	if(!isset($collect[$key])){
		$collect[$key] = array();
	}
	$collect[$key][] = $y; //收集该员工移动到公共库的客户姓名;
}
foreach( $collect as $k => $c ){ 
	foreach($c as $dec){//第二层循环
		//将移动客户的信息发送一条站内信给员工
		$msg['id']= f_id();
		$msg['send_id']= 0;
		$msg['rec_id']= $k;
		$msg['m_id']= 4;
		$msg['message']= "由于超过30天未拜访，您私有库中的";
		$msg['message'] .= $dec['user_name'] ;
		$msg['message'] .= "已转移到公共库中";
		$msg['m_id']= 0;
		$msg['s_time']= time();
		$db = DB::instance();
		$sql_sw[] = $db->insert('zf_message',$msg,1);
		//执行更新操作
		$arr['admin_id'] = 0;
		$arr['br_last_visit'] = 0;
		$cond = " id =  ".$dec['id']; 
		$db = DB::instance();
		$sql_sw[] = $db->update('zf_customer', $arr, $cond,1 );
	}
}
/*
 * 超过27天未访问的发送一条站内信
 * 
 */
$day27 = 60*60*24*27;
$sql = "select  * from zf_customer where last_visit_time >= $day27 and last_visit_time < $day30"; //查询所有客户
$db = DB::instance();
$ydkh = $db->get_all( $sql );//移动的客户
$collect = array(); //收集客户名
foreach( $ydkh as $y ){
	$key = $y['admin_id'];
	if(!isset($collect[$key])){
		$collect[$key] = array();
	}
	$collect[$key][] = $y['user_name']; //收集该员工移动到公共库的客户姓名;
}
foreach( $collect as $k => $c ){ //将移动客户的信息发送一条站内信给员工
	$msg['id']= f_id();
	$msg['send_id']= 0;
	$msg['rec_id']= $k;
	$msg['m_id']= 4;
	$msg['message'] = "您私有库中的客户：";
	$msg['message'] .= explode("，",$c);
	$msg['message']= " 超过27天未拜访，请及时拜访。如果未拜访超30天，该客户将转移到公共库中";
	$msg['m_id']= 0;
	$msg['s_time']= time();
	$db = DB::instance();
	$sql_sw[] = $db->insert('zf_message',$msg,1);
}
/**
 *2. 执行排行榜的相关脚本
 */
$date = date("Y-m-d");//现在时刻
//统计分公司下的不同职位的员工数量
$sql = "SELECT id FROM `zf_admin_level` WHERE id >= 3";
$db = DB::instance();
$level_id = $db->get_all($sql);// 
foreach( $level_id as $l ){
	$sql = " SELECT count(*) as count ,fgs_id FROM `zf_admin`  where fgs_id >0 ";
	$sql .= " AND level_id = ".$l['id'];
	$sql .= " group by fgs_id ";
	$db = DB::instance();
	$shuliang = $db->get_all($sql); //每个分公司不同职位的数量
	foreach( $shuliang as $s ){
		$arr['phb_id'] = f_id();
		$arr['flyj'] = $l['id'];
		$arr['fgs_id'] = $s['fgs_id'];
		$arr['shuliang'] = $s['count'];
		$arr['riqi'] = $date ;
		$db = DB::instance();
		$sql_sw[] = $db->insert("zf_paihangbang",$arr,1);
	}
}
//统计分公司下不同产品的客户数量
$sql = "SELECT id ,fgs_id FROM `zf_admin` where fgs_id >0 ";
$db = DB::instance();
$id_fgsid = $db->get_all($sql); 
$yg_id_arr = array();//员工ID数组，索引是分公司
foreach( $id_fgsid as $i ){
	$fgs_id = $i['fgs_id'];
	if(!isset($yg_id_arr[$fgs_id])) {
		$yg_id_arr[$fgs_id] = array($fgs_id);
	}
	array_push($yg_id_arr[$fgs_id],$i['id']);
}
foreach( $yg_id_arr as $k => $y ){
	$id_array = implode(", ",$y);
	$sql = " SELECT count(*) as count , `pd_id` FROM `zf_customer`  where admin_id in ( ".$id_array." ) ";
	$sql .= " group by `pd_id` ";
	$db = DB::instance();
	$shuliang = $db->get_all($sql);
	foreach( $shuliang as $sh ){
		$arr['phb_id'] = f_id();
		$arr['flyj'] = $sh['pd_id'];
		$arr['fgs_id'] = $k;
		$arr['shuliang'] = $sh['count'];
		$arr['riqi'] = $date ;
		$db = DB::instance();
		$sql_sw[] = $db->insert("zf_paihangbang",$arr,1);
	}
}
$db=DB::instance();
$db->query("BEGIN"); //开始
$r=1;
foreach($sql_sw as $row) {
	$res=$db->query($row);
	if(!$res){
		$r = 0;
		break;
	} 
} 	
if(!$r) {
	$db->query("ROLLBACK"); //sql语句执行失败,事务回滚
	exit('每日脚本执行失败!');
} else {
	$db->query("COMMIT");//提交事务

} 
?>

<?php
require_once( 'global.php ');

$admin_id  = $_SESSION['id'];
$admin_name = $_SESSION['name'] ; 
$level_id = $_SESSION['level_id']; 

/* 获取权限数字 */
$module_per_obj = modulePermission::instance();
$per_num = $module_per_obj->getPermission($level_id, 'staff', 'query');

$sql = " select admin.name,
                t1.down_time,
                t2.name as file_name,
                t2.comp_id 
         from zf_file as t1 
         inner join zf_file as t2
         on t1.file_id = t2.id
         inner join zf_admin as admin
         on t1.admin_id = admin.id
         where  t1.down_time > 0  order by t1.down_time desc limit 0,30 ;
";


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

$param = 'uid={uid_user}';
$tp->set_file('down_reconder');
$tp->p();



?>
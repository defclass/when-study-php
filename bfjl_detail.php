<?php
require_once("global.php");


$admin_id  = $_SESSION['id'];
$name = $_SESSION['name'] ; 
$level_id = $_SESSION['level_id']; 

/* 鑾峰彇鏉冮檺鏁板瓧 */
$module_per_obj = modulePermission::instance();
$per_num = $module_per_obj->getPermission($level_id, 'visit', 'query');

$cust_id = $_GET['cust_id'];

$sql = " select admin.name,
                baifang.visit_time,
                baifang.remark
         from   zf_baifang as baifang,
                zf_admin as admin
         where  admin.id = baifang.admin_id
            and baifang.user_id = '$cust_id' order by baifang.visit_time desc limit 0,20
";

$db = DB::instance();
$bf_recs = $db->get_all( $sql );
foreach( $bf_recs as &$rec ){
	$rec['visit_time'] = date("Y-m-d", $rec['visit_time']);

}
$param = 'vid={sid_user}';
$tp->set_file('bfjl_detail');
$tp->p();

?>

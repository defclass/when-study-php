<?php
class modulePermission {
	private $modules = array(
		'customer' => 0, //客户
		'visit' =>1, //拜访记录
		'staff'=>2, //下属管理
		'yuangongshenhe'=>3, //员工审核
		'qfdx'=>4, //群发短信
		'lhb'=>5, //龙虎榜
		'fkjy'=>6, //反馈建议
		'znx'=>7, //站内信
		'phb'=>8,  //排行榜
		'fgg'=>9 //发公告
		);

	private $operator = array(
		'add' => 0,
		'del' => 1,
		'update' => 2,
		'query'=> 3,
		'search'=> 4,
		);

	private static  $instance;

		
	private  function __construct(){}


	public static  function instance(){
	
		if(!isset( self::$instance ) )	self::$instance = new self();
		
		
		return self::$instance;
	}

	/**
	 * @return  权限数字，如0,1,2,3 。一般情况，0代表无权查（改，删）
	 *          所有数据，1代表仅查（改，删）自己的数据，2代表可查（改，删）
	 *          下属的数据，3代表可查（改，删）所有数据
	 * @param $admin_level_id 　职位的ID号
	 * @param $modules_name 　　调用的模块名
	 * @param $operator_name  操作，包括增，删，改，查
	 */
	public function  getPermission($admin_level_id, $module_name, $operator_name){


                /* 获取权限字符串 */

		$sql = "select * from zf_admin_level where id = '$admin_level_id' ";
		$db = DB::instance();

		$rt = $db->get_one( $sql );


		if (!is_null( $rt )) $str = $rt['permissions'];

		$patterns = '#{(.*)}#U';

		preg_match_all($patterns, $str, $str);

                /* 获取对应的模块 */

		$modules_num = $this->modules[$module_name];
		$modules = $str[1][$modules_num];
		
		$per_arr = explode( ',', $modules );

                 /* 获取的权限数字 */
		$operator_num = $this->operator[$operator_name];
		$permissions = $per_arr[$operator_num];

		return $permissions;

	}


}
?>

<?php
//======================== 系统常量 ========================\\
//error_reporting(0); 
error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ALL);
ini_set('date.timezone','Asia/Shanghai');


define('APP_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR); //当前目录
define('LIB_PATH', APP_PATH . 'libs'. DIRECTORY_SEPARATOR); //类库目录
define('TEMP_PATH', APP_PATH . 'tpl'. DIRECTORY_SEPARATOR); //模板目录
//header("Content-Type: text/html; charset=UTF-8");

include_once (LIB_PATH . "template.ease.php"); //template类	
/*模板引擎*/
$tp = new template(array (
			   'TemplateDir' => TEMP_PATH
			   ));
@ob_end_clean();

require_once( LIB_PATH."DB.php");

session_start();


/**
 * 加载文件
 */
include_once("config.php");



/**
 * 自动加载函数
 */

function autoload($classname){
	if(file_exists("libs/".$classname.".php")){
		include_once("libs/".$classname.".php");
	}

}

spl_autoload_register('autoload');

//======================== 检测是否登陆   ===================================\\
if( isset( $_SEESION['id'] )){
	header('location:index.php');

}

//======================== 过滤全局变量 ===============================\
foreach(array('_COOKIE', '_POST', '_GET') as $_request) {
	foreach($$_request as $_key => $_value) {
		$_key{0} != '_' && $$_key = daddslashes($_value);
	}
}




//========================== 常用函数 ================================\\
 

/**
 * 
 * @param  $array  返回 当前ID的所有子ID(包括当前ID) 
 * @param $result  二维数据 ，包括id ,fid 的字段
 * @param $fid     当前ID 。当前ID作为最顶级的父ID
 */

function scanNodeOfTree($result,&$array=array(),$fid=0){
        $i=0; 
	if((bool)$result){
		foreach($result as $value){
			if($value['fid']==$fid){
				$array[]=$value['id'];
				scanNodeOfTree($result,$array,$value['id'],$lv);
			}
		}
	}
}	

//Get the real client IP ("bullet-proof")
function GetIP(){
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
		$ip = getenv("REMOTE_ADDR");
	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
		$ip = $_SERVER['REMOTE_ADDR'];
	else
		$ip = "unknown";
	return($ip);
}



//dwz_ajax_succee
function success($msg){
	$msg = $msg ? $msg : "操作成功!";
	$json = "{\"statusCode\":\"200\",\"message\":\"".$msg."\",\"navTabId\":\"\",\"callbackType\":\"forward\"}";
	return $json;
}

//dwz_ajax_error
function error($msg){
	$msg = $msg ? $msg : "操作错误!";
	$json = "{\"statusCode\":\"300\",\"message\":\"".$msg."\",\"navTabId\":\"\",\"callbackType\":\"forward\"}";
	return $json;
}

function numDiff2Time($count) { //这是将数字固定化输出的函数，输出字符串，格式2012-0-0 13:00:00
	// $count=5005537;
	$year = 0;
	$month = 0;
	$day = 0;
	$hour = 0;
	$minu = 0;
	$second = 0;

	$year_0 = 3600 * 24 * 30 * 12;
	$month_0 = 3600 * 24 * 30;
	$day_0 = 3600 * 24;
	$hour_0 = 3600;
	$minu_0 = 60;
	//	if($count <0)  alert("请输入大于0的整数"); 	
	//求年
	if ($count >= $year_0) {
		$year = floor($count / (3600 * 24 * 30 * 12));
		$count = $count % (3600 * 24 * 30 * 12);
	}

	//求月
	if ($count >= $month_0) {
		$month = floor($count / (3600 * 24 * 30));
		$count = $count % (3600 * 24 * 30);
	}

	//求天数
	if ($count >= $day_0) {
		$day = floor($count / (3600 * 24));
		$count = $count % (3600 * 24);
	}

	//求小时数
	if ($count >= $hour_0) {
		$hour = floor($count / 3600);
		$count = $count % 3600;
	}

	//求分钟数
	if ($count >= $minu_0) {
		$minu = floor($count / 60);
		$count = $count % 60;
	}
	//求秒数
	$second = $count;

	//时，分，秒如果小于10则自动在前面添加0.	
	$hour = ($hour < 10) ? '0' . $hour: $hour;
	$minu = ($minu < 10) ? '0' . $minu: $minu;
	$second = ($second < 10) ? '0' . $second: $second;

	//返回固定格式的天数，时间，如2000-00-00 00:00:01
	/* if ($year !== 0) return $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minu . ":" . $second; */

	/* if ($month !== 0) return $month . "-" . $day . " " . $hour . ":" . $minu . ":" . $second; */

	if ($day !== 0) return $day ;

	/* if ($hour !== 0 && $hour !== "00") return $hour . ":" . $minu . ":" . $second; */

	/* if ($minu !== 0) return $minu . ":" . $second; */

	/* if ($second !== 0) return $minu . ":" . $second; */
}//numDiff2Time函数结束



/* 防注入函数 */
function daddslashes($string, $force = 0) {
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(!MAGIC_QUOTES_GPC || $force) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = daddslashes($val, $force);
			}
		} else {
			$string = addslashes($string);
		}
	}
	return $string;
}


function f_id()
{
	$order_sn = date('ymd').substr(time(),-5).substr(microtime(),2,5);
	return $order_sn;
}

require_once( LIB_PATH."common.php");//加载公共类
$com = new Common();


function cut_str($string, $sublen, $start = 0, $code = 'UTF-8')
{
	if($code == 'UTF-8')
	{
		$pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
		preg_match_all($pa, $string, $t_string);

		if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen));
		return join('', array_slice($t_string[0], $start, $sublen));
	}
	else
	{
		$start = $start*2;
		$sublen = $sublen*2;
		$strlen = strlen($string);
		$tmpstr = '';

		for($i=0; $i< $strlen; $i++)
		{
			if($i>=$start && $i< ($start+$sublen))
			{
				if(ord(substr($string, $i, 1))>129)
				{
					$tmpstr.= substr($string, $i, 2);
				}
				else
				{
					$tmpstr.= substr($string, $i, 1);
				}
			}
			if(ord(substr($string, $i, 1))>129) $i++;
		}
		if(strlen($tmpstr)< $strlen ) $tmpstr.= "";
		return $tmpstr;
	}
}

//一些数组
$user_sex_array=array("0"=>"女","1"=>"男");
$tzgj_ph = array("1"=>"股票","2"=>"房产","3"=>"股权","4"=>"信托","5"=>"银行存款","6"=>"国内基金","7"=>"全球基金","8"=>"短投","9"=>"保险","10"=>"实体投资","11"=>"黄金","12"=>"外汇","13"=>"期货");//投资工具偏好
$tzsj_ph = array(1=>"长期(10年,20年,25年)",2=>"中期(3年,5年,7年)",3=>"短期(1个月,2个月,3个月,1年)");//投资时间偏好
$tzph = array(1=>"高风险高回报",2=>"低风险稳健");//投资偏好

?>

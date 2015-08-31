<?php	
    header('Content-Type:text/html;charset=utf-8');	//编码为utf-8
	
	//$files文件域名称name
	//$filesign文件存放标示就是文件名
	//$path存放的路径
	//$arr允许的类型
	//$fileSize允许的文件的大小一般以kb来计算
	function upload($files,$filesign,$path,$arr,$fileSize)
	{
		if(!($files['size']>0))
		{
			exit(('对不起，请上传文件'));
		}
		//1、判断路径是否存在；
		//1/$path='upload/';
		if(!file_exists($path))
		{
			exit(('对不起文件路径不存在'));
			//mkdir($path);//创建新的文件夹
			//chmod($path, 777);//???			
	
		}
		//2、验证文件类型
		//步骤一：获取扩展名
		$extend=strrchr($files['name'],'.');//strrchr('abcdef','c');cdef,最后一个出现'c'的位置，abcdcef返回还是cef	
		$extend=str_replace('.','',$extend);//意思是在$exten变量里查找到.这个字符在用 （空）来替换。
		$extend=strtolower($extend);//把字符串转成小写
		
		//步骤二：判断后续名是否符合要求
		//2/$arr=array('jpg','jpeg','gif','png','bmp');//组一个白名单的数组，把成员列到一个数组上
		if(!in_array($extend,$arr))//然后用in_array($a,$arr)看$a是否存在于$arr里面。
		{
			$error_msg = "对不起，只支持";
			$error_msg .= implode(",",$arr);
			$error_msg .= "格式的文件";	
			exit($error_msg);
		}
		//步骤三：判断文件大小
		//3/$fileSize
		if($files['size']>$fileSize)//规定文件大小不大于(1024*100)k
		{
			exit(('对不起，文件大小不要超过'.($fileSize/1024/1024).'M'));
		}
		//3、文件上传
		//$newname=$path.time().mt_srand().'.'.$extend;
		$newname=$path.$filesign.'.'.$extend;
		//生成一个文件名为：存在目录/时间函数(不同时间断生成不同的数字).更好的随机数.后续名
		move_uploaded_file($files['tmp_name'],$newname);
		//move_uploaded_file(文件名称，移到哪里去)移动已经上传的文件，剪切
		
		/*如果日后上传文件过多要把文件分类，放在不同目录下：以月份做为标记(按月归档)*/
	
	
		/*$path.=date('Ym').'/';
		if(!file_exists($path))//判断文件路径是否存在
		{
			mkdir($path);//创建新的文件夹
		}
		echo '上传成功';*/
		return $newname;
	}
?>

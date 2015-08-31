<?php
//声明返回 png 图片文件。
header("content-type:image/png");
//开启 session。
session_start(); //启动session
//ini_set('session.gc_maxlifetime',44400);
//四位的验证码。
$checkWord = '';
//验证码的所有可用字符。
$checkChar = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIGKLMNOPQRSTUVWXYZ1234567890';
//获取 4 位随机数。
for($num=0; $num<4; $num++){
  $char=rand(0, strlen($checkChar)-1);
  $checkWord.=$checkChar[$char];
}
//将验证字符放入对话中。
$_SESSION["checkWord"]=strtolower($checkWord);
//创建图片。
$image = imagecreate(80,32);
//字体路径。
$font = "tahoma.ttf";
//设置要用到的颜色。
$red = imagecolorallocate($image,0xf3,0x61,0x61);
$blue = imagecolorallocate($image,0x53,0x68,0xbd);
$green = imagecolorallocate($image,0x6b,0xc1,0x46);
$colors = array($red, $blue, $green);
$gray = imagecolorallocate($image,0xf5,0xf5,0xf5);
//用灰色填充图片。
imagefill($image,0,0,$gray);
//绘制一条干扰线。
imageline($image,rand(0,5),rand(6,18),rand(65,70),rand(6,18),$colors[rand(0,2)]);
//将验证字符绘入图片。
for($num=0; $num<4; $num++){
imagettftext($image, rand(12,16), (rand(0,60)+330)%360, 5+15*$num+rand(0,4), 18+rand(0,4), $colors[rand(0,2)], $font, $checkWord[$num]);
}
//输出图片。
ImagePNG($image);
ImageDestroy($image);
?>
<?php
//�������� png ͼƬ�ļ���
header("content-type:image/png");
//���� session��
session_start(); //����session
//ini_set('session.gc_maxlifetime',44400);
//��λ����֤�롣
$checkWord = '';
//��֤������п����ַ���
$checkChar = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIGKLMNOPQRSTUVWXYZ1234567890';
//��ȡ 4 λ�������
for($num=0; $num<4; $num++){
  $char=rand(0, strlen($checkChar)-1);
  $checkWord.=$checkChar[$char];
}
//����֤�ַ�����Ի��С�
$_SESSION["checkWord"]=strtolower($checkWord);
//����ͼƬ��
$image = imagecreate(80,32);
//����·����
$font = "tahoma.ttf";
//����Ҫ�õ�����ɫ��
$red = imagecolorallocate($image,0xf3,0x61,0x61);
$blue = imagecolorallocate($image,0x53,0x68,0xbd);
$green = imagecolorallocate($image,0x6b,0xc1,0x46);
$colors = array($red, $blue, $green);
$gray = imagecolorallocate($image,0xf5,0xf5,0xf5);
//�û�ɫ���ͼƬ��
imagefill($image,0,0,$gray);
//����һ�������ߡ�
imageline($image,rand(0,5),rand(6,18),rand(65,70),rand(6,18),$colors[rand(0,2)]);
//����֤�ַ�����ͼƬ��
for($num=0; $num<4; $num++){
imagettftext($image, rand(12,16), (rand(0,60)+330)%360, 5+15*$num+rand(0,4), 18+rand(0,4), $colors[rand(0,2)], $font, $checkWord[$num]);
}
//���ͼƬ��
ImagePNG($image);
ImageDestroy($image);
?>
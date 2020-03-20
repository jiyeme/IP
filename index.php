<?php 
include 'src/IpLocation.php';
use itbdw\Ip\IpLocation;

$counter = intval(file_get_contents("counter.dat"));

include 'function.php';

header("Content-type: image/JPEG");
$im = imagecreatefromjpeg("bg.jpg");

$ip = $_SERVER["REMOTE_ADDR"];
$location = IpLocation::getLocation($ip);
$weekarray = array("日","一","二","三","四","五","六");

//先定义一个数组
$get = isset($_GET["s"])?$_GET["s"]:'欢迎';
$get = base64_decode(str_replace(" ","+",$get));

//定义颜色
//定义黑色的值
$black = ImageColorAllocate($im, 0,0,0);
//红色
$red = ImageColorAllocate($im, 255,0,0);
//加载字体
$font = 'msyh.ttf';
//输出
imagettftext($im, 16, 0, 10, 40, $red, $font,'欢迎您来自'.$location['country'].'-'.$location['province'].'-'.$location['city'].'的朋友');
//当前时间添加到图片
imagettftext($im, 16, 0, 10, 72, $red, $font, '今天是'.date('Y年n月j日')."  星期".$weekarray[date("w")]);
//ip
imagettftext($im, 16, 0, 10, 104, $red, $font,'您的IP是:'.$ip);
imagettftext($im, 16, 0, 10, 140, $red, $font,'您使用的是'.$os.'操作系统');
imagettftext($im, 16, 0, 10, 175, $red, $font,'您使用的是'.$bro.'浏览器');
// imagettftext($im, 14, 0, 10, 200, $black, $font, $get);
imagettftext($im, 15, 0, 10, 200, $red, $font,'被偷窥'.$counter.'次');

ImageGif($im);
ImageDestroy($im);

$counter = intval(file_get_contents("counter.dat"));
$_SESSION['#'] = true;
$counter++;
$fp = fopen("counter.dat","w");
fwrite($fp, $counter);
fclose($fp);

// -------------END
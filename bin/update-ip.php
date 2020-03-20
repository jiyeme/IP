<?php
/**
 * Created by PhpStorm.
 * User: zhao.binyan
 * Date: 2019/7/25
 * Time: 下午2:24
 */

/*
		纯真数据库自动更新原理实现
		www.shuax.com 2014.03.27
	*/

/**
 * 示例
 * `php ~/bin/update-ip.php`
 *
 * 更新到指定目录
 * `php ~/bin/update-ip.php -d=/tmp`
 */

date_default_timezone_set("PRC");

//可设置为服务器特定目录，单独，避免组件升级互相影响
$dir = dirname(__DIR__) . "/src";
$option = getopt("d::");
if (isset($option['d'])) {
    if (!is_readable($option['d'])) {
        die("bad param, dir not readable " . $option['d']);
    }
    $dir = $option['d'];
}

$stime = microtime(true);

echo "开始准备更新数据库" . date("Y-m-d H:i:s");
echo "\n";

$copywrite = getUrlData("http://update.cz88.net/ip/copywrite.rar");

if (!$copywrite) {
    $download_spend = $qqwry_time - $stime;
    die("copywrite.rar 下载失败 " . sprintf("下载耗时%s", $download_spend));
}

$qqwry = getUrlData("http://update.cz88.net/ip/qqwry.rar");
$qqwry_time = microtime(true);

if (!$qqwry) {
    $download_spend = $qqwry_time - $stime;
    die("qqwry.rar 下载失败 " . sprintf("下载耗时%s", $download_spend));
}

$key = unpack("V6", $copywrite)[6];
for ($i = 0; $i < 0x200; $i++) {
    $key *= 0x805;
    $key++;
    $key = $key & 0xFF;
    $qqwry[$i] = chr(ord($qqwry[$i]) ^ $key);
}
$qqwry = gzuncompress($qqwry);
$unzip_time = microtime(true);

$download_spend = $qqwry_time - $stime;
$unzip_spend = $unzip_time - $qqwry_time;

if (!$qqwry) {
    die("gzip 解压缩失败 " . sprintf("下载耗时%s，解压耗时%s", $download_spend, $unzip_spend));
}

$tmp_file = $dir . '/' . 'qqwry.dat.bak';
$online_file = $dir . '/' . 'qqwry.dat';

if (file_put_contents($tmp_file, $qqwry)) {
    $put_time = microtime(true);
    $put_spend = $put_time - $unzip_time;
    copy($online_file, $online_file.'.online.bak');
    copy($tmp_file, $online_file);

    $copy_spend = microtime(true) - $put_time;

    //写入数据库更新日期
    $update_log = "update_log.dat";
    $entries = file($update_log);
    $temp = array();
    array_push($temp,date("Y-m-d H:i:s"));
    $entries = implode("",$temp);
    $fp = fopen($update_log,"w");
    flock($fp,LOCK_EX);
    fputs($fp,$entries);
    flock($fp,LOCK_UN);
    fclose($fp);

    die("更新成功 " . sprintf("下载耗时%s，解压耗时%s，写入耗时%s，复制耗时%s", $download_spend, $unzip_spend, $put_spend, $copy_spend));
} else {
    die("更新失败 " . sprintf("下载耗时%s，解压耗时%s", $download_spend, $unzip_spend));
}


function getUrlData($url) {
	    try{
    	    $ch = curl_init();
    	    curl_setopt($ch, CURLOPT_URL, $url);
    	    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.202 Safari/535.1");
    	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); //链接超时时间
    	    $content = curl_exec($ch);
    	    curl_close($ch);
    	    return $content;
	    }
	    catch(Exception $error) {
	        return "";
	    }
	}
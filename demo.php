<link rel="shortcut icon" href="favicon.ico" />

<?php
set_time_limit(0);
date_default_timezone_set('PRC');
header("content-type:text/html; charset=utf-8");
require 'PHPFetion.php';

#============= 账号变量相关配置 =============
$account = '**********';//飞信帐号或者手机号
$password = '***************';	//飞信密码

#============= CURL获取天气预报，请自行修改[说白了，就是采集数据，小偷程序啦~！]  =============
$ch = curl_init('http://www.weather.com.cn/weather/101020100.shtml');
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, 0);
$result = curl_exec($ch);
curl_close($ch);
if(!$result) {
	exit;
}

$bb = '';
$todayWeather = '';
for($i = 1;$i<4;$i++)
{
    preg_match_all("/<!--day ".$i."-->(.|\n)*?table>/i", $result, $todayWeather);
    $aa = strip_tags($todayWeather[0][0]);
    $aa = preg_replace("/\s+/","",$aa);
    $aa = str_replace(' ','',$aa);
    $bb = $bb.$aa.',';
}

$weatherReport = '北京:'.$bb;
unset($todayWeather, $result);
//var_dump($weatherReport);
//die;
#============= 发送对象设置以及发送短信  =============
$mobileArr = array('************');	//收件人
$content = $weatherReport;
$fetion = new PHPFetion($account, $password);// 手机号、飞信密码

$str = '';
foreach($mobileArr as $k => $v)
{
	$retryInit = 0;
	$str .= '发送号码：'.$v."\n"; 
	
	while($retryInit < 2)//若失败再重试1次
	{
        $retryInit++;
        $fetion->sendMSG($v, $content);	// 接收人手机号、飞信内容//刘硕飞信号774843412，手机15221552826
        $sendStatus = json_decode($fetion->GetResultJson(),true);
        $str .= '执行状态：'.$sendStatus['Msg']."\n"; 
		if($sendStatus['Staus'] == 0)
		{
			break;
		}
		sleep(5);
	} 

	$str .= '发送总次数：'.$retryInit."\n"; 
	$str .= '发送时间：'.date('Y-m-d H:i:s')."\n\n"; 
	
}
$str .= $content."\n";
$str .= "===================================== ".date('Y-m-d')." =====================================\n\n\n";
$fp = fopen('send_log.txt', 'a+');
fwrite($fp, $str);
fclose($fp);
echo $fetion->GetResultJson();
$fetion->__destruct();
unset($str, $content, $weatherReport);
?>
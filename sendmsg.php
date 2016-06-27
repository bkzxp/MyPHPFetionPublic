<?php
set_time_limit(0);
date_default_timezone_set('PRC');
header("content-type:text/html; charset=utf-8");
require 'PHPFetion.php';

$account = '**********';//飞信帐号或者手机号
$password = '***********';	//飞信密码
$mobileArr = array('************');	//收件人
$content = htmlspecialchars(trim($_POST['msgContent']));
$fetion = new PHPFetion($account, $password);// 手机号、飞信密码

$str = '';
foreach($mobileArr as $k => $v)
{
	$retryInit = 0;
	$str .= '发送号码：'.$v."\n"; 
	
	while($retryInit < 2)//若失败再重试1次
	{
        $retryInit++;
        $fetion->sendMSG($v, $content);	// 接收人手机号、飞信内容//刘硕飞信号******，手机******
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
$str .= "--------------- ".date('Y-m-d')." ---------------\n\n\n";
$fp = fopen('send_log.txt', 'a+');
fwrite($fp, $str);
fclose($fp);
echo $fetion->GetResultJson();
$fetion->__destruct();
unset($str, $content, $weatherReport);
?>

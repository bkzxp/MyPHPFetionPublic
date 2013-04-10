<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="shortcut icon" href="favicon1.ico" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>飞信接口程序 V1.0 - 2012-03-09</title>
<style type="text/css">
* {
	font-size:14px;
	font-family:Verdana, Geneva, sans-serif;
	line-height:24px;
	color:#333;
}
.all {
	width:800px;
	padding-bottom: 70px;
	margin:0 auto;
}
input {
	height:24px;
	border:1px solid #B4B4B4;
}
</style>
</head>

<body>
<div class="all">
	<form action="sendmsg.php" method="post" target="_self">
		<fieldset>
			<legend><b class="red">WEB飞信 - 在线即时短信接口</b></legend>
			<table width="100%">
	<!-- 		<tr>
					<td align="right">您的手机或飞信号：</td>
					<td><input type="text" name="mobile" size="50" /></td>
				</tr>
				<tr>
					<td align="right">飞信密码：</td>
					<td><input type="password" name="pwd" size="50" /></td>
				</tr>
				<tr>
					<td align="right">好友手机或飞信号：</td>
					<td><input type="text" name="fid" size="50" /></td>
				</tr>		 -->	
				<tr>
					<td align="right">短信内容：：</td>
					<td><textarea style="width:550px; height:220px; overflow:auto;" name="msgContent"></textarea></td>
				</tr>
				<tr>
					<td colspan="2" align="center">
						<input type="submit" name="tj" value="立即发送" />&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="reset" value="重置内容" />
					</td>
				</tr>
			</table>
			
		</fieldset>
	</form>
</div>
</body>
</html>
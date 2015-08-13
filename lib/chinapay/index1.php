<?php
	header('Content-type: text/html; charset=gbk');
?>
<html>
	<head>
		<title>chinapay网上支付接口演示程序(PHP版)</title>
	</head>
<body>
<div style="text-align:center">
	<h1>chinapay网上支付接口演示程序(PHP版)</h1>
	<h2><a href="netpayclient_order_submit.php" target="_blank">支付交易</a></h2>
	<h2><a href="netpayclient_order_submit_ext.php" target="_blank">境外支付</a></h2>
	<h2><a href="netpayclient_query_submit.php" target="_blank">单笔查询</a></h2>
	<h2><a href="netpayclient_refund_submit.php" target="_blank">单笔退款</a></h2>
	<hr>
<?php
	include_once("netpayclient_config.php");
	echo "<h2><font color='red'>本接口需要 mcrypt 和 bcmath 扩展库支持，请查看<a href='phpinfo.php' target='_blank'>PHP配置</a>，确认安装了这两个扩展。</font></h2>";
	
	echo "<h2>当前密钥配置：(<font color='red'>请按照您的实际情况在 netpayclient_config.php 中做适当修改</font>)</h2>";
	echo "<h4>[私钥文件路径：".PRI_KEY."]</h4>";
	echo "<h4>[公钥文件路径：".PUB_KEY."]</h4>";
	
	echo "<h2>本示例安装位置：</h2>";
	echo "<h4>[网络访问地址：$site_url]</h4>";
	echo "<h4>[服务器绝对路径：$_SERVER[DOCUMENT_ROOT]]</h4>";
	
	echo "<h2>服务器IP地址：<font color='green'>[$_SERVER[SERVER_ADDR]]</font></h2>";
	
?>
</div>
</body>
</html>
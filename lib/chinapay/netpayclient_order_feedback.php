<?php
	header('Content-type: text/html; charset=gbk');
	include_once("netpayclient_config.php");
?>
<title>支付应答</title>
<h1>支付应答</h1>
<?php
	//加载 netpayclient 组件
	include_once("netpayclient.php");
	
	//导入公钥文件
	$flag = buildKey(PUB_KEY);
	if(!$flag) {
		echo "导入公钥文件失败！";
		exit;
	}
	
	//获取交易应答的各项值
	$merid = $_REQUEST["merid"];
	$orderno = $_REQUEST["orderno"];
	$transdate = $_REQUEST["transdate"];
	$amount = $_REQUEST["amount"];
	$currencycode = $_REQUEST["currencycode"];
	$transtype = $_REQUEST["transtype"];
	$status = $_REQUEST["status"];
	$checkvalue = $_REQUEST["checkvalue"];
	$gateId = $_REQUEST["GateId"];
	$priv1 = $_REQUEST["Priv1"];
	
	echo "商户号: [$merid]<br/>";
	echo "订单号: [$orderno]<br/>";
	echo "订单日期: [$transdate]<br/>";
	echo "订单金额: [$amount]<br/>";
	echo "货币代码: [$currencycode]<br/>";
	echo "交易类型: [$transtype]<br/>";
	echo "交易状态: [$status]<br/>";
	echo "网关号: [$gateId]<br/>";
	echo "备注: [$priv1]<br/>";
	echo "签名值: [$checkvalue]<br/>";
	echo "===============================<br/>";
	
	//验证签名值，true 表示验证通过
	$flag = verifyTransResponse($merid, $orderno, $amount, $currencycode, $transdate, $transtype, $status, $checkvalue);
	if(!flag) {
		echo "<h2>验证签名失败！</h2>";
		exit;
	}
	echo "<h2>验证签名成功！</h2>";
	//交易状态为1001表示交易成功，其他为各类错误，如卡内余额不足等
	if ($status == '1001'){
		echo "<h3>交易成功！</h3>";
		//您的处理逻辑请写在这里，如更新数据库等。
		//注意：如果您在提交时同时填写了页面返回地址和后台返回地址，且地址相同，请在这里先做一次数据库查询判断订单状态，以防止重复处理该笔订单
		
	} else {
		echo "<h3>交易失败！</h3>";
	}
	

?>
<h5><a href="netpayclient_query_submit.php?transdate=<?php echo $transdate; ?>&ordid=<?php echo $orderno; ?>" target="_blank">查询该笔订单</a></h5>

<h5><a href="netpayclient_refund_submit.php?priv1=<?php echo date('YmdHis');?>&transdate=<?php echo $transdate; ?>&ordid=<?php echo $orderno; ?>&refundamount=<?php echo $amount; ?>&transtype=0002" target="_blank">发起全额退款</a></h5>
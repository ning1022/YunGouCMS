<?php
	header('Content-type: text/html; charset=gbk');
	include_once("netpayclient_config.php");
?>
<title>退款应答</title>
<h1>退款应答</h1>
<?php
	//加载 netpayclient 组件
	include_once("netpayclient.php");
	
	//导入公钥文件
	$flag = buildKey(PUB_KEY);
	if(!$flag) {
		echo "导入公钥文件失败！";
		exit;
	} 
	
	//取得退款应答中的各项值
	$merid = $_REQUEST["MerID"]; 
	$orderno = $_REQUEST["OrderId"];
	$refundamount = $_REQUEST["RefundAmout"];
	$currencycode = $_REQUEST["currencycode"];
	$processdate = $_REQUEST["ProcessDate"];
	$sendtime = $_REQUEST["SendTime"];
	$transtype = $_REQUEST["TransType"];
	$status = $_REQUEST["Status"];
	$checkvalue = $_REQUEST["CheckValue"];
	$priv1 = $_REQUEST["Priv1"];
	
	$plain = $merid . $processdate . $transtype . $orderno . $refundamount . $status . $priv1;
	//本示例在这里记录文件日志，方便您测试是否收到应答
	traceLog("refund.log",$plain);
	
	$flag = verify($plain, $checkvalue);
	if($flag) {
		//验证签名成功，
		echo "<h4>验证签名成功</h4>";
		if ($status == '3'){
			//退款完成
			echo "<h3>退款成功</h3>";
			//请把您自己需要处理的逻辑写在这里，
			

		} else {
			echo "<h3>退款失败</h3>";
		}
	} else {
		echo "<h4>验证签名失败！</h4>";
	}
	
?>
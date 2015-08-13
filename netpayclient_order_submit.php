<?php
	header('Content-type: text/html; charset=gbk');
	include_once("netpayclient_config.php");
	ini_set('display_errors', 'On');
	error_reporting(E_ALL);
?>
<title>支付交易</title>
<?php
	//加载 netpayclient 组件
	include_once("netpayclient.php");
	
	//导入私钥文件, 返回值即为您的商户号，长度15位
	$merid = buildKey(PRI_KEY);
	if(!$merid) {
		echo "导入私钥文件失败！";
		exit;
	}
	
	//生成订单号，定长16位，任意数字组合，一天内不允许重复，本例采用当前时间戳，必填
	//$ordid = "00" . date('YmdHis');
	$ordid='0000'.'90265'.time();
	$ordid=substr($ordid, 0,16);
	//订单金额，定长12位，以分为单位，不足左补0，必填
	$transamt = padstr('1',12);
	//货币代码，3位，境内商户固定为156，表示人民币，必填
	$curyid = "156";
	//订单日期，本例采用当前日期，必填
	$transdate = date('Ymd');
	//交易类型，0001 表示支付交易，0002 表示退款交易
	$transtype = "0001";
	//接口版本号，境内支付为 20070129，必填
	$version = "20070129";
	//页面返回地址(您服务器上可访问的URL)，最长80位，当用户完成支付后，银行页面会自动跳转到该页面，并POST订单结果信息，可选
	$pagereturl = "$site_url/netpayclient_order_feedback.php";
	//后台返回地址(您服务器上可访问的URL)，最长80位，当用户完成支付后，我方服务器会POST订单结果信息到该页面，必填
	$bgreturl = "$site_url/netpayclient_order_feedback.php";
	
	/************************
	页面返回地址和后台返回地址的区别：
	后台返回从我方服务器发出，不受用户操作和浏览器的影响，从而保证交易结果的送达。
	************************/
	
	//支付网关号，4位，上线时建议留空，以跳转到银行列表页面由用户自由选择，本示例选用0001农商行网关便于测试，可选
	$gateid = "";
	//备注，最长60位，交易成功后会原样返回，可用于额外的订单跟踪等，可选
	$priv1 = "memo";
	
	//按次序组合订单信息为待签名串
	$plain = $merid . $ordid . $transamt . $curyid . $transdate . $transtype . $priv1;
	//生成签名值，必填
	$chkvalue = sign($plain);
	if (!$chkvalue) {
		echo "签名失败！";
		exit;
	}
	//var_dump($_POST);
	//
	//
	//
	//
	// //
	// $uri = "http://payment-test.ChinaPay.com/pay/TransGet" ;
	// $useragent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
	// echo $uri;
	// //die();
	// // 参数数组
	// $data = array (
	//         'MerId' => $merid, 
	// 		'Version' => $version,
	// 		"OrdId"=>$ordid,
	// 		"TransAmt"=>$transamt,
	// 		"CuryId"=>$curyid,
	// 		"TransDate"=>$transdate,
	// 		"TransType"=>$transtype,
	// 		"BgRetUrl"=>$bgreturl,
	// 		"PageRetUrl"=>$pagereturl,
	// 		"GateId"=>$gateid,
	// 		"Priv1"=>$priv1,
	// 		"ChkValue"=>$chkvalue
	// );
	// print_r($data);
	 
	// $ch = curl_init ();
	// // print_r($ch);
	// curl_setopt ( $ch, CURLOPT_URL, $uri );
	// curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

	// //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	// curl_setopt ( $ch, CURLOPT_POST, 1 );
	// curl_setopt ($ch, CURLOPT_REFERER, $uri);
	// curl_setopt ($ch, CURLOPT_AUTOREFERER, 1);

	// curl_setopt ( $ch, CURLOPT_HEADER, 0);
	// curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 0 );
	// curl_setopt ($ch, CURLOPT_UNRESTRICTED_AUTH, 1);
	// curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
	// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
	// $a=curl_exec ( $ch );
	// curl_close ( $ch );
	// file_put_contents('a.txt', serialize($a));
	// //header("Location:http://payment-test.ChinaPay.com/pay/TransGet0");
	// //print_r($a);
	// die();
	



?>
<h1>支付交易</h1>
<h3>支付测试方法</h3>
<!-- <h4>点击“支付”按钮，跳转到农商行网关支付页面后，输入卡密和验证码即可完成支付，输入密码时请选择“键盘”</h4> -->
<!-- <h5>卡号：[1234567890000000000]</h5>
<h5>密码：[000000]</h5> -->
<h5><a href="javascript:window.location.reload()">刷新本页以改变订单号</a></h5>


<form action="<?php echo REQ_URL_PAY; ?>" method="post" target="_blank">
<label>商户号</label><br/>
<input type="text" name="MerId" value="<? echo $merid; ?>" readonly/><br/>
<label>支付版本号</label><br/>
<input type="text" name="Version" value="<? echo $version; ?>" readonly/><br/>
<label>订单号</label><br/>
<input type="text" name="OrdId" value="<? echo $ordid; ?>" readonly/><br/>
<label>订单金额</label><br/>
<input type="text" name="TransAmt" value="<? echo $transamt; ?>" readonly/><br/>
<label>货币代码</label><br/>
<input type="text" name="CuryId" value="<? echo $curyid; ?>" readonly/><br/>
<label>订单日期</label><br/>
<input type="text" name="TransDate" value="<? echo $transdate; ?>" readonly/><br/>
<label>交易类型</label><br/>
<input type="text" name="TransType" value="<? echo $transtype; ?>" readonly/><br/>
<label>后台返回地址</label><br/>
<input type="text" name="BgRetUrl" value="<? echo $bgreturl; ?>"/><br/>
<label>页面返回地址</label><br/>
<input type="text" name="PageRetUrl" value="<? echo $pagereturl; ?>"/><br/>
<label>网关号</label><br/>
<input type="text" name="GateId" value="<? echo $gateid; ?>"/><br/>
<label>备注</label><br/>
<input type="text" name="Priv1" value="<? echo $priv1; ?>" readonly/><br/>
<label>签名值</label><br/>
<input type="text" name="ChkValue" value="<? echo $chkvalue; ?>" readonly/><br/>
<input type="submit" value="支付">
</form>
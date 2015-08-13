<?php
	header('Content-type: text/html; charset=gbk');
	include_once("netpayclient_config.php");
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
	$ordid = "0000000000000014";
	//订单金额，定长12位，以分为单位，不足左补0，必填
	//$transamt = padstr('1',12);
	$transamt = "000000001000";
	//货币代码，3位，境内商户固定为156，表示人民币，必填
	$curyid = "JPY";
	//订单日期，本例采用当前日期，必填
	$countryid = "0049";
	//$transdate = date('Ymd');
	$transdate = "20090715";
	//交易类型，0001 表示支付交易，0002 表示退款交易
	$transtype = "0001";
	//接口版本号，境外支付为 20080515，必填
	$version = "20080515";
	//页面返回地址(您服务器上可访问的URL)，最长80位，当用户完成支付后，银行页面会自动跳转到该页面，并POST订单结果信息，可选
	$pagereturl = "$site_url/netpayclient_order_feedback.php";
	//后台返回地址(您服务器上可访问的URL)，最长80位，当用户完成支付后，我方服务器会POST订单结果信息到该页面，必填
	$bgreturl = "$site_url/netpayclient_order_feedback.php";
	
	/************************
	页面返回地址和后台返回地址的区别：
	后台返回从我方服务器发出，不受用户操作和浏览器的影响，从而保证交易结果的送达。
	************************/
	
	//支付网关号，4位，上线时建议留空，以跳转到银行列表页面由用户自由选择，本示例选用0001农商行网关便于测试，可选
	$gateid = "0001";
	//备注，最长60位，交易成功后会原样返回，可用于额外的订单跟踪等，支持中文，但注意要转成GBK编码，可选
	$priv1 = "QH4LLMRT207K5SE1";
	
	$timezone = "+02";
	$transtime = "090402";
	$dstflag = "1";
	$extflag = "00";
	
	//按次序组合订单信息为待签名串
	$plain = $merid . $ordid . $transamt . $curyid . $transdate . $transtime . $transtype . $countryid . $timezone . $dstflag . $extflag . $priv1;
	
	//echo $plain;
	//生成签名值，必填
	$chkvalue = sign($plain);
	if (!$chkvalue) {
		echo "签名失败！";
		exit;
	}
?>
<h1>支付交易</h1>
<h3>支付测试方法</h3>
<h4>点击“支付”按钮，跳转到农商行网关支付页面后，输入卡密和验证码即可完成支付，输入密码时请选择“键盘”</h4>
<h5>卡号：[1234567890000000000]</h5>
<h5>密码：[000000]</h5>
<h5><a href="javascript:window.location.reload()">刷新本页以改变订单号</a></h5>


<form action="<?php echo REQ_URL_PAY; ?>" method="post" target="_blank">
<label>商户号</label><br/>
<input type="text" name="MerId" value="<? echo $merid; ?>" readonly/><br/>
<label>支付版本号</label><br/>
<input type="text" name="Version" value="<? echo $version; ?>" readonly/><br/>
<label>国家代码</label><br/>
<input type="text" name="CountryId" value="<? echo $countryid; ?>" readonly/><br/>
<label>订单号</label><br/>
<input type="text" name="OrdId" value="<? echo $ordid; ?>" readonly/><br/>
<label>订单金额</label><br/>
<input type="text" name="TransAmt" value="<? echo $transamt; ?>" readonly/><br/>
<label>货币代码</label><br/>
<input type="text" name="CuryId" value="<? echo $curyid; ?>" readonly/><br/>
<label>订单日期</label><br/>
<input type="text" name="TransDate" value="<? echo $transdate; ?>" readonly/><br/>
<label>订单时间</label><br/>
<input type="text" name="TransTime" value="<? echo $transtime; ?>" readonly/><br/>
<label>时区</label><br/>
<input type="text" name="TimeZone" value="<? echo $timezone; ?>" readonly/><br/>
<label>交易类型</label><br/>
<input type="text" name="TransType" value="<? echo $transtype; ?>" readonly/><br/>
<label>后台返回地址</label><br/>
<input type="text" name="BgRetUrl" value="<? echo $bgreturl; ?>"/><br/>
<label>页面返回地址</label><br/>
<input type="text" name="PageRetUrl" value="<? echo $pagereturl; ?>"/><br/>
<label>网关号</label><br/>
<input type="text" name="GateId" value="<? echo $gateid; ?>"/><br/>
<label>夏令时标志</label><br/>
<input type="text" name="DSTFlag" value="<? echo $dstflag; ?>" readonly/><br/>
<label>扩展标志</label><br/>
<input type="text" name="ExtFlag" value="<? echo $extflag; ?>" readonly/><br/>
<label>备注</label><br/>
<input type="text" name="Priv1" value="<? echo $priv1; ?>" readonly/><br/>
<label>签名值</label><br/>
<input type="text" name="ChkValue" value="<? echo $chkvalue; ?>" readonly/><br/>
<input type="submit" value="支付">
</form>
<?php 

include dirname(__FILE__).DIRECTORY_SEPARATOR."chinapay".DIRECTORY_SEPARATOR."netpayclient_config.php";
include dirname(__FILE__).DIRECTORY_SEPARATOR."chinapay".DIRECTORY_SEPARATOR."netpayclient.php";
class chinapay {
	
	private $config;
	private $url=null;
	//主入口
	public function config($config=null){
		//导入私钥文件, 返回值即为您的商户号，长度15位
	$merid = buildKey(PRI_KEY);
	if(!$merid) {
		echo "导入私钥文件失败！";
		exit;
	}
	
	//生成订单号，定长16位，任意数字组合，一天内不允许重复，本例采用当前时间戳，必填
	//$ordid = "00" . date('YmdHis');
	$ordid=$config['code'];
	//$ordid='0000'.'90265'.time();
	//$ordid=substr($ordid, 0,16);
	//订单金额，定长12位，以分为单位，不足左补0，必填
	//$transamt = $config['money'];
	$money = $config['money']*100;
	$transamt = padstr($money,12);
	//货币代码，3位，境内商户固定为156，表示人民币，必填
	$curyid = "156";
	//订单日期，本例采用当前日期，必填
	$transdate = date('Ymd');
	//交易类型，0001 表示支付交易，0002 表示退款交易
	$transtype = "0001";
	//接口版本号，境内支付为 20070129，必填
	$version = "20070129";
	//页面返回地址(您服务器上可访问的URL)，最长80位，当用户完成支付后，银行页面会自动跳转到该页面，并POST订单结果信息，可选
	$pagereturl = $config['ReturnUrl'];
	//后台返回地址(您服务器上可访问的URL)，最长80位，当用户完成支付后，我方服务器会POST订单结果信息到该页面，必填
	$bgreturl = $config['NotifyUrl'];
	
	/************************
	页面返回地址和后台返回地址的区别：
	后台返回从我方服务器发出，不受用户操作和浏览器的影响，从而保证交易结果的送达。
	************************/
	
	//支付网关号，4位，上线时建议留空，以跳转到银行列表页面由用户自由选择，本示例选用0001农商行网关便于测试，可选
	$gateid = "0001";
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

	//$uri = REQ_URL_PAY ;
	//$useragent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
	//echo $uri;
	//die();
	// 参数数组
	$data = array (
	        'MerId' => $merid, 
			'Version' => $version,
			"OrdId"=>$ordid,
			"TransAmt"=>$transamt,
			"CuryId"=>$curyid,
			"TransDate"=>$transdate,
			"TransType"=>$transtype,
			"BgRetUrl"=>$bgreturl,
			"PageRetUrl"=>$pagereturl,
			"GateId"=>$gateid,
			"Priv1"=>$priv1,
			"ChkValue"=>$chkvalue
	);
	echo "<form action=".REQ_URL_PAY." method='post' id='myform'  >";
	echo "<br>";
	echo "<input type='text' name='MerId' value='".$merid."' />";
	echo "<br>";

	echo "<input type='text' name='Version' value='".$version."' />";
	echo "<br>";

	echo "<input type='text' name='OrdId' value='".$ordid."'/>";
	echo "<br>";

	echo "<input type='text' name='TransAmt' value='".$transamt."' />";
	echo "<br>";

	echo "<input type='text' name='CuryId' value='".$curyid."'/>";
	echo "<br>";
	echo "<input type='text' name='TransDate' value='".$transdate."' />";


	echo "<input type='text' name='TransType' value='".$transtype."' />";
	echo "<br>";

	echo "<input type='text' name='BgRetUrl' value='".$bgreturl."' />";
	echo "<br>";

	echo "<input type='text' name='PageRetUrl' value='".$pagereturl."' />";
	echo "<br>";

	echo "<input type='text' name='GateId' value='".$gateid."' />";
	echo "<br>";

	echo "<input type='text' name='Priv1' value='".$priv1."' />";
	echo "<br>";

	echo "<input type='text' name='ChkValue' value='".$chkvalue."' />";
	echo "<br>";

	echo "</form>";
	//die();
	echo "<script>";
	echo "document.getElementById('myform').submit();";
	echo "</script>";
	//print_r($data);
	 
	// $ch = curl_init ();
	// // print_r($ch);
	// curl_setopt ( $ch, CURLOPT_URL, $uri );
	// curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

	// //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	// curl_setopt ( $ch, CURLOPT_POST, 1 );
	// curl_setopt ($ch, CURLOPT_REFERER, $uri);
	// curl_setopt ($ch, CURLOPT_AUTOREFERER, 1);

	// curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	// curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	// // curl_setopt ($ch, CURLOPT_REFERER, "http://www.php100.com/");
	// curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
	// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
	// $return = curl_exec ( $ch );
	// curl_close ( $ch );
	 
	//print_r($return);
	//die();

		
		
	}
	//发送
	public function send_pay(){
		$this->url=$bgreturl ;
		 echo  $this->url;
		 
		header("Location: $url");	
		exit;
	}
}

?>

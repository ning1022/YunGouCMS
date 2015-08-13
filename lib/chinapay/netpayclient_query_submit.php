<?php
	header('Content-type: text/html; charset=gbk');
	include_once("netpayclient_config.php");
?>
<title>单笔查询</title>
<h1>单笔查询</h1>
<?php
	//加载 netpayclient 组件
	include_once("netpayclient.php");
	//加载 CURL 函数库，该库由 chinapay 提供，方便您使用 curl 发送 HTTP 请求
	include_once("lib_curl.php");
	
	//导入私钥文件, 返回值即为您的商户号，长度15位
	$merid = buildKey(PRI_KEY);
	if(!$merid) {
		echo "导入私钥文件失败！";
		exit;
	}
	
	//需要查询的订单号，16位长
	$ordid = $_REQUEST["ordid"];
	//订单日期，8位长
	$transdate = $_REQUEST["transdate"];
	
	if($transdate=='') $transdate = date('Ymd');
	
?>
<form action="" method="get">
<label>订单日期</label><br/>
<input type="text" name="transdate" value="<?php echo $transdate; ?>"><br/>
<label>订单号</label><br/>
<input type="text" name="ordid" value="<?php echo $ordid; ?>"><br/>
<input type="submit" value="查询">
</form>
<?
	if(($ordid!='')&&($transdate!='')){
		//交易类型，固定为支付交易 0001
		$transtype = "0001";
		//接口版本号，单笔查询，固定为 20060831，必填
		$version = "20060831";
		//备注，最长60位，可选
		$resv = "memo";
		
		//按次序组合报文信息为待签名串
		$plain = $merid . $transdate . $ordid . $transtype;
		//生成签名值，必填
		$chkvalue = sign($plain);
		if (!$chkvalue) {
			echo "签名失败！";
			exit;
		}
		
		$http = HttpInit();
		$post_data = "MerId=$merid&TransType=$transtype&OrdId=$ordid&TransDate=$transdate&Version=$version&Resv=$resv&ChkValue=$chkvalue";
		$output = HttpPost($http, $post_data, REQ_URL_QRY);
		
		if($output){
			$output = trim(strip_tags($output));
			
			echo "<h2>查询返回</h2>";
			echo htmlspecialchars($output) . "<br/>";
			echo "=================================<br/>";
			//开始解析数据
			$datas = explode("&",$output);
			$extracted_data = array();
			foreach($datas as $data){
				echo "$data<br/>";
				$name_value = explode('=',$data);
				if(count($name_value)==2){
					$extracted_data[$name_value[0]] = $name_value[1];
				}
			}
			
			echo "=================================<br/>";
			
			$resp_code = $extracted_data["ResponseCode"];
			if($resp_code == '0'){
				$merid = $extracted_data["merid"]; 
				$orderno = $extracted_data["orderno"];
				$amount = $extracted_data["amount"];
				$currencycode = $extracted_data["currencycode"];
				$transdate = $extracted_data["transdate"];
				$transtype = $extracted_data["transtype"];
				$status = $extracted_data["status"];
				$checkvalue = $extracted_data["checkvalue"];
				
				$gateid = $extracted_data["GateId"];
				$priv1 = $extracted_data["Priv1"];
			} else {
				$message = $extracted_data["Message"];
			}
			
			switch($resp_code){
								
				case '111': echo "<h3>$message</h3>"; 
										echo "<h4>请确认你是否申请开通了此业务，并提供了正确的公网IP地址</h4>";
										break;
				case '307': echo "<h3>$message</h3>"; 
										echo "<h4>您填写的订单号或订单日期有误，未能查询到该笔订单</h4>";
										break;
				case '0'  : echo "<h3>查询请求发送成功</h3>";
				
										//开始验证签名，首先导入公钥文件
										$flag = buildKey(PUB_KEY);
										if(!$flag) {
											echo "导入公钥文件失败！";
										} else {
											$flag = verifyTransResponse($merid, $orderno, $amount, $currencycode, $transdate, $transtype, $status, $checkvalue);
											if($flag) {
												//验证签名成功，
												echo "<h4>验证签名成功</h4>";
												//请把您自己需要处理的逻辑写在这里
												
											} else {
												echo "<h4>验证签名失败！</h4>";
											}
										}
										break;
				default   : echo "<h3>查询请求发送失败</h3>";
										echo "<h4>请查阅接口文档附录以确定具体错误原因！</h4>";
										break;
				
			}
		} else {
			echo "<h3>HTTP 请求失败！</h3>";
		}
		HttpDone($http);
	} else {
		echo "<h3>请填写订单日期和订单号</h3>";
	}
?>
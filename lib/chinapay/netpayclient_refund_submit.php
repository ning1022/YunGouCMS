<?php
	header('Content-type: text/html; charset=gbk');
	include_once("netpayclient_config.php");
?>
<title>单笔退款</title>
<h1>单笔退款</h1>
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
	
	//需要查询的订单号，16位长，必填
	$ordid = $_REQUEST["ordid"];
	//订单日期，8位长，必填
	$transdate = $_REQUEST["transdate"];
	//退款金额，必须小于原始交易金额，12位长，不足左补0，必填
	$refundamount = $_REQUEST["refundamount"];
	//交易类型，4位长，此处表示退款方式 0002 为全额退款，0102 为部分退款，必填
	$transtype = $_REQUEST["transtype"];
	//备注，退款时用作退款流水号，一天内不可重复，最长40位，必填，此处使用当前时间戳仅用作演示
	$priv1 = date('YmdHis');
	
	if($transdate=='') $transdate = date('Ymd');
	
	if($refundamount=='') $refundamount = padstr('1',12);
	
	if($transtype=='') $transtype = '0002';
	
?>
<form action="" method="get">
<label>退款流水</label><br/>
<input type="text" name="priv1" value="<?php echo $priv1; ?>"><br/>
<label>订单日期</label><br/>
<input type="text" name="transdate" value="<?php echo $transdate; ?>"><br/>
<label>订单号</label><br/>
<input type="text" name="ordid" value="<?php echo $ordid; ?>"><br/>
<label>退款金额</label><br/>
<input type="text" name="refundamount" value="<?php echo $refundamount; ?>"><br/>
<label>退款方式</label><br/>
<select name="transtype">
<option value="0002">首次全额退款</option>
<option value="0012">二次全额退款</option>
<option value="0102">首次部分退款</option>
<option value="0112">二次部分退款</option>
</select>
<input type="submit" value="退款">
</form>
<?
	if(($ordid!='')&&($transdate!='')&&($refundamount!='')){
		//接口版本号，单笔退款，固定为 20070129，必填
		$version = "20070129";
		//退款返回地址，退款提交后需经过人工审核处理，并在T+2日后完成，此时我方服务器将POST退款结果到该地址
		$returnurl = "$site_url/netpayclient_refund_feedback.php";
		
		//按次序组合报文信息为待签名串
		$plain = $merid . $transdate . $transtype . $ordid . $refundamount . $priv1;
		//生成签名值，必填
		$chkvalue = sign($plain);
		if (!$chkvalue) {
			echo "签名失败！";
			exit;
		}
		
		//生产环境提交地址为 ,更换生产密钥后请注意修改
		
		$http = HttpInit();
		$post_data = "MerID=$merid&TransType=$transtype&OrderId=$ordid&RefundAmount=$refundamount&TransDate=$transdate&Version=$version&ReturnURL=$returnurl&Priv1=$priv1&ChkValue=$chkvalue";
		$output = HttpPost($http, $post_data, REQ_URL_REF);
		
		if($output){
			$output = trim(strip_tags($output));
			
			echo "<h2>退款返回</h2>";
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
				$merid = $extracted_data["MerID"]; 
				$orderno = $extracted_data["OrderId"];
				$refundamount = $extracted_data["RefundAmout"];
				$currencycode = $extracted_data["currencycode"];
				$processdate = $extracted_data["ProcessDate"];
				$sendtime = $extracted_data["SendTime"];
				$transtype = $extracted_data["TransType"];
				$status = $extracted_data["Status"];
				$checkvalue = $extracted_data["CheckValue"];
				$priv1 = $extracted_data["Priv1"];
			} else {
				$message = $extracted_data["Message"];
			}
			
			switch($resp_code){
								
				case '111': echo "<h3>$message</h3>"; 
										echo "<h4>请确认你是否申请开通了此业务，并提供了正确的公网IP地址</h4>";
										break;
				case '0'  : echo "<h3>退款请求发送成功</h3>";
				
										//开始验证签名，首先导入公钥文件
										$flag = buildKey(PUB_KEY);
										if(!$flag) {
											echo "导入公钥文件失败！";
										} else {
											$plain = $merid . $processdate . $transtype . $orderno . $refundamount . $status . $priv1;
											$flag = verify($plain, $checkvalue);
											if($flag) {
												//验证签名成功，
												echo "<h4>验证签名成功</h4>";
												if ($status == '1'){
													//chinapay 接收退款请求成功，进入人工审核阶段
													echo "<h3>退款提交成功</h3>";
													//请把您自己需要处理的逻辑写在这里
													
													
												}
												
											} else {
												echo "<h4>验证签名失败！</h4>";
											}
										}
										break;
				default   : echo "<h3>退款请求发送失败</h3>";
										echo "<h4>请查阅接口文档附录以确定具体错误原因！</h4>";
										break;
				
			}
		} else {
			echo "<h3>HTTP 请求失败！</h3>";
		}
		HttpDone($http);
	} else {
		echo "<h3>请填写订单号，退款金额等必填项</h3>";
	}
?>
<?php 

defined('G_IN_SYSTEM')or exit('No permission resources.');
ini_set("display_errors","On");
error_reporting(E_ALL);
include dirname(__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'chinapay'.DIRECTORY_SEPARATOR.'netpayclient_config.php';
include dirname(__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'chinapay'.DIRECTORY_SEPARATOR.'netpayclient.php';

class chinapay_url extends SystemAction {
	private $out_trade_no;
	public function __construct(){			
		$this->db=System::load_sys_class('model');		
	} 	
	
	
	public function qiantai(){
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
		$flag = verifyTransResponse($merid, $orderno, $amount, $currencycode, $transdate, $transtype, $status, $checkvalue);

		//sleep(2);
		$out_trade_no =$orderno;	//商户订单号
		$dingdaninfo = $this->db->GetOne("select * from `@#_member_addmoney_record` where `code` = '$out_trade_no'");
		// var_dump($_GET);
		// echo "<br>";
		// var_dump($_POST);
		// echo "<br>";

		//var_dump($dingdaninfo);
		if(!$dingdaninfo || $dingdaninfo['status'] == '未付款'){
			// echo "支付失败";
			// die();
			_message("支付失败");			
		}else{
			if(empty($dingdaninfo['scookies'])){
				_message("充值成功!",WEB_PATH."/member/home/userbalance");
			}else{
				if($dingdaninfo['scookies'] == '1'){
					_message("支付成功!",WEB_PATH."/member/cart/paysuccess");
				}else{
					_message("商品还未购买,请重新购买商品!",WEB_PATH."/member/cart/cartlist");
				}
					
			}
		}


	}
	public function houtai1(){
		$f=fopen(dirname(__FILE__).DIRECTORY_SEPARATOR.'aa.txt', 'w');
		fwrite($f, 'ssssssss');
		fclose($f);
	}
	
	public function houtai(){

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
		
		$flag = verifyTransResponse($merid, $orderno, $amount, $currencycode, $transdate, $transtype, $status, $checkvalue);
		if(!flag) {
			echo "<h2>验证签名失败！</h2>";
			exit;
		}

		// file_put_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'aa.txt', serialize($status));

		$out_trade_no = $orderno;	//商户订单号

		if($status == '1001') {
			$dingdaninfo = $this->db->GetOne("select * from `@#_member_addmoney_record` where `code` = '$out_trade_no' and `status` = '未付款'");
			if(!$dingdaninfo){	echo "fail";exit;}	//没有该订单,失败
			$c_money = intval($dingdaninfo['money']);			
			$uid = $dingdaninfo['uid'];
			$time = time();
			$this->db->Autocommit_start();
			$up_q1 = $this->db->Query("UPDATE `@#_member_addmoney_record` SET `pay_type` = '支付宝', `status` = '已付款' where `id` = '$dingdaninfo[id]' and `code` = '$dingdaninfo[code]'");
			$up_q2 = $this->db->Query("UPDATE `@#_member` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
			$up_q3 = $this->db->Query("INSERT INTO `@#_member_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '充值', '$c_money', '$time')");
				
			if($up_q1 && $up_q2 && $up_q3){
				$this->db->Autocommit_commit();			
			}else{
				$this->db->Autocommit_rollback();
				echo "fail";exit;
			}			
			if(empty($dingdaninfo['scookies'])){					
					echo "success";exit;	//充值完成			
			}			
			$scookies = unserialize($dingdaninfo['scookies']);			
			$pay = System::load_app_class('pay','pay');			
			$pay->scookie = $scookies;	
			// var_dump($pay_type['pay_id']);
			// die();

			$ok = $pay->init($uid,$pay_type['pay_id'],'go_record');	//云购商品	
			if($ok != 'ok'){
				_setcookie('Cartlist',NULL);
				echo "fail";exit;	//商品购买失败			
			}			
			$check = $pay->go_pay(1);
			if($check){
				$this->db->Query("UPDATE `@#_member_addmoney_record` SET `scookies` = '1' where `code` = '$out_trade_no' and `status` = '已付款'");
				_setcookie('Cartlist',NULL);
				echo "success";exit;			
			}else{
				echo "fail";exit;
			}
		}
	
		

	}//function end
	
	
	
	
	
}//

?>
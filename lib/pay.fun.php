<?php 



/*
*   生成购买的云购码
*	user_num 		@生成个数
*	shopinfo		@商品信息
*	ret_data		@返回信息
*/
function pay_get_shop_codes($user_num=1,$shopinfo=null,&$ret_data=null){
	
		$db = System::load_sys_class("model");
		$ret_data['query'] = true;
		$table = '@#_'.$shopinfo['codes_table'];
		$codes_arr = array();
		$codes_one = $db->GetOne("select * from `$table` where `s_id` = '$shopinfo[id]' order by `s_cid` DESC  LIMIT 1 for update");
		$codes_arr[$codes_one['s_cid']] = $codes_one;		
		$codes_count_len = $codes_arr[$codes_one['s_cid']]['s_len'];

		if($codes_count_len < $user_num && $codes_one['s_cid'] > 1){		
			for($i=$codes_one['s_cid']-1;$i>=1;$i--):
				$codes_arr[$i] = $db->GetOne("select * from `$table` where `s_id` = '$shopinfo[id]' and `s_cid` = '$i'  LIMIT 1 for update");
				$codes_count_len += $codes_arr[$i]['s_len'];			
				if($codes_count_len > $user_num)  break;
			endfor;
		}
		
		if($codes_count_len < $user_num) $user_num = $codes_count_len;
		
		$ret_data['user_code'] = '';
		$ret_data['user_code_len'] = 0;
		
		foreach($codes_arr as $icodes){			
			$u_num = $user_num;			
			$icodes['s_codes'] = unserialize($icodes['s_codes']);	
			$code_tmp_arr = array_slice($icodes['s_codes'],0,$u_num);
			$ret_data['user_code'] .= implode(',',$code_tmp_arr);	
			$code_tmp_arr_len = count($code_tmp_arr);
			
			if($code_tmp_arr_len < $u_num){
				$ret_data['user_code'] .= ',';
			}
			
			$icodes['s_codes'] = array_slice($icodes['s_codes'],$u_num,count($icodes['s_codes']));
			$icode_sub = count($icodes['s_codes']);		
			$icodes['s_codes'] = serialize($icodes['s_codes']);

			if(!$icode_sub){
				$query = $db->Query("UPDATE `$table` SET `s_cid` = '0',`s_codes` = '$icodes[s_codes]',`s_len` = '$icode_sub' where `id` = '$icodes[id]'");
				if(!$query)$ret_data['query'] = false;
			}else{		
				$query = $db->Query("UPDATE `$table` SET `s_codes` = '$icodes[s_codes]',`s_len` = '$icode_sub' where `id` = '$icodes[id]'");
				if(!$query)$ret_data['query'] = false;
			}
			$ret_data['user_code_len'] += $code_tmp_arr_len;
			$user_num  = $user_num - $code_tmp_arr_len;
			
			
		}
		
}


//生成订单号
function pay_get_dingdan_code($dingdanzhui=''){
	return $dingdanzhui.time().substr(microtime(),2,6).rand(0,9);
}



function pay_insert_shop($shop='',$type=''){
	$time=sprintf("%.3f",microtime(true)+(int)System::load_sys_config('system','goods_end_time'));
	$db = System::load_sys_class("model");
	if($shop['xsjx_time'] != '0'){
		return $db->Query("UPDATE `@#_shoplist` SET `canyurenshu`=`zongrenshu`,	`shenyurenshu` = '0' where `id` = '$shop[id]'");
	}
	$tocode = System::load_app_class("tocode","pay");
	$tocode->shop = $shop;	
	$tocode->run_tocode($time,100,$shop['canyurenshu'],$shop);

	$code =$tocode->go_code;	
	$content =$tocode->go_content;
	$counttime = $tocode->count_time;	
	$u_go_info = $db->GetOne("select * from `@#_member_go_record` where `shopid` = '$shop[id]' and `shopqishu` = '$shop[qishu]' and `goucode` LIKE  '%$code%'");	
	$u_info = $db->GetOne("select * from `@#_member` where `uid` = '$u_go_info[uid]'");
	$q_user = serialize($u_info);

	//更新商品
	$query = true;
	if($u_info){		
		$gtimes = (int)System::load_sys_config('system','goods_end_time');
		if($gtimes == 0 || $gtimes == 1){
			$q_showtime = 'N';
		}else{
			$q_showtime = 'Y';
		}
		$q = $db->Query("UPDATE `@#_shoplist` SET 
							`canyurenshu`=`zongrenshu`,
							`shenyurenshu` = '0',
							`q_uid` = '$u_info[uid]',
							`q_user` = '$q_user',
							`q_user_code` = '$code',
							`q_content`	= '$content',
							`q_counttime` ='$counttime',
							`q_end_time` = '$time',
							`q_showtime` = '$q_showtime'
							 where `id` = '$shop[id]'");
		if(!$q) $query = false;		
		$q = $db->Query("UPDATE `@#_member_go_record` SET `huode` = '$code' where `id` = '$u_go_info[id]' and `code` = '$u_go_info[code]' and `uid` = '$u_go_info[uid]' and `shopid` = '$shop[id]' and `shopqishu` = '$shop[qishu]'");
		if(!$q) $query = false;
		
		$post_arr= array("uid"=>$u_info['uid'],"gid"=>$shop['id'],"send"=>1);
		_g_triggerRequest(WEB_PATH.'/api/send/send_shop_code',false,$post_arr);
	}
	
	/******************************/
	
	//新建
	if($shop['qishu'] < $shop['maxqishu']){		
		$time = time();
		System::load_app_fun("content",G_ADMIN_DIR);		
		$goods = $shop;
		$qishu = $goods['qishu']+1;
		$shenyurenshu = $goods['zongrenshu'] - $goods['def_renshu'];
		$query_table = content_get_codes_table();
		$q = $db->Query("INSERT INTO `@#_shoplist` (`sid`,`cateid`, `brandid`, `title`, `title_style`, `title2`, `keywords`, `description`, `money`, `yunjiage`, `zongrenshu`, `canyurenshu`,`shenyurenshu`,`def_renshu`, `qishu`,`maxqishu`,`thumb`, `picarr`, `content`,`codes_table`,`xsjx_time`,`renqi`,`pos`, `time`)
				VALUES
				('$goods[sid]','$goods[cateid]','$goods[brandid]','$goods[title]','$goods[title_style]','$goods[title2]','$goods[keywords]','$goods[description]','$goods[money]','$goods[yunjiage]','$goods[zongrenshu]','$goods[def_renshu]','$shenyurenshu','$goods[def_renshu]','$qishu','$goods[maxqishu]','$goods[thumb]','$goods[picarr]','$goods[content]','$query_table','$goods[xsjx_time]','$goods[renqi]','$goods[pos]','$time')
				");		
		if(!$q) return $query;	
		$id = $db->insert_id();
		$q = content_get_go_codes($goods['zongrenshu'],3000,$id);
		if(!$q) return $query;
	}	

	return $query;
}


/*
	云购基金
	go_number @云购人次
*/
function pay_go_fund($go_number=null){
	if(!$go_number)return true;
	$db = System::load_sys_class("model");
	$fund = $db->GetOne("select * from `@#_fund` where 1");
	if($fund && $fund['fund_off']){
		$money = $fund['fund_money'] * $go_number + $fund['fund_count_money'];
		return $db->Query("UPDATE `@#_fund` SET `fund_count_money` = '$money'");
	}else{
		return true;
	}
}

/*
	用户佣金
	uid 		用户id
	dingdancode	@订单号
*/
function pay_go_yongjin($uid=null,$dingdancode=null){
	if(!$uid || !$dingdancode)return true;
	$db = System::load_sys_class("model");$time=time();
	$config = System::load_app_config("user_fufen",'','member');//福分/经验/佣金
	$yesyaoqing=$db->GetOne("SELECT `yaoqing` FROM `@#_member` WHERE `uid`='$uid'");
	if($yesyaoqing['yaoqing']){
		$yongjin=$config['fufen_yongjin']; //每一元返回的佣金				
	}else{
		return true;
	}	
	$yongjin = floatval(substr(sprintf("%.3f",$yongjin), 0, -1));
	$gorecode=$db->GetList("SELECT * FROM `@#_member_go_record` WHERE `code`='$dingdancode'");
	foreach($gorecode as $val){
		$y_money=$val['moneycount'] * $yongjin;
		$content="(第".$val['shopqishu']."期)".$val['shopname'];
		$db->Query("INSERT INTO `@#_member_recodes`(`uid`,`type`,`content`,`shopid`,`money`,`ygmoney`,`time`)VALUES('$uid','1','$content','$val[shopid]','$y_money','$val[moneycount]','$time' )"); 				
	}
	
}

?>


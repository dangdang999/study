<?php
defined('IN_IA') or exit('Access Denied');
$operation = empty($operation) ? 'list' : $operation;
$method = empty($_GPC['method'])?'getself':trim($_GPC['method']);

if($isMobile){
	$itoastStatus = "success";
}else{
	$itoastStatus = "error";
}
if($operation == 'list'){
	$setting = uni_setting_load('payment', $_W['uniacid']);
	$pay_setting = $setting['payment'];
	$status = intval($_GPC['status']);
	$condition = " and store_id=".$store_id;
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$searchfield = trim($_GPC['searchfield']);
	$keyword = trim($_GPC['keyword']);
	if($searchfield == 'ordernumber' && !empty($keyword)){
		$condition .= " and order_number like '{$keyword}'";
	}
	if($searchfield == 'ordersn' && !empty($keyword)){
		$condition .= " and ordersn like '%{$keyword}%'";
	}
	if(empty($status)){
		$condition .= " and status > 0";
	}else{
		$condition .= " and status = ".$status;
	}
	if($method == 'getself'){
		$condition .= " and order_type = 1";
	}elseif($method == 'takeout'){
		$condition .= " and order_type = 2";
	}
	$list = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_order')." WHERE uniacid=:uniacid ".$condition." ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':uniacid'=>$_W['uniacid']));

	foreach ($list as $key2 => &$row) {
		$deliverText = "";
		$row['storeInfo'] = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$row['store_id']),array('name'));
		if(!empty($row['desk_id'])){
			$row['deskInfo'] = pdo_get("deamx_food_desknumber",array('uniacid'=>$_W['uniacid'],'id'=>$row['desk_id'],'store_id'=>$row['store_id']),array('id','name'));
		}
		$type_count = 0;
        $row['new_order'] = 0;
		$row['goods_list'] = json_decode($row['goods_list'],true);
        if (empty($row['goods_list'][0]['goodsid'])) {
            //新版
            $row['new_order'] = 1;
        }
        $newGoodsList = array();
		foreach ($row['goods_list'] as $key => $goods) {
            if ($row['new_order'] == 1) {
                //新版
                $newGoodsList[$key] = $goods;
            } else {
                //旧版
                if ($goods['hasoption'] == '1' && $goods['count'] > 0) {
                    //有规格
                    foreach ($goods['options'] as $options) {
                        if ($options['count'] > 0) {
                            $newGoodsList[] = array(
                                'goodsId'       =>  $goods['goodsid'],
                                'name'          =>  $goods['name'],
                                'count'         =>  $options['count'],
                                'optionName'    =>  $options['name'],
                                'optionsId'     =>  $options['id'],
                                'optionsRealId' =>  $options['id'],
                                'perPrice'      =>  $options['marketprice'],
                                'price'         =>  $options['marketprice'] * $options['count']
                            );
                        }

                    }
                } else {
                    if ($goods['count'] > 0) {
                        $newGoodsList[] = array(
                            'goodsId'       =>  $goods['goodsid'],
                            'name'          =>  $goods['name'],
                            'count'         =>  $goods['count'],
                            'optionName'    =>  "",
                            'optionsId'     =>  0,
                            'optionsRealId' =>  0,
                            'perPrice'      =>  $goods['marketprice'],
                            'price'         =>  $goods['marketprice'] * $goods['count']
                        );
                    }
                }
            }
		}
        $row['goods_list'] = $newGoodsList;
		if($row['order_type'] == '2'){
			$row['address_info'] = @json_decode($row['address_info'],true);
			if($row['deliver_type'] == '1'){
				$deliverLog = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_deliver_log')." WHERE order_id=:order_id ORDER BY updatetime ASC",array(':order_id'=>$row['ordersn']));
				foreach ($deliverLog as $key => $log) {
					$remark = json_decode($log['remark'],true);
					if($log['status'] == '1'){
						$statusText = "待接单";
					}elseif($log['status'] == '2'){
						$statusText = "待取货";
					}elseif($log['status'] == '3'){
						$statusText = "配送中";
					}elseif($log['status'] == '4'){
						$statusText = "已完成";
					}elseif($log['status'] == '5'){
						$statusText = "已取消";
					}elseif($log['status'] == '7'){
						$statusText = "已过期";
					}else{
						$statusText = "未知状态";
					}
					$deliverText .= ($key+1).". ".date("Y-m-d H:i:s",$log['updatetime'])." ".$statusText." 配送员：".$remark['dm_name']." 联系方式：".$remark['dm_mobile']."<BR>";
				}
				$row['deliverText'] = $deliverText;
			}
		}elseif($row['order_type'] == '1'){
            if ($row['getfood_time'] == '现在下单，稍后即取') {
                $row['getfood_time'] = "立即取餐";
            } else {
                $row['getfood_time'] = str_replace("取餐", "", $row['getfood_time']);
                $row['getfood_time'] = $row['getfood_time'] . "取餐";
            }
		}
		$row['type_count'] = $type_count;
	}
	unset($row);
	unset($goods_list);
	unset($options);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('deamx_food_order') . " WHERE uniacid = '{$_W['uniacid']}' ".$condition);
	$pager = pagination($total, $pindex, $psize);
}elseif($operation == 'detail'){
	$orderid = intval($_GPC['id']);
	$orderItem = pdo_get("deamx_food_order",array('uniacid'=>$_W['uniacid'],'id'=>$orderid,'store_id'=>$store_id));

	$type_count = 0;
	$orderItem['goods_list'] = json_decode($orderItem['goods_list'],true);
	foreach ($orderItem['goods_list'] as &$goods_list) {
		$goods_list['img'] = pdo_getcolumn("deamx_food_goods",array('uniacid'=>$_W['uniacid'],'id'=>$goods_list['goodsid']), 'img',1);
		if(empty($goods_list['hasoption']) && $goods_list['count']>0){
			$type_count ++;
		}else{
			foreach ($goods_list['options'] as $options) {
				if($options['count']>0){
					$type_count ++;
				}
			}
		}
	}
	if($orderItem['order_type'] == '2'){
		$orderItem['address_info'] = @json_decode($orderItem['address_info'],true);
		if($orderItem['deliver_type'] == '1'){
			$deliverLog = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_deliver_log')." WHERE order_id=:order_id ORDER BY updatetime ASC",array(':order_id'=>$orderItem['ordersn']));
			foreach ($deliverLog as $key => $log) {
				$remark = json_decode($log['remark'],true);
				if($log['status'] == '1'){
					$statusText = "待接单";
				}elseif($log['status'] == '2'){
					$statusText = "待取货";
				}elseif($log['status'] == '3'){
					$statusText = "配送中";
				}elseif($log['status'] == '4'){
					$statusText = "已完成";
				}elseif($log['status'] == '5'){
					$statusText = "已取消";
				}elseif($log['status'] == '7'){
					$statusText = "已过期";
				}else{
					$statusText = "未知状态";
				}
				$deliverText .= ($key+1).". ".date("Y-m-d H:i:s",$log['updatetime'])." ".$statusText." 配送员：".$remark['dm_name']." 联系方式：".$remark['dm_mobile']."<BR>";
			}
			$orderItem['deliverText'] = $deliverText;

		}
	}
	$orderItem['type_count'] = $type_count;
}elseif($operation == 'deal'){
	$orderid = intval($_GPC['orderid']);
	$to_status = intval($_GPC['status']);
	$log = $orderinfo = pdo_get("deamx_food_order",array('uniacid'=>$_W['uniacid'],'id'=>$orderid,'store_id'=>$store_id));
	empty($orderinfo) && itoast("订单不存在！",manage_url(array('r'=>'order','ac'=>'index','op'=>'list')),$itoastStatus);
	$storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$orderinfo['store_id']));
	//获取模板消息设置
	$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']), array('template_status', 'template_id', 'template_id_param','takeout_template_id','takeout_template_id_param','get_food_template_id_param','get_food_template_id','deliver_dada_status','deliver_dada_app_key','deliver_dada_app_secret','deliver_dada_shopid'));
	//memberid转openid
	//$fansinfo = pdo_get("mc_mapping_fans",array('uniacid'=>$_W['uniacid'],'uid'=>$orderinfo['member_id']),array('openid'));
	if($to_status == '2'){//接单
		if ($orderinfo['status'] == '1') {
			require DM_ROOT.'/common/print.class.php';
			//接单
			$updateResult = pdo_update('deamx_food_order', array(
				'status' => $to_status,
				'receivetime' => TIMESTAMP
			), array(
				'id' => $orderid
			));
			//判断是否为扫桌号小程序订餐
			if(!empty($orderinfo['desk_id'])){
				$deskInfo = pdo_get("deamx_food_desknumber",array('uniacid'=>$_W['uniacid'],'id'=>$orderinfo['desk_id'],'store_id'=>$orderinfo['store_id']),array('id','name'));
			}
			//非外卖订单
			if($orderinfo['order_type'] != '2'){
				//获取今日已接单订单数
				$todaytime = strtotime(date('Y-m-d'));
				$orderCount = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('deamx_food_order')." WHERE uniacid=:uniacid and store_id=:store_id and status>=2 and receivetime>=:todaytime and order_type=:order_type",array(':uniacid'=>$_W['uniacid'],':store_id'=>$orderinfo['store_id'],':todaytime'=>$todaytime,':order_type'=>'1'));
				$orderNumber = "A".sprintf("%03d",$orderCount);//取单号
				$updateResult = pdo_update('deamx_food_order', array(
					'order_number' => $orderNumber
				), array(
					'id' => $orderid
				));
				$template_remark = TEMPLATE_REMARK;
			}
			if($orderinfo['order_type'] != '2'){
				if(!empty($settings['template_status']) && !empty($settings['template_id'])){
					
					load()->classs('wxapp.account');
					$accObj= WxappAccount::create($_W['uniacid']);
					$access_token = $accObj->getAccessToken();
                    $template_id_param = explode(",", $settings['template_id_param']);
					$templateMessageArr = array(
                        $template_id_param[0]=>array(
							'value'	=>	str_replace('A', '', $orderNumber)
						),
                        $template_id_param[1]=>array(
							'value'	=>	'自助点餐'
						),
                        $template_id_param[2]=>array(
							'value'	=>	$storeInfo['name']
						),
                        $template_id_param[3]=>array(
							'value'	=>	$template_remark
						),
					);
					$templateMessageUrl = "deam_food/pages/order/detail?id=".$orderid;

                    $messageResult = messageSubscribe($access_token, $orderinfo['openid'], $settings['template_id'], $templateMessageUrl, $templateMessageArr);
                    if($messageResult['errcode'] != '0'){
                        load()->func('logging');
                        logging_run($messageResult);
                    }
				}
			}else{
				//判断是否为第三方配送
				if($storeInfo['deliver_type'] != '0'){
					if($storeInfo['deliver_type'] == '1' && $settings['deliver_dada_status'] == '1'){
						//达达配送
						require DM_ROOT.'/common/imdada.class.php';
						$imdada = new Imdada_DeamFood($settings['deliver_dada_shopid'],$settings['deliver_dada_app_key'],$settings['deliver_dada_app_secret']);
						$addressinfo = json_decode($log['address_info'],true);
						$addOrder = array(
							'shop_no'	=>	$storeInfo['deliver_dada_shopno'],
							'origin_id' =>	$log['ordersn'],
							'city_code'	=>	$storeInfo['deliver_dada_citycode'],
							'cargo_price'	=>	$log['price'],
							'is_prepay'		=>	'0',
							'expected_fetch_time'	=>	TIMESTAMP + 900,
							'receiver_name'			=>	$addressinfo['realname'],
							'receiver_address'		=>	$addressinfo['address']." ".$addressinfo['address_road']." ".$addressinfo['number'],
							'receiver_lat'			=>	$addressinfo['latitude'],
							'receiver_lng'			=>	$addressinfo['longitude'],
							'callback'				=>	MODULE_URL.'deliver/imdada/callback.php',
							'receiver_phone'		=>	$addressinfo['telphone']
						);
						$result = $imdada->addOrder($addOrder);
						if($result['status'] == 'success'){
							$updateResult = pdo_update('deamx_food_order', array(
								'deliver_type' => 1
							), array(
								'id' => $orderid
							));
						}else{
							$updateResult = pdo_update('deamx_food_order', array(
								'deliver_type' => -1,
								'deliver_dada_failreason' => $imdada->error_code($result['errorCode']),
							), array(
								'id' => $orderid
							));
							//记录文本日志
                            load()->func('logging');
							logging_run($log['ordersn']);
							logging_run($result);
						}
						
					}
				}
				//外卖模板消息
				if(!empty($settings['template_status']) && !empty($settings['takeout_template_id'])){
					load()->classs('wxapp.account');
					$accObj= WxappAccount::create($_W['uniacid']);
					$access_token = $accObj->getAccessToken();
                    $takeout_template_id_param = explode(",", $settings['takeout_template_id_param']);
					$templateMessageArr = array(
                        $takeout_template_id_param[0]=>array(
							'value'	=>	STORE_STATUS2
						),
                        $takeout_template_id_param[1]=>array(
							'value'	=>	'外卖'
						),
                        $takeout_template_id_param[2]=>array(
							'value'	=>	$storeInfo['name']
						),
                        $takeout_template_id_param[3]=>array(
							'value'	=>	STORE_REMARK2
						),
					);
					$templateMessageUrl = "deam_food/pages/order/detail?id=".$orderid;

                    $messageResult = messageSubscribe($access_token,$orderinfo['openid'],$settings['takeout_template_id'],$templateMessageUrl,$templateMessageArr);
                    if($messageResult['errcode'] != '0'){
                        load()->func('logging');
                        logging_run($messageResult);
                    }
				}
			}
			//打印
			com('printer')->deamPrint($orderid);
			if($isMobile){
				itoast("订单状态更新成功",$_SERVER['HTTP_REFERER'],'success');
				exit;
			}
			itoast("订单状态更新成功",manage_url(array('r'=>'order','ac'=>'index','op'=>'list','status'=>$orderinfo['status'],'method'=>$method)),'success');
		}else{
			if($isMobile){
				$itoastStatus = "success";
			}else{
				$itoastStatus = "error";
			}
			if($isMobile){
				itoast("订单状态非等待接单状态",$_SERVER['HTTP_REFERER'],'success');
				exit;
			}
			itoast("订单状态非等待接单状态",manage_url(array('r'=>'order','ac'=>'index','op'=>'list','method'=>$method)),$itoastStatus);
		}
	}elseif($to_status == '4' && $orderinfo['order_type'] == '2'){//待配送->开始配送
		if ($orderinfo['status'] == '2') {
			pdo_update("deamx_food_order",array('status'=>$to_status),array('uniacid'=>$_W['uniacid'],'id'=>$orderid));
			//向用户发送模板消息
			//外卖模板消息

			if($isMobile){
				itoast("订单状态更新成功",$_SERVER['HTTP_REFERER'],'success');
				exit;
			}
			itoast("订单状态更新成功",manage_url(array('r'=>'order','ac'=>'index','op'=>'list','status'=>$orderinfo['status'],'method'=>$method)),'success');
		}else{
			if($isMobile){
				$itoastStatus = "success";
			}else{
				$itoastStatus = "error";
			}
			if($isMobile){
				itoast("订单状态非待配送状态",$_SERVER['HTTP_REFERER'],'success');
				exit;
			}
			itoast("订单状态非待配送状态",manage_url(array('r'=>'order','ac'=>'index','op'=>'list','method'=>$method)),$itoastStatus);
		}
	}elseif($to_status == '3'){//->已完成/配送成功
		if($orderinfo['order_type'] == '1'){
			if ($orderinfo['status'] == '2') {
				pdo_update("deamx_food_order",array('status'=>$to_status),array('uniacid'=>$_W['uniacid'],'id'=>$orderid));
				if($isMobile){
					itoast("订单状态更新成功",$_SERVER['HTTP_REFERER'],'success');
					exit;
				}
				itoast("订单状态更新成功",manage_url(array('r'=>'order','ac'=>'index','op'=>'list','status'=>$orderinfo['status'],'method'=>$method)),'success');
				exit;
			}else{
				if($isMobile){
					itoast("订单状态非制作中状态",$_SERVER['HTTP_REFERER'],'success');
					exit;
				}
				itoast("订单状态非制作中状态",manage_url(array('r'=>'order','ac'=>'index','op'=>'list','method'=>$method)),$itoastStatus);
			}
		}elseif($orderinfo['order_type'] == '2'){//外卖
			if ($orderinfo['status'] == '4') {
				pdo_update("deamx_food_order",array('status'=>$to_status),array('uniacid'=>$_W['uniacid'],'id'=>$orderid));
				//向用户发送模板消息
				//外卖模板消息

				if($isMobile){
					itoast("订单状态更新成功",$_SERVER['HTTP_REFERER'],'success');
					exit;
				}
				itoast("订单状态更新成功",manage_url(array('r'=>'order','ac'=>'index','op'=>'list','status'=>$orderinfo['status'],'method'=>$method)),'success');
			}else{
				if($isMobile){
					itoast("订单状态非配送中状态",$_SERVER['HTTP_REFERER'],'success');
					exit;
				}
				itoast("订单状态非配送中状态",manage_url(array('r'=>'order','ac'=>'index','op'=>'list','method'=>$method)),$itoastStatus);
			}
		}
		
	}else{
		if($isMobile){
			itoast("未知操作",$_SERVER['HTTP_REFERER'],'success');
			exit;
		}
		itoast("未知操作",manage_url(array('r'=>'order','ac'=>'index','op'=>'list')),$itoastStatus);
	}
}elseif($operation == 'to_dada'){
	$orderid = intval($_GPC['orderid']);
	$orderinfo = pdo_get("deamx_food_order",array('uniacid'=>$_W['uniacid'],'id'=>$orderid));
	$storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$orderinfo['store_id']));
	//获取模板消息设置
	$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']), array('template_status', 'template_id','takeout_template_id','deliver_dada_status','deliver_dada_app_key','deliver_dada_app_secret','deliver_dada_shopid'));
	if($storeInfo['deliver_type'] == '1' && $settings['deliver_dada_status'] == '1'){
		//达达配送
		require DM_ROOT.'/common/imdada.class.php';
		$imdada = new Imdada_DeamFood($settings['deliver_dada_shopid'],$settings['deliver_dada_app_key'],$settings['deliver_dada_app_secret']);
		$addressinfo = json_decode($orderinfo['address_info'],true);
		$addOrder = array(
			'shop_no'	=>	$storeInfo['deliver_dada_shopno'],
			'origin_id' =>	$orderinfo['ordersn'],
			'city_code'	=>	$storeInfo['deliver_dada_citycode'],
			'cargo_price'	=>	$orderinfo['price'],
			'is_prepay'		=>	'0',
			'expected_fetch_time'	=>	TIMESTAMP + 900,
			'receiver_name'			=>	$addressinfo['realname'],
			'receiver_address'		=>	$addressinfo['address']." ".$addressinfo['address_road']." ".$addressinfo['number'],
			'receiver_lat'			=>	$addressinfo['latitude'],
			'receiver_lng'			=>	$addressinfo['longitude'],
			'callback'				=>	MODULE_URL.'deliver/imdada/callback.php',
			'receiver_phone'		=>	$addressinfo['telphone']
		);
		$result = $imdada->addOrder($addOrder);
		if($result['status'] == 'success'){
			$updateResult = pdo_update('deamx_food_order', array(
				'deliver_type' => 1
			), array(
				'id' => $orderid
			));
			if($isMobile){
				itoast("已成功提交至达达，请等待配送员接单。",$_SERVER['HTTP_REFERER'],'success');
				exit;
			}
			itoast("已成功提交至达达，请等待配送员接单。",manage_url(array('r'=>'order','ac'=>'index','op'=>'list','method'=>'takeout')),'success');
		}else{
			$updateResult = pdo_update('deamx_food_order', array(
				'deliver_type' => -1,
				'deliver_dada_failreason' => $imdada->error_code($result['errorCode']),
			), array(
				'id' => $orderid
			));
			load()->func('logging');
			//记录文本日志
//			logging_run($orderinfo['ordersn']);
//			logging_run($result);
			if($isMobile){
				itoast("提交失败，原因：".$imdada->error_code($result['errorCode']),$_SERVER['HTTP_REFERER'],'success');
				exit;
			}
			itoast("提交失败，原因：".$imdada->error_code($result['errorCode']),manage_url(array('r'=>'order','ac'=>'index','op'=>'list','method'=>'takeout')),$itoastStatus);
		}
		
	}else{
		if($isMobile){
			itoast("未配置达达配送",$_SERVER['HTTP_REFERER'],'success');
			exit;
		}
		itoast("未配置达达配送",manage_url(array('r'=>'order','ac'=>'index','op'=>'list','method'=>'takeout')),$itoastStatus);
	}
}elseif($operation == 'refund'){
	load()->func('file');
	$orderid = intval($_GPC['orderid']);
	$refund_fee = trim($_GPC['refund_fee']);
	$refund_reason = trim($_GPC['refund_reason']);
	if(empty($refund_reason)){
		$refund_reason = "订单退款";
	}
	$orderInfo = pdo_get("deamx_food_order",array('uniacid'=>$_W['uniacid'],'id'=>$orderid));
	if (empty($orderInfo)) {
		show_json(0,array('message'=>"订单不存在！"));
	}
	if(($orderInfo['pay_price']-$orderInfo['refund_fee']) < $refund_fee){
		show_json(0,array('message'=>"退款总金额不能大于订单支付金额！"));
	}
	if($orderInfo['pay_type'] == '1'){
		$setting = uni_setting_load('payment', $_W['uniacid']);
		$pay_setting = $setting['payment'];

		$cert =  authcode($pay_setting['wechat_refund']['cert'], 'DECODE');
		$key =  authcode($pay_setting['wechat_refund']['key'], 'DECODE');
        if($pay_setting['wechat_facilitator']['status'] == '1' && $orderInfo['wechat_paytype'] == '2'){
            $cert =  authcode($storeinfo['payment']['wechat_refund']['cert'], 'DECODE');
            $key =  authcode($storeinfo['payment']['wechat_refund']['key'], 'DECODE');
        }
		$cert_dir = MODULE_ROOT."/data/cert/{$_W['uniacid']}";
		if (!file_exists($cert_dir)){
		    @mkdir($cert_dir,0777,true);
		}
		$cert_name = md5(TIMESTAMP)."_cert.pem";
		$key_name = md5(TIMESTAMP)."_key.pem";
		$cert_status = @file_put_contents($cert_dir."/".$cert_name, $cert);
		$key_status = @file_put_contents($cert_dir."/".$key_name, $key);
		if(empty($cert_status) || empty($key_status) || empty($cert) || empty($key)){
			show_json(0,array('message'=>"证书异常！请检查支付证书是否已上传！"));
		}
		//退款操作
		//load()->func('logging');
		$refundsn = "RF".sprintf("%03d",$_W['uniacid']).date("YmdHis").mt_rand(1000,9999);
		$refund_param = array(
			'appid' => $_W['account']['key'],
			'mch_id' => $pay_setting['wechat']['mchid'],
			'out_trade_no' => $orderInfo['ordersn'],
			'out_refund_no' => $refundsn,
			'total_fee' => intval($orderInfo['pay_price'] * 100),
			'refund_fee' => intval($refund_fee * 100),
			'nonce_str' => random(8),
			'refund_desc' => $refund_reason
		);
		if($pay_setting['wechat_facilitator']['status'] == '1' && $orderInfo['wechat_paytype'] == '1'){
		    //独立支付
            $refund_param['sub_appid'] = $refund_param['appid'];
            $refund_param['sub_mch_id'] = $refund_param['mch_id'];
            $refund_param['appid'] = $pay_setting['wechat_facilitator']['appid'];
            $refund_param['mch_id'] = $pay_setting['wechat_facilitator']['mchid'];
            $pay_setting['wechat']['signkey'] = $pay_setting['wechat_facilitator']['signkey'];
        }
        if($pay_setting['wechat_facilitator']['status'] == '1' && $orderInfo['wechat_paytype'] == '2'){
            //独立支付
            $refund_param['sub_appid'] = $refund_param['appid'];
            $refund_param['sub_mch_id'] = $storeinfo['payment']['wechat']['mchid'];
            $refund_param['appid'] = $pay_setting['wechat_facilitator']['appid'];
            $refund_param['mch_id'] = $pay_setting['wechat_facilitator']['mchid'];
            $pay_setting['wechat']['signkey'] = $pay_setting['wechat_facilitator']['signkey'];

        }
		$unSignParaStr = formatQueryParaMap($refund_param,false);
		$signStr = $unSignParaStr."&key=".$pay_setting['wechat']['signkey'];
		$refund_param['sign'] = strtoupper(md5($signStr));
		$refundinfo = curl_post_ssl("https://api.mch.weixin.qq.com/secapi/pay/refund",arrayToXml($refund_param),$cert_dir."/".$cert_name,$cert_dir."/".$key_name);
		$refundinfo = xml2array($refundinfo);
		$refundinfo = json_decode(json_encode($refundinfo),true);

		if($refundinfo['return_code']=='FAIL'){
			show_json(0,array('message'=>$refundinfo['return_msg']));
		}
		if($refundinfo['return_code']!='SUCCESS'){
			show_json(0,array('message'=>"退款操作未执行，请检查配置是否正确！"));
		}
		if($refundinfo['result_code']=='FAIL'){
			show_json(0,array('message'=>$refundinfo['err_code_des']));
		}
		if($refundinfo['result_code']!='SUCCESS'){
			show_json(0,array('message'=>"退款操作未执行，请检查配置是否正确！"));
		}else{
			//进行退款操作结果查询

		}
		//更新表
		pdo_update("deamx_food_order",array('refund_fee +='=>$refund_fee),array('uniacid'=>$_W['uniacid'],'id'=>$orderid));
		//退款记录
		$refundArr = array(
			'uniacid'			=>	$_W['uniacid'],
			'refund_uniontid'	=>	$refundsn,
			'reason'			=>	$refund_reason,
			'uniontid'			=>	$orderInfo['ordersn'],
			'fee'				=>	$refund_fee,
			'status'			=>	'1'
		);
		pdo_insert("deamx_food_refundlog",$refundArr);
		file_delete($cert_dir."/".$cert_name);
		file_delete($cert_dir."/".$key_name);
	}elseif($orderInfo['pay_type'] == '2'){
		$refundsn = "RF".sprintf("%03d",$_W['uniacid']).date("YmdHis").mt_rand(1000,9999);
		load()->model('mc');
		$reduceResult = m("member")->credit_update($orderInfo['member_id'], "credit2", $refund_fee, array($_W['uid'], $refund_reason));
		if(empty($reduceResult['errno'])){
			//更新表
			pdo_update("deamx_food_order",array('refund_fee +='=>$refund_fee),array('uniacid'=>$_W['uniacid'],'id'=>$orderid));
			$refundArr = array(
				'uniacid'			=>	$_W['uniacid'],
				'refund_type'		=>	'2',
				'refund_uniontid'	=>	$refundsn,
				'reason'			=>	$refund_reason,
				'uniontid'			=>	$orderInfo['ordersn'],
				'fee'				=>	$refund_fee,
				'status'			=>	'1'
			);
			pdo_insert("deamx_food_refundlog",$refundArr);
		}
	}
	
	show_json(1,array('message'=>'退款操作成功！'));
	exit();
}elseif ($operation == 'print_order') {
	$orderid = intval($_GPC['orderid']);
	$log = $orderinfo = pdo_get("deamx_food_order",array('uniacid'=>$_W['uniacid'],'id'=>$orderid,'store_id'=>$store_id));
	empty($orderinfo) && itoast("订单不存在！",manage_url(array('r'=>'order','ac'=>'index','op'=>'list','method'=>trim($_GPC['method']))),$itoastStatus);
	$storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$orderinfo['store_id']));
	//获取打印机列表
	$printerArr = pdo_getall("deamx_food_printer",array('uniacid'=>$_W['uniacid'],'status'=>'1','store_id'=>$orderinfo['store_id']));
	$orderNumber = $orderinfo['order_number'];
	$printText = "手动打印订单，请注意不要重复制作";
	if(!empty($printerArr)){
		//打印
		com('printer')->deamPrint($orderid, $printText);
		
		itoast("手动打印成功！",manage_url(array('r'=>'order','ac'=>'index','op'=>'list','method'=>trim($_GPC['method']))),'success');
	}else{
		itoast("未设置打印机！",manage_url(array('r'=>'order','ac'=>'index','op'=>'list','method'=>trim($_GPC['method']))),'error');
	}
}elseif($operation == 'cancel_order'){
	$orderid = intval($_GPC['orderid']);
	$orderInfo = pdo_get("deamx_food_order",array('uniacid'=>$_W['uniacid'],'id'=>$orderid,'store_id'=>$store_id));
	if(empty($orderInfo)){
		show_json(0,array('err_msg' => '订单不存在！'));
	}elseif($orderInfo['status'] > '3'){
		show_json(0,array('err_msg' => '订单非可取消状态！'));
	}
	if($orderInfo['pay_price'] > '0' && $orderInfo['pay_price'] - $orderInfo['refund_fee'] > '0'){
		//进行退款处理
		$refund_reason = "订单取消";
		$refund_fee = $orderInfo['pay_price'] - $orderInfo['refund_fee'];
		if($orderInfo['pay_type'] == '1'){
			load()->func('file');
			$setting = uni_setting_load('payment', $_W['uniacid']);
			$pay_setting = $setting['payment'];
			$cert =  authcode($pay_setting['wechat_refund']['cert'], 'DECODE');
			$key =  authcode($pay_setting['wechat_refund']['key'], 'DECODE');
            if($pay_setting['wechat_facilitator']['status'] == '1' && ($orderInfo['wechat_paytype'] == '1' || $orderInfo['wechat_paytype'] == '2')){
                $cert =  authcode($pay_setting['wechat_facilitator']['wechat_refund']['cert'], 'DECODE');
                $key =  authcode($pay_setting['wechat_facilitator']['wechat_refund']['key'], 'DECODE');
            }
			$cert_dir = MODULE_ROOT."/data/cert/{$_W['uniacid']}";
			if (!file_exists($cert_dir)){
			    @mkdir($cert_dir,0777,true);
			}
			$cert_name = md5(TIMESTAMP)."_cert.pem";
			$key_name = md5(TIMESTAMP)."_key.pem";
			$cert_status = @file_put_contents($cert_dir."/".$cert_name, $cert);
			$key_status = @file_put_contents($cert_dir."/".$key_name, $key);
			if(empty($cert_status) || empty($key_status) || empty($cert) || empty($key)){
				show_json(0,array('message'=>"退款失败，原因：证书异常，请检查支付证书是否已上传！"));
			}
			$refundsn = "RF".sprintf("%03d",$_W['uniacid']).date("YmdHis").mt_rand(1000,9999);
			$refund_param = array(
				'appid' => $_W['account']['key'],
				'mch_id' => $pay_setting['wechat']['mchid'],
				'out_trade_no' => $orderInfo['ordersn'],
				'out_refund_no' => $refundsn,
				'total_fee' => intval($orderInfo['pay_price'] * 100),
				'refund_fee' => intval($refund_fee * 100),
				'nonce_str' => random(8),
				'refund_desc' => $refund_reason
			);
            if($pay_setting['wechat_facilitator']['status'] == '1' && $orderInfo['wechat_paytype'] == '1'){
                //独立支付
                $refund_param['sub_appid'] = $refund_param['appid'];
                $refund_param['sub_mch_id'] = $refund_param['mch_id'];
                $refund_param['appid'] = $pay_setting['wechat_facilitator']['appid'];
                $refund_param['mch_id'] = $pay_setting['wechat_facilitator']['mchid'];
                $pay_setting['wechat']['signkey'] = $pay_setting['wechat_facilitator']['signkey'];
            }
            if($pay_setting['wechat_facilitator']['status'] == '1' && $orderInfo['wechat_paytype'] == '2'){
                //独立支付
                $refund_param['sub_appid'] = $refund_param['appid'];
                $refund_param['sub_mch_id'] = $storeinfo['payment']['wechat']['mchid'];
                $refund_param['appid'] = $pay_setting['wechat_facilitator']['appid'];
                $refund_param['mch_id'] = $pay_setting['wechat_facilitator']['mchid'];
                $pay_setting['wechat']['signkey'] = $pay_setting['wechat_facilitator']['signkey'];

            }
			$unSignParaStr = formatQueryParaMap($refund_param,false);
			$signStr = $unSignParaStr."&key=".$pay_setting['wechat']['signkey'];
			$refund_param['sign'] = strtoupper(md5($signStr));
			$refundinfo = curl_post_ssl("https://api.mch.weixin.qq.com/secapi/pay/refund",arrayToXml($refund_param),$cert_dir."/".$cert_name,$cert_dir."/".$key_name);
			$refundinfo = xml2array($refundinfo);
			$refundinfo = json_decode(json_encode($refundinfo),true);
			if($refundinfo['return_code']=='FAIL'){
				show_json(0,array('message'=>$refundinfo['return_msg']));
			}
			if($refundinfo['return_code']!='SUCCESS'){
				show_json(0,array('message'=>"退款操作未执行，请检查配置是否正确！"));
			}
			if($refundinfo['result_code']=='FAIL'){
				show_json(0,array('message'=>$refundinfo['err_code_des']));
			}
			if($refundinfo['result_code']!='SUCCESS'){
				show_json(0,array('message'=>"退款操作未执行，请检查配置是否正确！"));
			}else{
				//进行退款操作结果查询

			}
			//更新表
			pdo_update("deamx_food_order",array('refund_fee +='=>$refund_fee),array('uniacid'=>$_W['uniacid'],'id'=>$orderid));
			//退款记录
			$refundArr = array(
				'uniacid'			=>	$_W['uniacid'],
				'refund_uniontid'	=>	$refundsn,
				'reason'			=>	$refund_reason,
				'uniontid'			=>	$orderInfo['ordersn'],
				'fee'				=>	$refund_fee,
				'status'			=>	'1'
			);
			pdo_insert("deamx_food_refundlog",$refundArr);
			file_delete($cert_dir."/".$cert_name);
			file_delete($cert_dir."/".$key_name);
		}elseif($orderInfo['pay_type'] == '2'){
			$refundsn = "RF".sprintf("%03d",$_W['uniacid']).date("YmdHis").mt_rand(1000,9999);
			load()->model('mc');
			$reduceResult = m("member")->credit_update($orderInfo['member_id'], "credit2", $refund_fee, array($_W['uid'], $refund_reason.$refundsn));
			if(empty($reduceResult['errno'])){
				//更新表
				pdo_update("deamx_food_order",array('refund_fee +='=>$refund_fee),array('uniacid'=>$_W['uniacid'],'id'=>$orderid));
				$refundArr = array(
					'uniacid'			=>	$_W['uniacid'],
					'refund_type'		=>	'2',
					'refund_uniontid'	=>	$refundsn,
					'reason'			=>	$refund_reason,
					'uniontid'			=>	$orderInfo['ordersn'],
					'fee'				=>	$refund_fee,
					'status'			=>	'1'
				);
				pdo_insert("deamx_food_refundlog",$refundArr);
			}
		}
	}
	//删除销售报表
	pdo_delete("deamx_food_order_goods", array('orderid' => $orderInfo['id']));
	pdo_update("deamx_food_order", array('status' => '-1'), array('id' => $orderInfo['id']));
	show_json(1);
}elseif($operation == 'cancel_deliver_dada'){
    $orderId = intval($_GPC['orderid']);
    $orderinfo = pdo_get("deamx_food_order",array('uniacid'=>$_W['uniacid'],'id'=>$orderId));
    if(empty($orderinfo)){
        show_json(0, "未找到该订单！");
    }
    $storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$orderinfo['store_id']));
    //获取模板消息设置
    $settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']), array('template_status', 'template_id','takeout_template_id','deliver_dada_status','deliver_dada_app_key','deliver_dada_app_secret','deliver_dada_shopid'));
    //达达配送
    require DM_ROOT.'/common/imdada.class.php';
    $imDada = new Imdada_DeamFood($settings['deliver_dada_shopid'], $settings['deliver_dada_app_key'], $settings['deliver_dada_app_secret']);
    $result = $imDada->cancelOrder(array('order_id' => $orderinfo['ordersn'], 'cancel_reason_id' => intval('1')));
    if($result['status'] == 'success'){
        pdo_update("deamx_food_order", array('deliver_type' => '-1', 'deliver_dada_failreason' => "手动取消配送"), array('id' => $orderId));
        $message = "达达配送取消成功，请自行配送！";
        if($result['result']['deduct_fee'] > 0){
            $message .= "本次取消扣除违约金金额：" . $result['result']['deduct_fee'] . "元";
        }
        show_json(1,$message);
    }else{
        show_json(0, "取消失败，原因：" . $result['msg']);
    }
    exit;
}
if(!$isMobile){
	include manage_template("store/order2");
}else{
	include manage_template('store/mobile/order');
}

?>
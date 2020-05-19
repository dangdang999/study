<?php  if( !defined("IN_IA") ) 
{
	exit( "Access Denied" );
}
class Order_DeamFoodModel
{
	public function dealOrder($orderid){
		global $_W;
		$uniacid = $_W['uniacid'];
		$log = pdo_fetch('SELECT * FROM ' . tablename('deamx_food_order') . ' WHERE `uniacid`=:uniacid and `id`=:id limit 1', array(
			':uniacid' => $_W['uniacid'],
			':id' => $orderid
		));
		load()->func('logging');
						
		$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']), array('template_status', 'template_id', 'template_id_param','sms_status','sms_type','sms_params','takeout_template_id', 'takeout_template_id_param','coupon_uniacid','deliver_dada_status','deliver_dada_app_key','deliver_dada_app_secret','deliver_dada_shopid'));
		//获取门店信息
		$store_id = $log['store_id'];
		$storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$store_id));
		//判断是否为外卖第三方配送 && 店铺为自动接单
		if($log['order_type'] == '2' && $storeInfo['deliver_type'] != '0' && (!empty($storeInfo['auto_order'])) ){
			if($storeInfo['deliver_type'] == '1' && $settings['deliver_dada_status'] == '1'){
				//达达配送
				require DM_ROOT.'/common/imdada.class.php';
				$imdada = new Imdada_DeamFood($settings['deliver_dada_shopid'],$settings['deliver_dada_app_key'],$settings['deliver_dada_app_secret']);
				$addressinfo = json_decode($log['address_info'],true);
				$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
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
					'callback'				=>	$http_type . $_SERVER['HTTP_HOST'].'/addons/deam_food/deliver/imdada/callback.php',
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
					// load()->func('logging');
					// logging_run($log['ordersn']);
					// logging_run($result);
				}
				
			}
		}
		$payMoney = $log['pay_price'];
		//判断是否使用优惠券
		//获取unionid
		$memberInfo = pdo_get("mc_mapping_fans",array('uniacid'=>$_W['uniacid'],'openid'=>$log['openid']));
		if($log['use_coupon']>0){
			$couponinfo = pdo_get("deamx_food_coupon_record",array('uniacid'=>$settings['coupon_uniacid'],'id'=>$log['use_coupon']));
			load()->classs('coupon');
			$coupon_api = new coupon($settings['coupon_uniacid']);
			
			$couponList = pdo_fetchall("SELECT r.*,c.title,c.least_cost,c.reduce_cost FROM ".tablename("deamx_food_coupon_record")." r LEFT JOIN ".tablename('deamx_food_coupon')." c ON r.card_id=c.card_id AND r.uniacid=c.coupon_uniacid WHERE c.least_cost<=:least_cost AND r.starttime<=:time AND r.endtime>=:time AND c.title!='' AND c.uniacid=:uniacid AND r.unionid=:unionid AND r.card_id=:card_id AND r.status=:status ORDER BY r.endtime ASC",array(':uniacid'=>$_W['uniacid'],':unionid'=>$memberInfo['unionid'],':least_cost'=>$log['price']*100,':time'=>$log['createtime'],':card_id'=>$couponinfo['card_id'],':status'=>'1'));
			//logging_run($couponList);
			foreach ($couponList as $key => $row) {
				$max_use = intval($log['coupon_price'] / ($row['reduce_cost']/100));
				if($key+1 <= $max_use){
					//核销卡券
					pdo_update("deamx_food_coupon_record",array('usetime'=>TIMESTAMP,'status'=>3,'order_id'=>$log['id']), array('id' => $row['id']));
					//同步核销微信卡券
					if(!empty($row['code'])){
						//获取微信
						$status = $coupon_api->ConsumeCode(array('code'=>$row['code']));
						
    					//logging_run($status);
					}
				}

			}
		}
		//判断是否赠送优惠券
		$send_rule = pdo_get("deamx_food_coupon_rules",array('starttime <='=>TIMESTAMP,'endtime >='=>TIMESTAMP,'status'=>'1','reduce_cost <='=>$payMoney));
		if(!empty($send_rule)){
			//赠送卡券
			$is_send = 0;
			if(!empty($send_rule['limit_send'])){
				//获取已经赠送的次数
				$send_count = pdo_fetchall("SELECT id FROM ".tablename('deamx_food_coupon_record')." WHERE uniacid=:uniacid AND unionid=:unionid AND rule_id=:rule_id GROUP BY source_orderid");
				$send_count = count($send_count);
				if($send_count >=$send_rule['limit_send']){
					$is_send = 1;
				}else{
					$is_send = 0;
				}
			}
			if(empty($is_send)){
				//开始赠送卡券
				$couponArr = iunserializer($send_rule['coupon_info']);
				$all_count = 0;
				foreach ($couponArr as $key => &$value) {
					$count = $value;
					// $all_count +=$count;
					$coupon_info = pdo_get("deamx_food_coupon",array('uniacid'=>$_W['uniacid'],'id'=>$key));
					$coupon_info['date_info'] = iunserializer($coupon_info['date_info']);
					if ($coupon_info['date_info']['time_type'] == 1) {
						$starttime = strtotime(str_replace(".","-",$coupon_info['date_info']['time_limit_start']));
						$endtime = strtotime(str_replace(".","-",$coupon_info['date_info']['time_limit_end']));
					} elseif($coupon_info['date_info']['time_type'] == 2) {
						if($coupon_info['date_info']['deadline'] > '0'){
							$startday = intval($coupon_info['date_info']['deadline']);
						}else{
							$startday = 0;
						}
						$starttime = strtotime(date("Y-m-d"),time()) + $startday * 24 * 60 * 60;
						$endtime = strtotime(date("Y-m-d"),time()) + $startday * 24 * 60 * 60 + $coupon_info['date_info']['limit'] * 24 * 60 * 60-1;
					}
					for ($x=1; $x<=$count; $x++) {
						$pcount = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('deamx_food_coupon_record') . " WHERE `unionid` = :unionid AND `couponid` = :couponid", array(':couponid' => $coupon_info['id'], ':unionid' => $memberInfo['unionid']));
						if ($pcount < $coupon_info['get_limit'] && $coupon_info['quantity'] > 0) {
							$insert_data = array(
								'uniacid' => $settings['coupon_uniacid'],
								'acid' => $_W['uniacid'],
								'card_id' => $coupon_info['card_id'],
								'openid' => $log['openid'],
								'code' => '',
								'addtime' => TIMESTAMP,
								'status' => '1',
								'uid' => '',
								'grantmodule' => 'deam_food',
								'remark' => $send_rule['title'],
								'couponid' => $coupon_info['id'],
								'granttype' => 3,
								'unionid' => $memberInfo['unionid'],
								'starttime' => $starttime,
								'endtime' => $endtime,
								'rule_id' => $send_rule['id'],
								'source_orderid' => $log['id'],
							);
							$all_count ++;
							pdo_insert('deamx_food_coupon_record', $insert_data);
							pdo_update('deamx_food_coupon', array('quantity -=' => 1, 'dosage +=' => 1), array('coupon_uniacid' => $_W['uniacid'], 'id' => $coupon_info['id']));
						}
					}
					
				}
				pdo_update('deamx_food_order', array(
					'need_send_coupon' => $all_count
				), array(
					'id' => $orderid
				));
			}
		}
		//判断是否为扫桌号小程序订餐
		if(!empty($log['desk_id'])){
			$deskInfo = pdo_get("deamx_food_desknumber",array('uniacid'=>$_W['uniacid'],'id'=>$log['desk_id'],'store_id'=>$log['store_id']),array('id','name'));
		}
		
		//判断门店是否自动接单
		if(empty($storeInfo['auto_order'])){
			//
			$orderNumber = "等待接单";//等待接单
			$template_remark = "请耐心等待商家处理";
			$newOrderNumber = "0";
		}else{
			//非外卖订单
			if($log['order_type'] != '2'){
				//获取该门店今日已接订单数
				$todaytime = strtotime(date('Y-m-d'));
				$orderCount = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('deamx_food_order')." WHERE uniacid=:uniacid and store_id=:store_id and (status>=2 OR status = '-1') and receivetime>=:todaytime and order_type=:order_type",array(':uniacid'=>$_W['uniacid'],':store_id'=>$store_id,':todaytime'=>$todaytime,':order_type'=>'1'));
				$orderNumber = "A".sprintf("%03d",$orderCount);//取单号
                $newOrderNumber = sprintf("%03d",$orderCount);
				$updateResult = pdo_update('deamx_food_order', array(
					'order_number' => $orderNumber
				), array(
					'id' => $orderid
				));
				$template_remark = TEMPLATE_REMARK;
			}
			
		}
		if($log['order_type'] != '2'){
			//获取模板消息设置
			if(!empty($settings['template_status']) && !empty($settings['template_id'] && !empty($settings['template_id_param']))){
				load()->classs('wxapp.account');
				$accObj= WxappAccount::create($_W['uniacid']);
				$access_token = @$accObj->getAccessToken();
				$template_id_param = explode(",", $settings['template_id_param']);
				$templateMessageArr = array(
                    $template_id_param[0]=>array(
						'value'	=>	$newOrderNumber
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

                $messageResult = messageSubscribe($access_token, $log['openid'], $settings['template_id'], $templateMessageUrl, $templateMessageArr);
                if($messageResult['errcode'] != '0'){
                    load()->func('logging');
                    logging_run($messageResult);
                }

			}
		}else{
			//外卖模板消息
			if(!empty($settings['template_status']) && !empty($settings['takeout_template_id']) && !empty($storeInfo['auto_order'])){
				load()->classs('wxapp.account');
				$accObj= WxappAccount::create($_W['uniacid']);
				$access_token = @$accObj->getAccessToken();
                $takeout_template_id_param = explode(",", $settings['takeout_template_id_param']);
				$templateMessageArr = array(
                    $takeout_template_id_param[0]=>array(
						'value'	=>	STORE_STATUS2,
					),
                    $takeout_template_id_param[1]=>array(
						'value'	=>	'外卖',
					),
                    $takeout_template_id_param[2]=>array(
						'value'	=>	$storeInfo['name'],
					),
                    $takeout_template_id_param[3]=>array(
						'value'	=>	STORE_REMARK2,
					),
				);
				$templateMessageUrl = "deam_food/pages/order/detail?id=".$orderid;
				$messageResult = messageSubscribe($access_token,$log['openid'],$settings['takeout_template_id'],$templateMessageUrl,$templateMessageArr);
                if($messageResult['errcode'] != '0'){
                    load()->func('logging');
                    logging_run($messageResult);
                }
			}
		}
		if(!empty($storeInfo['auto_order'])){//接单后打印订单
			//打印
			com('printer')->deamPrint($orderid);
		}
		//短信通知商家
		if(!empty($settings['sms_status']) && !empty($storeInfo['notice_tel'])){
			$settings['sms_params'] = json_decode($settings['sms_params'],true);
			if($settings['sms_type'] == '1'){//阿里云接口
				date_default_timezone_set('GMT');
				$postdate = array();
				//系统参数
				$postdate['SignatureMethod'] = 'HMAC-SHA1';
				$postdate['SignatureNonce'] = uniqid();
				$postdate['AccessKeyId'] = $settings['sms_params']['ali']['appkey'];
				$postdate['SignatureVersion'] = '1.0';
				$postdate['SignatureVersion'] = '1.0';
				$postdate['Timestamp'] = date('Y-m-d\TH:i:s\Z');
				$postdate['Format'] = "JSON";
				//业务API参数
				$postdate['Action'] = "SendSms";
				$postdate['Version'] = "2017-05-25";
				$postdate['RegionId'] = "cn-hangzhou";
				$postdate['PhoneNumbers'] = $storeInfo['notice_tel'];
				$postdate['SignName'] = $settings['sms_params']['ali']['signname'];
				$postdate['TemplateParam'] = '{"storename":"'.$storeInfo['name'].'"}';
				$postdate['TemplateCode'] = $settings['sms_params']['ali']['templateCode'];
				$postdate['OutId'] = $extend;
				$unSignParaStr = formatQueryParaMap($postdate,true);
				$unSignParaStr = str_replace("+", "%20",$unSignParaStr);
				$unSignParaStr = str_replace("*", "%2A",$unSignParaStr);
				$unSignParaStr = str_replace("%7E", "~",$unSignParaStr);
				//二次转换
				$tempStr = urlencode($unSignParaStr);
				$tempStr = str_replace("+", "%20",$tempStr);
				$tempStr = str_replace("*", "%2A",$tempStr);
				$tempStr = str_replace("%7E", "~",$tempStr);
				$tempStr = "GET&".urlencode("/")."&".$tempStr;
				$sign = getSignature($tempStr,$settings['sms_params']['ali']['secret']."&");
				//特殊urlencode转换sign
				$sign = urlencode($sign);
				$sign = str_replace("+", "%20",$sign);
				$sign = str_replace("*", "%2A",$sign);
				$sign = str_replace("%7E", "~",$sign);
				$unSignParaStr = "Signature=".$sign."&".$unSignParaStr;
				$postUrl = "http://dysmsapi.aliyuncs.com/?".$unSignParaStr;
				$recode = httpcurl($postUrl);
				
				$return = json_decode($recode,true);
				if($return['Code'] == 'OK'){
					
				}else{
					file_put_contents("message_tel.txt", $recode);
				}
			}
		}
	}
	public function setGoodsStockAndSaleVolume($orderid){
		global $_W;
		$log = pdo_fetch('SELECT * FROM ' . tablename('deamx_food_order') . ' WHERE `uniacid`=:uniacid and `id`=:id limit 1', array(
			':uniacid' => $_W['uniacid'],
			':id' => $orderid
		));
		//增加销量&&
		$goodsArr = json_decode($log['goods_list'],true);
		foreach ($goodsArr as $goods) {
			if($goods['count'] > 0){
			    $goodsId = empty($goods['goodsid']) ? $goods['goodsId'] : $goods['goodsid'];
				pdo_update("deamx_food_goods",array('realsales +='=>$goods['count']),array('uniacid'=>$_W['uniacid'],'id'=> $goodsId));
				if($goods['hasoption'] == '1'){
					foreach ($goods['options'] as $options) {
						if ($options['count'] > 0) {
							pdo_update("deamx_food_goods_option",array('realsales +='=>$options['count']),array('uniacid'=>$_W['uniacid'],'id'=>$options['id']));
						}
						//使用规格的商品入库
						$addSalesArr = array(
							'uniacid'		=>	$_W['uniacid'],
							'orderid'		=>	$orderid,
							'store_id'		=>	$log['store_id'],
							'goodsid'		=>	$goodsId,
							'price'			=>	$options['price'],
							'total'			=>	$options['count'],
							'optionid'		=>	$options['id'],
							'createtime'	=>	TIMESTAMP,
							'daytime'		=>	strtotime(date("Y-m-d")),
							'optionname'	=>	$options['name'],
							'openid'		=>	$log['openid'],
							'title'			=>	$goods['name']
						);
						pdo_insert("deamx_food_order_goods",$addSalesArr);
					}
				}else{
					//没有规格的商品入库
                    if (empty($goods['goodsid'])) {
                        //新版
                        if ($goods['optionsRealId'] > 0) {
                            pdo_update("deamx_food_goods_option",array('realsales +='=>$goods['count']),array('uniacid'=>$_W['uniacid'],'id'=>$goods['optionsRealId']));
                        }
                        $addSalesArr = array(
                            'uniacid'		=>	$_W['uniacid'],
                            'orderid'		=>	$orderid,
                            'store_id'		=>	$log['store_id'],
                            'goodsid'		=>	$goodsId,
                            'price'			=>	$goods['price'],
                            'total'			=>	$goods['count'],
                            'optionid'		=>	$goods['optionsRealId'],
                            'createtime'	=>	TIMESTAMP,
                            'daytime'		=>	strtotime(date("Y-m-d")),
                            'optionname'	=>	$goods['optionName'],
                            'openid'		=>	$log['openid'],
                            'title'			=>	$goods['name']
                        );
                    } else {
                        //旧版
                        $addSalesArr = array(
                            'uniacid'		=>	$_W['uniacid'],
                            'orderid'		=>	$orderid,
                            'store_id'		=>	$log['store_id'],
                            'goodsid'		=>	$goodsId,
                            'price'			=>	$goods['totalprice'],
                            'total'			=>	$goods['count'],
                            'optionid'		=>	0,
                            'createtime'	=>	TIMESTAMP,
                            'daytime'		=>	strtotime(date("Y-m-d")),
                            'optionname'	=>	"",
                            'openid'		=>	$log['openid'],
                            'title'			=>	$goods['name']
                        );
                    }

					pdo_insert("deamx_food_order_goods",$addSalesArr);
				}
				//
				

			}
		}
	}
}
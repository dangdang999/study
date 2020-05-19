<?php
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$operation = empty($_GPC['op']) ? 'list' : trim($_GPC['op']);
$method = empty($_GPC['method']) ? 'getself' : trim($_GPC['method']);



if($operation == 'list'){
	$storeList = pdo_getall("deamx_food_store",array('uniacid'=>$_W['uniacid']),array('id','name'));
	$store_id = intval($_GPC['storeid']);
	$status = intval($_GPC['status']);
	$condition = "";
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	if(empty($status)){
		$condition .= " and status > 0";
	}else{
		$condition .= " and status = ".$status;
	}
	if(!empty($store_id)){
		$condition .= " and store_id = ".$store_id;
	}
	if($method == 'getself'){
		$condition .= " and order_type = 1";
	}elseif($method == 'takeout'){
		$condition .= " and order_type = 2";
	}
	$list = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_order')." WHERE uniacid=:uniacid ".$condition." ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':uniacid'=>$_W['uniacid']));
	
	foreach ($list as &$row) {
		$deliverText = "";
		$row['storeInfo'] = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$row['store_id']),array('name'));
		if(!empty($row['desk_id'])){
			$row['deskInfo'] = pdo_get("deamx_food_desknumber",array('uniacid'=>$_W['uniacid'],'id'=>$row['desk_id'],'store_id'=>$row['store_id']),array('id','name'));
		}
		$type_count = 0;
		$row['goods_list'] = json_decode($row['goods_list'],true);
		foreach ($row['goods_list'] as $goods_list) {
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
		}
		$row['type_count'] = $type_count;
	}
	unset($row);
	unset($goods_list);
	unset($options);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('deamx_food_order') . " WHERE uniacid = '{$_W['uniacid']}' ".$condition);
	$pager = pagination($total, $pindex, $psize);
}elseif($operation == 'deal'){
	$orderid = intval($_GPC['orderid']);
	$to_status = intval($_GPC['status']);
	$orderinfo = pdo_get("deamx_food_order",array('uniacid'=>$_W['uniacid'],'id'=>$orderid));
	$storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$orderinfo['store_id']));
	//获取模板消息设置
	$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']), array('template_status', 'template_id','takeout_template_id','deliver_dada_status','deliver_dada_app_key','deliver_dada_app_secret','deliver_dada_shopid'));
	if($to_status == '2'){
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
				//非外卖订单模板消息
				if(!empty($settings['template_status']) && !empty($settings['template_id'])){
					//memberid转openid
					//$fansinfo = pdo_get("mc_mapping_fans",array('uniacid'=>$_W['uniacid'],'uid'=>$orderinfo['member_id']),array('openid'));
					load()->classs('wxapp.account');
					$accObj= WxappAccount::create($_W['uniacid']);
					$access_token = $accObj->getAccessToken();
					$templateMessageArr = array(
						'keyword1'=>array(
							'value'	=>	$orderNumber,
							'color'	=>	'#000000',
						),
						'keyword2'=>array(
							'value'	=>	'自助点餐',
							'color'	=>	'#000000',
						),
						'keyword3'=>array(
							'value'	=>	$storeInfo['name'],
							'color'	=>	'#000000',
						),
						'keyword4'=>array(
							'value'	=>	$template_remark,
							'color'	=>	'#000000',
						),
					);
					$templateMessageUrl = "deam_food/pages/order/detail?id=".$orderid;
					$messageResult = wxappMessage($access_token,$orderinfo['openid'],$settings['template_id'],$templateMessageUrl,$orderinfo['prepay_id'],$templateMessageArr,"keyword1.DATA");
					if($messageResult['errcode'] == '0'){
						pdo_update('deamx_food_order', array(
							'message_count +=' => '1'
						), array(
							'id' => $orderid
						));
					}
				}
			}else{
				//判断是否为第三方配送
				if($storeInfo['deliver_type'] != '0'){
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
						}else{
							$updateResult = pdo_update('deamx_food_order', array(
								'deliver_type' => -1,
								'deliver_dada_failreason' => $imdada->error_code($result['errorCode']),
							), array(
								'id' => $orderid
							));
							//记录文本日志
							logging_run($orderinfo['ordersn']);
							logging_run($result);
						}
						
					}
				}
				//外卖模板消息
				if(!empty($settings['template_status']) && !empty($settings['takeout_template_id'])){
					load()->classs('wxapp.account');
					$accObj= WxappAccount::create($_W['uniacid']);
					$access_token = $accObj->getAccessToken();
					$templateMessageArr = array(
						'keyword1'=>array(
							'value'	=>	STORE_STATUS2,
							'color'	=>	'#000000',
						),
						'keyword2'=>array(
							'value'	=>	'外卖',
							'color'	=>	'#000000',
						),
						'keyword3'=>array(
							'value'	=>	$storeInfo['name'],
							'color'	=>	'#000000',
						),
						'keyword4'=>array(
							'value'	=>	STORE_REMARK2,
							'color'	=>	'#000000',
						),
					);
					$templateMessageUrl = "deam_food/pages/order/detail?id=".$orderid;
					$messageResult = wxappMessage($access_token,$orderinfo['openid'],$settings['takeout_template_id'],$templateMessageUrl,$orderinfo['prepay_id'],$templateMessageArr,"keyword1.DATA");
					//file_put_contents("messageResult.log", $messageResult);
					if($messageResult['errcode'] == '0'){
						pdo_update('deamx_food_order', array(
							'message_count +=' => '1'
						), array(
							'id' => $orderid
						));
					}
				}
			}
			//获取打印机列表
			$printerArr = pdo_getall("deamx_food_printer",array('uniacid'=>$_W['uniacid'],'status'=>'1','store_id'=>$orderinfo['store_id']));
			if(!empty($printerArr)){
				$print = new DeamPrint();
				$goodsArr = json_decode($orderinfo['goods_list'],true);//商品列表
				foreach ($printerArr as $printer) {
					if($printer['type'] == '4'){//易联云新
						if(empty($printer['print_type'])){//判断打印类型
							$goodsHtml = '<tr><td>品名</td><td>数量</td><td>小计</td></tr>';
							foreach ($goodsArr as $index => $goodsInfo) {
								if(empty($goodsInfo['hasoption']) && $goodsInfo['count']>0){
									if($index >0){
										$goodsHtml .= "<tr><td></td></tr>";
									}
									$goodsHtml .= "<tr><td>".$goodsInfo['name']."</td><td>".$goodsInfo['count']."</td><td>".$goodsInfo['totalprice']."</td></tr>";
								}else{
									if(is_array($goodsInfo['options'])){
										foreach ($goodsInfo['options'] as $optionsIndex => $optionsInfo) {
											if($optionsInfo['count']>0){
												if($index > 0 || $optionsIndex > 0){
													$goodsHtml .= "<tr><td></td></tr>";
												}
												$goodsHtml .= "<tr><td>".$goodsInfo['name']."</td><td>".$optionsInfo['count']."</td><td>".$optionsInfo['price']."</td></tr>";
												$goodsHtml .= "<tr><td>".$optionsInfo['name']."</td></tr>";
											}
											
										}
									}
								}
							}
							$printerConf = json_decode($printer['print_data'],true);
							$times = (empty($printerConf['printer_yilianyun_new']['times'])||$printerConf['printer_yilianyun_new']['times']=='1') ? '':"<MN>".$printerConf['printer_yilianyun_new']['times']."</MN>";
							if($orderinfo['order_type'] != '2'){
								$printTemp = "\r\n\r\n取餐号 <FS2>".$orderNumber."</FS2>".(empty($deskInfo['name'])?'':"\r\n\r\n桌号 <FS2>".$deskInfo['name']."</FS2>");
							}else{
								$printTemp = "\r\n\r\n订单号".$orderinfo['ordersn'];
								$goodsHtml .= "<tr><td></td></tr>";
								$goodsHtml .= "<tr><td>餐盒费</td><td> </td><td>".$orderinfo['pbox_fee']."</td></tr>";
								$goodsHtml .= "<tr><td></td></tr>";
								$goodsHtml .= "<tr><td>配送费</td><td> </td><td>".$orderinfo['send_fee']."</td></tr>";
							}
							$content = $times."<FB><center>".$storeInfo['name']."</center></FB>".$printTemp."\r\n\r\n<table>".$goodsHtml ."</table><table><tr></tr><tr><td>合计</td><td>".$orderinfo['count']."</td><td>".$orderinfo['price']."</td></tr></table>\r\n\r\n下单时间：".date('Y-m-d H:i:s',$orderinfo['paytime']).($orderinfo['enoughdeduct'] > 0 ? "\r\n满减优惠：".$orderinfo['enoughdeduct']."元":'').($orderinfo['coupon_price'] > 0 ? "\r\n优惠券优惠：".$orderinfo['coupon_price']."元":'')."\r\n实际支付：".$orderinfo['pay_price']."元\r\n<FB>备注：".$orderinfo['remark']."</FB>\r\n";
							if($orderinfo['order_type'] == '2'){
								$addressinfo = json_decode($orderinfo['address_info'],true);
								$content .= "\r\n<FS2>".$addressinfo['realname']." ".$addressinfo['telphone']."</FS2>\r\n<FS2>".$addressinfo['address']." ".$addressinfo['address_road']." ".$addressinfo['number']."</FS2>\r\n";
							}
						}else{
							//分单打印
							foreach ($goodsArr as $index => $goodsInfo) {
								if(empty($goodsInfo['hasoption']) && $goodsInfo['count']>0){
									$x = 1;
									while($x <= $goodsInfo['count']){
										if($orderinfo['order_type'] != '2'){
											$goodsHtml .= "取餐号 <FS2>".$orderNumber."</FS2>".(empty($deskInfo['name'])?'':"\r\n\r\n桌号 <FS2>".$deskInfo['name']."</FS2>")."\r\n\r\n";
										}else{
											$goodsHtml .= "订单号 <FB>".$orderinfo['ordersn']."</FB>\r\n\r\n";
										}
										$goodsHtml .= "<FS2>".$goodsInfo['name']." x1</FS2>\r\n\r\n<FB>备注：".$orderinfo['remark']."</FB>\r\n\r\n";
										$x++;
									}
								}else{
									if(is_array($goodsInfo['options'])){
										foreach ($goodsInfo['options'] as $optionsIndex => $optionsInfo) {
											if($optionsInfo['count']>0){
												$x = 1;
												while($x <= $optionsInfo['count']){
													if($orderinfo['order_type'] != '2'){
														$goodsHtml .= "取餐号 <FS2>".$orderNumber."</FS2>".(empty($deskInfo['name'])?'':"\r\n\r\n桌号 <FS2>".$deskInfo['name']."</FS2>")."\r\n\r\n";
													}else{
														$goodsHtml .= "订单号 <FB>".$orderinfo['ordersn']."</FB>\r\n\r\n";
													}
													$goodsHtml .= "<FS2>".$goodsInfo['name']."-".$optionsInfo['name']." x1</FS2>\r\n\r\n<FB>备注：".$orderinfo['remark']."</FB>\r\n\r\n";
													$x++;
												}
											}
										}
									}
								}
							}
							$printerConf = json_decode($printer['print_data'],true);
							$content = ((empty($printerConf['printer_yilianyun_new']['times'])||$printerConf['printer_yilianyun_new']['times']=='1') ? '':"<MN>".$printerConf['printer_yilianyun_new']['times']."</MN>").$goodsHtml;
						}
						
						$apiKey = $printerConf['printer_yilianyun_new']['apikey'];
						$msign = $printerConf['printer_yilianyun_new']['msign'];
						$result = $print->printerYilianyunNew($printerConf['printer_yilianyun_new']['url'],$printerConf['printer_yilianyun_new']['partner'],$printerConf['printer_yilianyun_new']['machine_code'],$content,$apiKey,$msign);
					}elseif($printer['type'] == '1'){
						if(empty($printer['print_type'])){//判断打印类型
							$content = "<C>".$storeInfo['name']."</C><BR><BR>";
							if($orderinfo['order_type'] != '2'){
								$content .= "取餐号 <B>".$orderNumber."</B><BR><BR>";
								if(!empty($deskInfo['name'])){
									$content .= "桌号 <B>".$deskInfo['name']."</B><BR>";
								}
							}else{
								$content .= "订单号 <B>".$orderinfo['ordersn']."</B><BR>";
							}
							$content .= setSpacing("品名",22).setSpacing("数量",5).setSpacing("金额",5).'<BR>';
							$content .= '--------------------------------<BR>';
							foreach ($goodsArr as $index => $goodsInfo) {
								if(empty($goodsInfo['hasoption']) && $goodsInfo['count']>0){
									if($index >0){
										$content .= "<BR>";
									}
									$content .= setSpacing($goodsInfo['name'],22).setSpacing($goodsInfo['count'],5).setSpacing($goodsInfo['totalprice'],5)."<BR>";
								}else{
									if(is_array($goodsInfo['options'])){
										foreach ($goodsInfo['options'] as $optionsIndex => $optionsInfo) {
											if($optionsInfo['count']>0){
												if($index > 0 || $optionsIndex > 0){
													$content .= "<BR>";
												}
												$content .= setSpacing($goodsInfo['name'],22).setSpacing($optionsInfo['count'],5).setSpacing($optionsInfo['price'],5)."<BR>";
												$content .= $optionsInfo['name']."<BR>";
											}
										}
									}
								}
							}
							if($orderinfo['order_type'] == '2'){
								if($orderinfo['pbox_fee'] > 0){
									$content .= setSpacing("餐盒费",22).setSpacing("",5).setSpacing($orderinfo['pbox_fee'],5).'<BR>';
								}
								$content .= setSpacing("配送费",22).setSpacing("",5).setSpacing($orderinfo['send_fee'],5).'<BR>';
							}
							$content .= '--------------------------------<BR>';
							$printerConf = json_decode($printer['print_data'],true);
							$content .= "下单时间：".date('Y-m-d H:i:s',$orderinfo['paytime'])."<BR><BR>";
							if($orderinfo['enoughdeduct'] > 0){
								$content .= "满减优惠：".$orderinfo['enoughdeduct']."元<BR><BR>";
							}
							if($orderinfo['coupon_price'] > 0){
								$content .= "优惠券优惠：".$orderinfo['coupon_price']."元<BR><BR>";
							}
							$content .= "实际支付：".$orderinfo['pay_price']."元<BR><BR>";
							$content .= "<B>备注：".$orderinfo['remark']."</B>";
							if($orderinfo['order_type'] == '2'){
								$addressinfo = json_decode($orderinfo['address_info'],true);
								$content .= "<BR><B>".$addressinfo['realname']." ".$addressinfo['telphone']." ".$addressinfo['address']." ".$addressinfo['address_road']." ".$addressinfo['number']."</B><BR>";
							}
							$content .= '<CUT>';
						}else{
							$printerConf = json_decode($printer['print_data'],true);
							//分单打印
							$content = '';
							foreach ($goodsArr as $index => $goodsInfo) {
								if(empty($goodsInfo['hasoption']) && $goodsInfo['count']>0){
									$x = 1;
									while($x <= $goodsInfo['count']){
										if($orderinfo['order_type'] != '2'){
											$content .= "取餐号 <B>".$orderNumber."</B>".(empty($deskInfo['name'])?'':"<BR><BR>桌号 <B>".$deskInfo['name']."</B>")."<BR><BR>";
										}else{
											$content .= "订单号 <B>".$orderinfo['ordersn']."</B>";
										}
										$content .= "<B>".$goodsInfo['name']." x1</B><BR><BR><B>备注：".$orderinfo['remark']."</B><BR><BR><CUT>";
										$x++;
									}
								}else{
									if(is_array($goodsInfo['options'])){
										foreach ($goodsInfo['options'] as $optionsIndex => $optionsInfo) {
											if($optionsInfo['count']>0){
												$x = 1;
												while($x <= $optionsInfo['count']){
													if($orderinfo['order_type'] != '2'){
														$content .= "取餐号 <B>".$orderNumber."</B>".(empty($deskInfo['name'])?'':"<BR><BR>桌号 <B>".$deskInfo['name']."</B>")."<BR><BR>";
													}else{
														$content .= "订单号 <B>".$orderinfo['ordersn']."</B>";
													}
													$content .= "<B>".$goodsInfo['name']."-".$optionsInfo['name']." x1</B><BR><BR><B>备注：".$orderinfo['remark']."</B><BR><BR><CUT>";
													$x++;
												}
											}
										}
									}
								}
							}
						}
						$result = $print->printerFeie($printerConf['printer_feie']['url'],$printerConf['printer_feie']['user'],$printerConf['printer_feie']['ukey'],$printerConf['printer_feie']['deviceNo'],$content,$printerConf['printer_feie']['times']);
					
					}elseif($printer['type'] == '2'){//365 s1
						$printerConf = json_decode($printer['print_data'],true);
						if(empty($printer['print_type'])){//判断打印类型
							$content = empty($printerConf['printer_365s1']['times']) ? '^N1^F1' . "\n" : '^N' . $printerConf['printer_365s1']['times'] . '^F1' . "\n";
							$content .= $storeInfo['name']."\n\n";
							if($orderinfo['order_type'] != '2'){
								$content .= "^H2取餐号 ".$orderNumber."\n\n";
								if(!empty($deskInfo['name'])){
									$content .= "^H2桌号 ".$deskInfo['name']."\n\n";
								}
							}else{
								$content .= "订单号 ".$orderinfo['ordersn']."\n";
							}
							$content .= setSpacing("品名",22).setSpacing("数量",5).setSpacing("金额",5)."\n";
							$content .= "--------------------------------\n";
							foreach ($goodsArr as $index => $goodsInfo) {
								if(empty($goodsInfo['hasoption']) && $goodsInfo['count']>0){
									if($index >0){
										$content .= "\n";
									}
									$content .= setSpacing($goodsInfo['name'],22).setSpacing($goodsInfo['count'],5).setSpacing($goodsInfo['totalprice'],5)."\n";
								}else{
									if(is_array($goodsInfo['options'])){
										foreach ($goodsInfo['options'] as $optionsIndex => $optionsInfo) {
											if($optionsInfo['count']>0){
												if($index > 0 || $optionsIndex > 0){
													$content .= "\n";
												}
												$content .= setSpacing($goodsInfo['name'],22).setSpacing($optionsInfo['count'],5).setSpacing($optionsInfo['price'],5)."\n";
												$content .= $optionsInfo['name']."\n";
											}
										}
									}
								}
							}
							if($orderinfo['order_type'] == '2'){
								if($orderinfo['pbox_fee'] > 0){
									$content .= setSpacing("餐盒费",22).setSpacing("",5).setSpacing($orderinfo['pbox_fee'],5)."\n";
								}
								$content .= setSpacing("配送费",22).setSpacing("",5).setSpacing($orderinfo['send_fee'],5)."\n";
							}
							$content .= "--------------------------------\n";
							
							$content .= "支付时间：".date('Y-m-d H:i:s',$orderinfo['paytime'])."\n\n";
							if($orderinfo['enoughdeduct'] > 0){
								$content .= "满减优惠：".$orderinfo['enoughdeduct']."元\n\n";
							}
							if($orderinfo['coupon_price'] > 0){
								$content .= "优惠券优惠：".$orderinfo['coupon_price']."元\n\n";
							}
							$content .= "实际支付：".$orderinfo['pay_price']."元\n\n";
							$content .= "^H2备注：".$orderinfo['remark']."\n";
							if($orderinfo['order_type'] == '2'){
								$addressinfo = json_decode($orderinfo['address_info'],true);
								$content .= "\n^H2".$addressinfo['realname']." ".$addressinfo['telphone']."\n^H2".$addressinfo['address']." ".$addressinfo['address_road']." ".$addressinfo['number']."\n";
							}
						}else{
							$printerConf = json_decode($printer['print_data'],true);
							//分单打印
							$content = '';
							foreach ($goodsArr as $index => $goodsInfo) {
								if(empty($goodsInfo['hasoption']) && $goodsInfo['count']>0){
									$x = 1;
									while($x <= $goodsInfo['count']){
										if($orderinfo['order_type'] != '2'){
											$content .= "^H2取餐号 ".$orderNumber."\n";
											if(!empty($deskInfo['name'])){
												$content .= "^H2桌号 ".$deskInfo['name']."\n";
											}
										}else{
											$content .= "订单号 ".$orderinfo['ordersn']."\n";
										}
										$content .= "^H2".$goodsInfo['name']." x1\n";
										$content .= "^H2备注：".$orderinfo['remark']."\n\n";
										$x++;
									}
								}else{
									if(is_array($goodsInfo['options'])){
										foreach ($goodsInfo['options'] as $optionsIndex => $optionsInfo) {
											if($optionsInfo['count']>0){
												$x = 1;
												while($x <= $optionsInfo['count']){
													if($orderinfo['order_type'] != '2'){
														$content .= "^H2取餐号 ".$orderNumber."\n";
														if(!empty($deskInfo['name'])){
															$content .= "^H2桌号 ".$deskInfo['name']."\n";
														}
													}else{
														$content .= "订单号 ".$orderinfo['ordersn']."\n";
													}
													$content .= "^H2".$goodsInfo['name']."-".$optionsInfo['name']." x1\n";
													$content .= "^H2备注：".$orderinfo['remark']."\n\n";
													$x++;
												}
											}
										}
									}
								}
							}
						}
						$result = $print->printer365s1($printerConf['printer_365s1']['deviceNo'],$printerConf['printer_365s1']['key'],$content,$printerConf['printer_365s1']['times']);
					}
				}
			}
			itoast("订单状态更新成功",$this->createWebUrl('orders', array('op' => 'list','status'=>$orderinfo['status'],'method'=>$method, 'version_id'=>intval($_GPC['version_id']))),'success');
		}else{
			itoast("订单状态非等待接单状态",$this->createWebUrl('orders', array('op' => 'list','method'=>$method, 'version_id'=>intval($_GPC['version_id']))),'error');
		}
	}elseif($to_status == '4' && $orderinfo['order_type'] == '2'){//待配送->开始配送
		if ($orderinfo['status'] == '2') {
			pdo_update("deamx_food_order",array('status'=>$to_status),array('uniacid'=>$_W['uniacid'],'id'=>$orderid));
			//向用户发送模板消息
			//外卖模板消息
			if(!empty($settings['template_status']) && !empty($settings['takeout_template_id'])){
				load()->classs('wxapp.account');
				$accObj= WxappAccount::create($_W['uniacid']);
				$access_token = $accObj->getAccessToken();
				$templateMessageArr = array(
					'keyword1'=>array(
						'value'	=>	STORE_STATUS4,
						'color'	=>	'#000000',
					),
					'keyword2'=>array(
						'value'	=>	'外卖',
						'color'	=>	'#000000',
					),
					'keyword3'=>array(
						'value'	=>	$storeInfo['name'],
						'color'	=>	'#000000',
					),
					'keyword4'=>array(
						'value'	=>	STORE_REMARK4,
						'color'	=>	'#000000',
					),
				);
				$templateMessageUrl = "deam_food/pages/order/detail?id=".$orderid;
				$messageResult = wxappMessage($access_token,$orderinfo['openid'],$settings['takeout_template_id'],$templateMessageUrl,$orderinfo['prepay_id'],$templateMessageArr,"keyword1.DATA");
				//file_put_contents("messageResult.log", $messageResult);
				if($messageResult['errcode'] == '0'){
					pdo_update('deamx_food_order', array(
						'message_count +=' => '1'
					), array(
						'id' => $orderid
					));
				}
			}
			itoast("订单状态更新成功",$this->createWebUrl('orders', array('op' => 'list','status'=>$orderinfo['status'],'method'=>$method, 'version_id'=>intval($_GPC['version_id']))),'success');
		}else{
			itoast("订单状态非待配送状态",$this->createWebUrl('orders', array('op' => 'list','method'=>$method, 'version_id'=>intval($_GPC['version_id']))),'error');
		}
	}elseif($to_status == '3'){//->已完成/配送成功
		if($orderinfo['order_type'] == '1'){
			if ($orderinfo['status'] == '2') {
				pdo_update("deamx_food_order",array('status'=>$to_status),array('uniacid'=>$_W['uniacid'],'id'=>$orderid));
				itoast("订单状态更新成功",$this->createWebUrl('orders', array('op' => 'list','status'=>$orderinfo['status'],'method'=>$method, 'version_id'=>intval($_GPC['version_id']))),'success');
			}else{
				itoast("订单状态非制作中状态",$this->createWebUrl('orders', array('op' => 'list','method'=>$method, 'version_id'=>intval($_GPC['version_id']))),'error');
			}
		}elseif($orderinfo['order_type'] == '2'){//外卖
			if ($orderinfo['status'] == '4') {
				pdo_update("deamx_food_order",array('status'=>$to_status),array('uniacid'=>$_W['uniacid'],'id'=>$orderid));
				//向用户发送模板消息
				//外卖模板消息
				if(!empty($settings['template_status']) && !empty($settings['takeout_template_id'])){
					load()->classs('wxapp.account');
					$accObj= WxappAccount::create($_W['uniacid']);
					$access_token = $accObj->getAccessToken();
					$templateMessageArr = array(
						'keyword1'=>array(
							'value'	=>	STORE_STATUS3,
							'color'	=>	'#000000',
						),
						'keyword2'=>array(
							'value'	=>	'外卖',
							'color'	=>	'#000000',
						),
						'keyword3'=>array(
							'value'	=>	$storeInfo['name'],
							'color'	=>	'#000000',
						),
						'keyword4'=>array(
							'value'	=>	STORE_REMARK3,
							'color'	=>	'#000000',
						),
					);
					$templateMessageUrl = "deam_food/pages/order/detail?id=".$orderid;
					$messageResult = wxappMessage($access_token,$orderinfo['openid'],$settings['takeout_template_id'],$templateMessageUrl,$orderinfo['prepay_id'],$templateMessageArr,"keyword1.DATA");
					//file_put_contents("messageResult.log", $messageResult);
					if($messageResult['errcode'] == '0'){
						pdo_update('deamx_food_order', array(
							'message_count +=' => '1'
						), array(
							'id' => $orderid
						));
					}
				}
				itoast("订单状态更新成功",$this->createWebUrl('orders', array('op' => 'list','status'=>$orderinfo['status'],'method'=>$method, 'version_id'=>intval($_GPC['version_id']))),'success');
			}else{
				itoast("订单状态非配送中状态",$this->createWebUrl('orders', array('op' => 'list','method'=>$method, 'version_id'=>intval($_GPC['version_id']))),'error');
			}
		}
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
			itoast("已成功提交至达达，请等待配送员接单。",$this->createWebUrl('orders', array('op' => 'list','method'=>'takeout', 'version_id'=>intval($_GPC['version_id']))),'success');
		}else{
			$updateResult = pdo_update('deamx_food_order', array(
				'deliver_type' => -1,
				'deliver_dada_failreason' => $imdada->error_code($result['errorCode']),
			), array(
				'id' => $orderid
			));
			load()->func('logging');
			//记录文本日志
			logging_run($orderinfo['ordersn']);
			logging_run($result);
			itoast("提交失败，原因：".$imdada->error_code($result['errorCode']),$this->createWebUrl('orders', array('op' => 'list','method'=>'takeout', 'version_id'=>intval($_GPC['version_id']))),'error');
		}
		
	}else{
		itoast("未配置达达配送",$this->createWebUrl('orders', array('op' => 'list','method'=>'takeout', 'version_id'=>intval($_GPC['version_id']))),'error');
	}
}

include $this->template("system/store/order");
?>
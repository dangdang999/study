<?php
/**
 * 本破解程序由VIP资源网提供
 * VIP资源网 www.vip-zyw.com
 *
 */
defined('IN_IA') or exit('Access Denied');
require_once IA_ROOT . '/addons/deam_food/common/defines.php';
require DM_ROOT. '/common/functions.php';
class Deam_foodModuleWxapp extends WeModuleWxapp {
	public function doPageSettings(){
		global $_GPC, $_W;
		$operation = trim($_GPC['op']);
		$getfields = array();
		if($operation!='all'){
			$getfields = array("id",'uniacid','name','type','bg_color','fg_color','share_title','area_limit','tencent_map_apikey','single_storeid','store_blogo','copyright','wxapp_scan','wxapp_scan_name','wxapp_scan_color','wxapp_scan_intro','wxapp_scan_logo','wxapp_takeout','wxapp_takeout_name','wxapp_takeout_color','wxapp_takeout_intro','wxapp_takeout_logo','wxapp_getself','wxapp_getself_name','wxapp_getself_color','wxapp_getself_intro','wxapp_getself_logo','deliver_dada_status','bell_settings','plugins','sets','template_status', 'template_id', 'get_food_template_id', 'takeout_template_id');
		}
		$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']),$getfields);
		$settings['tencent_map_apikey'] = empty($settings['tencent_map_apikey']) ? "DOKBZ-4HHRX-JVR4Q-ZROIE-INAP3-7UFG3" : $settings['tencent_map_apikey'];
		$settings['store_blogo'] = empty($settings['store_blogo']) ? '' : tomedia($settings['store_blogo']);
		if(empty($settings['single_storeid'])){
			$settings['single_storeid'] = pdo_fetchcolumn("select id from ".tablename('deamx_food_store')." where uniacid=:uniacid and status='1' order by id asc",array(':uniacid'=>$_W['uniacid']));
		}
		$settings['wxapp_scan_logo'] = !empty($settings['wxapp_scan_logo']) ? tomedia($settings['wxapp_scan_logo']) : "";
		$settings['wxapp_takeout_logo'] = !empty($settings['wxapp_takeout_logo']) ? tomedia($settings['wxapp_takeout_logo']) : "";
		$settings['wxapp_getself_logo'] = !empty($settings['wxapp_getself_logo']) ? tomedia($settings['wxapp_getself_logo']) : "";
		if($operation == 'storeInfo'){
			$storeid = intval($_GPC['store_id']);
			$settings['storeinfo'] = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$storeid));
            $deskId = intval($_GPC['desk_id']);
            if(!empty($deskId)){
                $settings['deskInfo'] = pdo_get("deamx_food_desknumber",array('uniacid'=>$_W['uniacid'],'store_id'=>$storeid,'id'=>$deskId),array('id','name'));
            }
		}
		if(empty($settings['bg_color'])){
			$settings['bg_color'] = "#ff9c37";
		}
		if(empty($settings['fg_color'])){
			$settings['fg_color'] = "#ffffff";
		}
		$settings['bell_settings'] = @json_decode($settings['bell_settings'],true);
		$settings['call_waiter'] = intval($settings['bell_settings']['call_waiter']);
		unset($settings['bell_settings']);
		$settings['plugins'] = iunserializer($settings['plugins']);
		$settings['sets'] = iunserializer($settings['sets']);
		show_json(1,$settings);
	}
	public function doPageData(){
		global $_GPC, $_W;
		$uniacid = $_W['uniacid'];
		$operation = trim($_GPC['op']);
		if($operation == 'index'){
			//adv
			$adv = pdo_getall("deamx_food_adv",array('uniacid'=>$_W['uniacid'],'adv_isshow'=>"1"),array('id','adv_title','adv_img','adv_url'),'',"sortid desc,id desc");
			foreach($adv as &$row){
				$row['adv_img'] = tomedia($row['adv_img']);
			}
			if(!empty($adv[0]['adv_img'])){
				$adv_img = trim($adv[0]['adv_img']);
				$parsed = parse_url($adv_img);
				if (isset($parsed['scheme']) && strtolower($parsed['scheme']) == 'https') {
				  $adv_img = 'http://'.substr($adv_img,8);
				}
				$advStyle = getimagesize($adv_img);
			}
			unset($row);
			//广告
			$sysset = m('common')->getSysset();
			$advertisement = $sysset['advertisement'];
			show_json(1,array('adv'=>$adv,'adv_width'=>$advStyle[0],'adv_height'=>$advStyle[1],'advertisement' => $advertisement));
		}elseif($operation == 'storelist'){
			$store_type = trim($_GPC['store_type']);
			$latitude = trim($_GPC['latitude']);
			$longitude = trim($_GPC['longitude']);
			$pagesize = 7;
			$page = intval($_GPC['page']);
			$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']));
			$area_limit = intval($settings['area_limit']);
			$limit = "";
			if($area_limit>0){
				$limit = " HAVING distance <=".$area_limit;
			}
			if($store_type == 'takeout'){
				$condition .= " and is_takeout = 1";
			}else{
				$condition .= " and is_getself = 1";
			}
			$list = pdo_fetchall("select id,province,city,district,district_edit_self,address,name,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(( ".$latitude." * PI()/180-latitude*PI()/180)/2),2)+COS( ".$latitude." *PI()/180)*COS(latitude*PI()/180)*POW(SIN(( ".$longitude." * PI()/180-longitude*PI()/180)/2),2)))*1000) AS distance FROM ".tablename('deamx_food_store')." WHERE 1 ".$condition." and uniacid=:uniacid and status='1' ".$limit." ORDER BY distance ASC,id desc LIMIT " . ($page - 1) * $pagesize . ',' . $pagesize,array(':uniacid'=>$uniacid));
			foreach ($list as &$row) {
				$row['distance'] = @number_format($row['distance']/1000,2,".","");
			}
			$total = pdo_fetchcolumn("SELECT COUNT(*),ROUND(6378.138*2*ASIN(SQRT(POW(SIN(( ".$latitude." * PI()/180-latitude*PI()/180)/2),2)+COS( ".$latitude." *PI()/180)*COS(latitude*PI()/180)*POW(SIN(( ".$longitude." * PI()/180-longitude*PI()/180)/2),2)))*1000) AS distance FROM ".tablename('deamx_food_store')." WHERE uniacid=:uniacid and status='1' ".$condition.$limit." ORDER BY id DESC",array(':uniacid'=>$_W['uniacid']));
			$result['pagesize'] = $pagesize;
			$result['list'] = $list;
			$result['total'] = $total;
			show_json(1,$result);
		}elseif($operation == "storeinfo"){
			$storeid = intval($_GPC['storeid']);
			$storeType = trim($_GPC['storetype']);
			$storeinfo = pdo_get("deamx_food_store",array('uniacid'=>$uniacid,'id'=>$storeid));
			empty($storeinfo) && show_json(0,"该门店不存在！");
			if(empty($storeinfo['status'])){
				$storeinfo['statusText'] = "门店已关闭";
				$storeinfo['storeStatus'] = "0";
			}else{
			    $takeoutName = "";
                $storeinfo['takeout_open_time'] = iunserializer($storeinfo['takeout_open_time']);
			    if(!empty($storeinfo['takeout_open_time']['status']) && $storeType == 'takeout'){

                    $storeinfo['starttime'] = $storeinfo['takeout_open_time']['starttime'];
                    $storeinfo['endtime'] = $storeinfo['takeout_open_time']['endtime'];
                    $takeoutName = "外卖";
                }

				if($storeinfo['starttime'] != "00:00" || $storeinfo['endtime'] != "00:00"){
					
					$starttime = strtotime($storeinfo['starttime']);
					$endtime = strtotime($storeinfo['endtime']);

					if($starttime > $endtime){
						$endtime = strtotime("+1 day",$endtime);
						$storeinfo['timeText'] = "次日";
					}
					if(TIMESTAMP >= $starttime && TIMESTAMP <= $endtime){
						$storeinfo['statusText'] = "正常营业中";
						$storeinfo['storeStatus'] = "1";
					}else{
						$storeinfo['statusText'] = "商家休息中...";
						$storeinfo['close_reason'] = $takeoutName."营业时间 ".$storeinfo['starttime']."-".$storeinfo['timeText'].$storeinfo['endtime'];
						$storeinfo['storeStatus'] = "2";
					}
				}
			}
			$deskid = intval($_GPC['deskid']);
			if(!empty($deskid)){
				$deskinfo = pdo_get("deamx_food_desknumber",array('uniacid'=>$_W['uniacid'],'store_id'=>$storeid,'id'=>$deskid),array('id','name'));
				if(!empty($deskinfo['name'])){
					$deskinfo['name'] ="当前位置：".$deskinfo['name'];
				}
			}
			$classlist = pdo_getall("deamx_food_class",array('uniacid'=>$_W['uniacid'],'enabled'=>"1",'store_id'=>$storeid),array('id','classname'),'',"sortid desc,id desc");
			foreach ($classlist as $key => $classItem) {
				$goodslist[$key] = pdo_getall("deamx_food_goods",array('uniacid'=>$_W['uniacid'],'status'=>"1",'store_id'=>$storeid,'class_id'=>$classItem['id']),array('id','name','intro','price','img','hasoption','unit','is_pbox','pbox_price','old_option'),'',"displayorder desc,id desc");
				foreach ($goodslist[$key] as &$row) {
					$row['img'] = tomedia($row['img']);
					$row['count'] = 0;
					$row['unit'] = empty($row['unit']) ? '份' : $row['unit'];
                    $row['price'] = floatval($row['price']);
                    $row['pbox_price'] = floatval($row['pbox_price']);
				}
				unset($row);
			}
			empty($goodslist) && $goodslist = array();
			show_json(1,array('storeinfo'=>$storeinfo,'classlist'=>$classlist,'goodslist'=>$goodslist,'deskinfo'=>$deskinfo));

		}elseif($operation == "goodsinfo"){
			$goodsid = intval($_GPC['goodsid']);
			$storeid = intval($_GPC['storeid']);
			$goods = pdo_get("deamx_food_goods",array('uniacid'=>$_W['uniacid'],'status'=>"1",'store_id'=>$storeid,'id'=>$goodsid),array('id','name','intro','price','img','hasoption', 'old_option', 'goods_attr', 'goods_specs_title'));
            $specs = $options = array();
			if (!(empty($goods)) && $goods['hasoption']) {
			    if($goods['old_option'] == '1'){
                    $specs = pdo_fetchall('select * from ' . tablename('deamx_food_goods_spec') . ' where goodsid=:goodsid and uniacid=:uniacid order by displayorder asc', array(':goodsid' => $goodsid, ':uniacid' => $_W['uniacid']));
                    foreach ($specs as &$spec ){
                        $spec['items'] = pdo_fetchall('select * from ' . tablename('deamx_food_goods_spec_item') . ' where specid=:specid and `show`=1 order by displayorder asc', array(':specid' => $spec['id']));
                    }
                    unset($spec);
                    $options = pdo_fetchall('select * from ' . tablename('deamx_food_goods_option') . ' where goodsid=:goodsid and uniacid=:uniacid AND is_new = "0" order by displayorder asc', array(':goodsid' => $goodsid, ':uniacid' => $_W['uniacid']));
                    foreach ($options as &$row){
                        $row['marketprice'] = floatval($row['marketprice']);
                    }
                }else{
			        //新版多规格
                    $oldOptions = pdo_fetchall('select * from ' . tablename('deamx_food_goods_option') . ' where goodsid=:goodsid and uniacid=:uniacid AND is_new = "1" order by displayorder asc, id asc', array(':goodsid' => $goodsid, ':uniacid' => $_W['uniacid']));
                    $oldSpecs = array();
                    if(!empty($oldOptions)){
                        $items = array();
                        foreach ($oldOptions as $option){
                            $items[] = array(
                                'id'            =>  $option['id'],
                                'displayorder'  =>  $option['displayorder'],
                                'show'          =>  '1',
                                'title'         =>  $option['title'],
                                'uniacid'       =>  $_W['uniacid'],
                                'price'         =>  $option['marketprice']
                            );
                        }
                        $specs[] = array(
                            'id'        =>  '0',
                            'store_id'  =>  $storeid,
                            'goodsid'   =>  $goodsid,
                            'title'     =>  empty($goods['goods_specs_title']) ? '规格' : $goods['goods_specs_title'],
                            'items'     =>  $items,
                        );
                    }
                    $goods_attr = iunserializer($goods['goods_attr']);
                    if(!empty($goods_attr)){
                        $autoId = 0;
                        foreach ($goods_attr as $index => $attrArr){
                            $optionsArr = $attrArr['options'];
                            $items = array();
                            foreach ((array)$optionsArr as $index2 => $option){
                                if($option['status'] == '1'){
                                    $oldSpecs[] = array(
                                        'title' =>  $option['name']
                                    );
                                    $items[] = array(
                                        'id'            =>  $autoId,
                                        'displayorder'  =>  $index2,
                                        'show'          =>  '1',
                                        'title'         =>  $option['name'],
                                        'uniacid'       =>  $_W['uniacid'],
                                        'price'         =>  0
                                    );
                                    $autoId++;
                                }
                            }
                            $specs[] = array(
                                'id'        =>  $index + 1,
                                'store_id'  =>  $storeid,
                                'goodsid'   =>  $goodsid,
                                'title'     =>  empty($attrArr['title']) ? '规格' . ($index + 1) : $attrArr['title'],
                                'items'    =>  $items
                            );

                        }
                    }

                    $allArr = array();
                    foreach ($specs as $item){
                        $allArr[] = $item['items'];
                    }
                    $arr = getOptionCompose($allArr);
                    foreach ($arr as $index => $childArr){
                        $price = 0;
                        $optionIdArr = array();
                        $titleArr = array();
                        $optionId = 0;
                        foreach ($childArr as $value){
                            $price += $value['price'];
                            $optionIdArr[] = $value['id'];
                            $titleArr[] = $value['title'];
                            if($value['price'] > '0'){
                                $optionId = $value['id'];
                            }
                        }
                        $options[] = array(
                            'displayorder'      =>  $index,
                            'goodsid'           =>  $goodsid,
                            'id'                =>  $index+1,
                            'real_id'           =>  $optionId,
                            'is_new'            =>  '1',
                            'marketprice'       =>  floatval($price),
                            'store_id'          =>  $storeid,
                            'uniacid'           =>  $_W['uniacid'],
                            'specs'             =>  implode("_", $optionIdArr),
                            'title'             =>  implode("+", $titleArr),
                        );
                    }
                    foreach ($options as &$value){
                        if($value['marketprice'] <= '0'){
                            $value['marketprice'] = floatval($goods['price']);
                        }
                    }
                }

			}
			show_json(1, array('goods' => $goods, 'specs' => $specs, 'options' => $options));
		}elseif($operation == 'orderlist'){
			$pagesize = 10;
			$page = intval($_GPC['page']);
			$member_id = intval($_GPC['member_id']);
			$list = pdo_fetchall("SELECT o.id,o.ordersn,o.goods_list,o.count,o.price,o.pay_price,o.status,o.paytime,o.order_type,s.name as store_name FROM ".tablename('deamx_food_order')." o INNER JOIN ".tablename('deamx_food_store')." s ON o.store_id=s.id WHERE 1 ".$condition." and o.uniacid=:uniacid and o.status <> '0' and o.openid=:openid ORDER BY id DESC LIMIT " . ($page - 1) * $pagesize . ',' . $pagesize,array(':uniacid'=>$uniacid,':openid'=>$_W['openid']));
			foreach ($list as &$row) {
				$row['paytime'] = date("Y-m-d H:i:s",$row['paytime']);
				//状态和样式
				if($row['order_type'] == '1'){
					if($row['status'] == '0'){
						$row['status_text'] = "待付款";
					}elseif($row['status'] == '1'){
						$row['status_text'] = "等待接单";
					}elseif($row['status'] == '2'){
						$row['status_text'] = "制作中";
					}elseif($row['status'] == '3'){
						$row['status_text'] = "已完成";
					}elseif($row['status'] == '5'){
						$row['status_text'] = "已关闭";
					}elseif($row['status'] == '-1'){
						$row['status_text'] = "商家取消";
					}
				}elseif($row['order_type'] == '2'){
					if($row['status'] == '0'){
						$row['status_text'] = "待付款";
					}elseif($row['status'] == '1'){
						$row['status_text'] = "等待接单";
					}elseif($row['status'] == '2'){
						$row['status_text'] = "等待配送";
					}elseif($row['status'] == '3'){
						$row['status_text'] = "已完成";
					}elseif($row['status'] == '4'){
						$row['status_text'] = "正在配送";
					}elseif($row['status'] == '5'){
						$row['status_text'] = "已关闭";
					}elseif($row['status'] == '-1'){
						$row['status_text'] = "商家取消";
					}
				}
			}
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('deamx_food_order')." o WHERE o.uniacid=:uniacid and o.status > '0' and o.openid=:openid ".$condition." ORDER BY id DESC",array(':uniacid'=>$_W['uniacid'],':openid'=>$_W['openid']));
			$result['pagesize'] = $pagesize;
			$result['list'] = $list;
			$result['total'] = $total;
			show_json(1,$result);
		}elseif($operation == 'orderinfo'){
			$order_id = intval($_GPC['order_id']);
			$member_id = intval($_GPC['member_id']);
			$orderInfo = pdo_get("deamx_food_order",array('uniacid'=>$_W['uniacid'],'id'=>$order_id,'openid'=>$_W['openid']),array('id','goods_list','count','price','pay_price','status','remark','paytime','order_number','createtime','ordersn','store_id','order_type','pbox_fee','send_fee','use_coupon','coupon_price','need_send_coupon','is_send_coupon','enoughdeduct','getfood_time','pay_type','refund_fee'));
			if(empty($orderInfo)){
				show_json(0,'订单不存在或状态异常！');
			}
			$orderInfo['ticket_remark'] = "请留意服务员叫号";
			$orderInfo['paytime'] = date("Y-m-d H:i:s",$orderInfo['paytime']);
			$orderInfo['store'] = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$orderInfo['store_id']),array('id','name','telephone'));
			$orderInfo['getfood_time'] = str_replace("取餐", "", $orderInfo['getfood_time']);
			if($orderInfo['order_type'] == '2'){
				if($orderInfo['status'] == '0'){
					$orderInfo['status_text'] = STORE_STATUS0;
				}elseif($orderInfo['status'] == '1'){
					$orderInfo['status_text'] = STORE_STATUS1;
				}elseif($orderInfo['status'] == '2'){
					$orderInfo['status_text'] = STORE_STATUS2;
				}elseif($orderInfo['status'] == '3'){
					$orderInfo['status_text'] = STORE_STATUS3;
				}elseif($orderInfo['status'] == '4'){
					$orderInfo['status_text'] = STORE_STATUS4;
				}elseif($orderInfo['status'] == '5'){
					$orderInfo['status_text'] = STORE_STATUS5;
				}elseif($orderInfo['status'] == '-1'){
					$orderInfo['status_text'] = "商家取消";
				}
				
			}else{
				if($orderInfo['status'] == '0'){
					$orderInfo['status_text'] = "待付款";
				}elseif($orderInfo['status'] == '1'){
					$orderInfo['status_text'] = "等待接单";
				}elseif($orderInfo['status'] == '2'){
					$orderInfo['status_text'] = "制作中";
				}elseif($orderInfo['status'] == '3'){
					$orderInfo['status_text'] = "已完成";
				}elseif($orderInfo['status'] == '5'){
					$orderInfo['status_text'] = "已关闭";
				}elseif($orderInfo['status'] == '-1'){
					$orderInfo['status_text'] = "商家取消";
				}
			}
			if($orderInfo['pay_type'] == '1'){
				$orderInfo['pay_type'] = "微信支付";
			}else if($orderInfo['pay_type'] == '2'){
				$orderInfo['pay_type'] = "余额支付";
			}
			//更新卡券显示
			if($orderInfo['need_send_coupon'] > 0 && $orderInfo['is_send_coupon'] == 0){
				pdo_update("deamx_food_order",array('is_send_coupon'=>'1'),array('id'=>$order_id));
			}
			if (empty($orderInfo['store']['telephone'])){
                $orderInfo['store']['telephone'] = '';
            }
			show_json(1,$orderInfo);
		}elseif($operation == 'address'){
			$pagesize = 10;
			$page = intval($_GPC['page']);
			$member_id = intval($_GPC['member_id']);
			$list = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_address')." WHERE uniacid=:uniacid AND openid=:openid ORDER BY id DESC LIMIT " . ($page - 1) * $pagesize . ',' . $pagesize,array(':uniacid'=>$uniacid,':openid'=>$_W['openid']));
			$total = pdo_fetchcolumn("SELECT * FROM ".tablename('deamx_food_address')." WHERE uniacid=:uniacid AND openid=:openid ORDER BY id DESC",array(':uniacid'=>$uniacid,':openid'=>$_W['openid']));
			$result['pagesize'] = $pagesize;
			$result['list'] = $list;
			$result['total'] = $total;
			show_json(1,$result);
		}elseif($operation == 'addr_detail'){
			$member_id = intval($_GPC['member_id']);
			$addressid = intval($_GPC['addressid']);
			$address = pdo_get("deamx_food_address",array('uniacid'=>$_W['uniacid'],'openid'=>$_W['openid'],'id'=>$addressid));
			if (!empty($address)) {
				show_json(1,$address);
			}else{
				show_json(0,'地址不存在，无法编辑！');
			}
		}elseif($operation == 'couponlist'){
			$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']), array('template_status', 'template_id','sms_status','sms_type','sms_params','takeout_template_id','coupon_uniacid'));
			$memberInfo = pdo_get("mc_mapping_fans",array('uniacid'=>$_W['uniacid'],'openid'=>$_W['openid']));
			$pagesize = 10;
			$page = intval($_GPC['page']);
			$member_id = intval($_GPC['member_id']);
			$status = intval($_GPC['status']);
			switch ($status){
				case '0':
				$result['tips'] = "您还没有未使用的优惠券~";
				$condition .= " AND r.status='1' AND r.endtime>=".TIMESTAMP;
				break;
				case '1':
				$result['tips'] = "您还没有已使用的优惠券~";
				$condition .= " AND r.status='3'";
				break;
				case '2':
				$result['tips'] = "您还没有已过期的优惠券~";
				$condition .= " AND r.status='1' AND r.endtime<".TIMESTAMP;
				break;
			}
			if(empty($memberInfo['unionid'])){
				$memberInfo['unionid'] = "deamx_food_user";
			}
			$list = pdo_fetchall("SELECT r.*,c.title,c.least_cost,c.reduce_cost FROM ".tablename('deamx_food_coupon_record')." r LEFT JOIN ".tablename('deamx_food_coupon')." c ON r.card_id=c.card_id AND r.uniacid=c.coupon_uniacid WHERE r.uniacid=:uniacid AND r.unionid=:unionid AND c.title != '' ".$condition." ORDER BY endtime ASC,id ASC LIMIT " . ($page - 1) * $pagesize . ',' . $pagesize,array(':uniacid'=>$settings['coupon_uniacid'],':unionid'=>$memberInfo['unionid']));
			foreach ($list as $key => &$row) {
				$row['reduce_cost'] = $row['reduce_cost']/100;
				$row['least_cost'] = $row['least_cost']/100;
				$row['starttime'] = date("Y.m.d",$row['starttime']);
				$row['endtime'] = date("Y.m.d",$row['endtime']);

			}
			$total = pdo_fetchcolumn("SELECT count(*) FROM ".tablename('deamx_food_coupon_record')." r LEFT JOIN ".tablename('deamx_food_coupon')." c ON r.card_id=c.card_id AND r.uniacid=c.coupon_uniacid WHERE r.uniacid=:uniacid AND r.unionid=:unionid AND c.title != '' ".$condition,array(':uniacid'=>$settings['coupon_uniacid'],':unionid'=>$memberInfo['unionid']));
			$result['pagesize'] = $pagesize;
			$result['list'] = $list;
			$result['total'] = intval($total);
			show_json(1,$result);
		}elseif ($operation == 'first_addr') {
			$member_id = intval($_GPC['member_id']);
			$latitude = trim($_GPC['latitude']);
			$longitude = trim($_GPC['longitude']);
			$store_id = intval($_GPC['store_id']);
			$storeinfo = pdo_get("deamx_food_store",array('uniacid'=>$uniacid,'id'=>$store_id),array('send_limit','longitude','latitude'));

			if($storeinfo['send_limit']>0){
				$limit = " HAVING distance <=".($storeinfo['send_limit'] * 1000);
			}
			$list = pdo_fetch("SELECT id,realname,telphone,address,address_road,number,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(( ".$storeinfo['latitude']." * PI()/180-latitude*PI()/180)/2),2)+COS( ".$storeinfo['latitude']." *PI()/180)*COS(latitude*PI()/180)*POW(SIN(( ".$storeinfo['longitude']." * PI()/180-longitude*PI()/180)/2),2)))*1000) AS distance,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(( ".$latitude." * PI()/180-latitude*PI()/180)/2),2)+COS( ".$latitude." *PI()/180)*COS(latitude*PI()/180)*POW(SIN(( ".$longitude." * PI()/180-longitude*PI()/180)/2),2)))*1000) AS mydistance,latitude,longitude FROM ".tablename('deamx_food_address')." WHERE 1 ".$condition." and uniacid=:uniacid and openid=:openid ".$limit." ORDER BY mydistance ASC, distance ASC,id desc",array(':uniacid'=>$uniacid,':openid'=>$_W['openid']));
			if(!empty($list)){
				show_json(1,$list);
			}else{
				show_json(0);
			}
		}elseif($operation == 'select_addr'){
			$member_id = intval($_GPC['member_id']);
			$store_id = intval($_GPC['store_id']);
			$storeinfo = pdo_get("deamx_food_store",array('uniacid'=>$uniacid,'id'=>$store_id),array('send_limit','longitude','latitude'));
			if($storeinfo['send_limit']>0){
				$true_limit = " HAVING distance <=".($storeinfo['send_limit'] * 1000);
				$false_limit = " HAVING distance >".($storeinfo['send_limit'] * 1000);
			}
			$truelist = pdo_fetchall("SELECT id,realname,telphone,address,address_road,number,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(( ".$storeinfo['latitude']." * PI()/180-latitude*PI()/180)/2),2)+COS( ".$storeinfo['latitude']." *PI()/180)*COS(latitude*PI()/180)*POW(SIN(( ".$storeinfo['longitude']." * PI()/180-longitude*PI()/180)/2),2)))*1000) AS distance,latitude,longitude FROM ".tablename('deamx_food_address')." WHERE 1 ".$condition." and uniacid=:uniacid and openid=:openid ".$true_limit." ORDER BY id desc",array(':uniacid'=>$uniacid,':openid'=>$_W['openid']));
			if($storeinfo['send_limit']>0){
				$falselist = pdo_fetchall("SELECT id,realname,telphone,address,address_road,number,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(( ".$storeinfo['latitude']." * PI()/180-latitude*PI()/180)/2),2)+COS( ".$storeinfo['latitude']." *PI()/180)*COS(latitude*PI()/180)*POW(SIN(( ".$storeinfo['longitude']." * PI()/180-longitude*PI()/180)/2),2)))*1000) AS distance,latitude,longitude FROM ".tablename('deamx_food_address')." WHERE 1 ".$condition." and uniacid=:uniacid and openid=:openid ".$false_limit." ORDER BY id desc",array(':uniacid'=>$uniacid,':openid'=>$_W['openid']));
			}else{
				$falselist = array();
			}
			
			$total = pdo_fetchcolumn("SELECT * FROM ".tablename('deamx_food_address')." WHERE uniacid=:uniacid AND openid=:openid ORDER BY id DESC",array(':uniacid'=>$uniacid,':openid'=>$_W['openid']));
			$result['truelist'] = $truelist;
			$result['truecount'] = count($truelist);
			$result['falsecount'] = count($falselist);
			$result['falselist'] = $falselist;
			$result['total'] = $total;
			show_json(1,$result);
		}elseif ($operation == 'check_addr') {
			$member_id = intval($_GPC['member_id']);
			$address_id = intval($_GPC['address_id']);
			$address = pdo_get("deamx_food_address",array('uniacid'=>$uniacid,'openid'=>$_W['openid'],'id'=>$address_id));
			if(empty($address)){
				show_json(0);
			}else{
				show_json(1);
			}
		}elseif($operation == 'adv_info'){
			$adv_id = intval($_GPC['id']);
			$adv = pdo_get("deamx_food_adv",array('uniacid'=>$_W['uniacid'],'id'=>$adv_id),array('adv_title','adv_url'));
			if(!empty($adv)){
				show_json(1,$adv);
			}
			show_json(0);
		}elseif($operation == 'auth_setting'){
			$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']),array('auth_setting','name'));
			$settings['auth_setting'] = iunserializer($settings['auth_setting']);
			if(empty($settings['auth_setting']['name'])){
				$settings['auth_setting']['name'] = $settings['name'];
			}
			if(empty($settings['auth_setting']['logo'])){
				$settings['auth_setting']['logo'] = $_W['current_module']['logo'];
			}else{
				$settings['auth_setting']['logo'] = tomedia($settings['auth_setting']['logo']);
			}
			if(empty($settings['auth_setting']['intro'])){
				$settings['auth_setting']['intro'] = "暂无简介";
			}
			show_json(1,$settings);
		}elseif($operation == 'getfood_time'){
			$store_id = intval($_GPC['store_id']);
			$storeinfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$store_id));
			
			$starttime = strtotime($storeinfo['starttime']);
			$endtime = strtotime($storeinfo['endtime']);

			if($starttime >= $endtime){
				$endtime = strtotime("+1 day",$endtime);
			}
			if(TIMESTAMP+1200 <= $endtime){//20分钟后还在营业
				$getstratTime = TIMESTAMP+1200;
				for ($i=0; $i <= 300; $i++) { 
					if((TIMESTAMP+1200+$i)%300<60){
						$getstratTime = TIMESTAMP+1200+$i;
						break;
					}
				}
				$chooseTime = array();
					$chooseTime[] = '现在下单，稍后即取';
				for ($j=$getstratTime; $j < $endtime;$j = $j+300) {
					$chooseTime[] = date('H:i',$j);
				}
				show_json(1,array('getfood_time'=>$chooseTime));
			}else{
				show_json(1,array('getfood_time'=>array('现在下单，稍后即取')));
			}

		}elseif($operation == 'memberinfo'){
			$member = m("member")->getMemberInfo($_W['openid']);
			if($member == false){
				show_json(0, "未获取到用户信息");
			}
			$member['credit2'] = floatval($member['credit2']);
			$memberInfo = pdo_get("mc_mapping_fans",array('uniacid'=>$_W['uniacid'],'openid'=>$_W['openid']));
			$condition .= " AND r.status='1' AND r.endtime>=".TIMESTAMP;
			$member['couponCount'] = pdo_fetchcolumn("SELECT count(*) FROM ".tablename('deamx_food_coupon_record')." r LEFT JOIN ".tablename('deamx_food_coupon')." c ON r.card_id=c.card_id AND r.uniacid=c.coupon_uniacid WHERE r.uniacid=:uniacid AND r.unionid=:unionid AND c.title != '' ".$condition,array(':uniacid'=>$settings['coupon_uniacid'],':unionid'=>$memberInfo['unionid']));
			if(empty($member['telephone'])){
				$member['telephone'] = "";
			}
			show_json(1,$member);
		}elseif($operation == 'recharge'){
			$acts = com_run("sale::getRechargeActivity");
			$member = m("member")->getMemberInfo($_W['openid']);
			if($member == false){
				show_json(0, "未获取到用户信息");
			}
			$member['credit2'] = number_format($member['credit2'],'2');
			show_json(1,array('memberinfo'=>$member,'acts_count'=>count($acts),'acts'=>$acts));
		}elseif($operation == 'credits_record'){
			$pagesize = 15;
			$page = intval($_GPC['page']);
			$type = empty($_GPC['type']) ? "credit2" : trim($_GPC['type']);
			$member = m("member")->getMemberInfo($_W['openid']);
			if($member == false){
				show_json(0, "未获取到用户信息");
			}
			$list = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_credits_record')." WHERE uniacid=:uniacid AND uid=:uid AND credittype=:credittype ORDER BY id DESC LIMIT " . ($page - 1) * $pagesize . ',' . $pagesize,array(':uniacid'=>$uniacid,':uid'=>$member['id'],':credittype'=>$type));
			foreach ($list as &$row) {
				if($type == 'credit1'){
					$row['num'] = intval($row['num']);
				}
				if($row['num'] > 0){
					$row['num'] = "+".$row['num'];
				}
				$row['createtime'] = date("Y-m-d H:i",$row['createtime']);
			}
			$total = pdo_fetchcolumn("SELECT * FROM ".tablename('deamx_food_credits_record')." WHERE uniacid=:uniacid AND uid=:uid AND credittype=:credittype ORDER BY id DESC",array(':uniacid'=>$uniacid,':uid'=>$member['id'],':credittype'=>$type));
			$result['pagesize'] = $pagesize;
			$result['list'] = $list;
			$result['total'] = $total;
			show_json(1,$result);
		}elseif($operation == 'getPhoneNumber'){
			$encryptedData = urldecode($_GPC['encryptedData']);
			$iv = Base64_Decode($_GPC['iv']);
			$result=openssl_decrypt( base64_decode($encryptedData), "AES-128-CBC", base64_decode($_SESSION['session_key']), 1, $iv);
			$dataArr=json_decode($result, true);
			if(!empty($dataArr)){
				$updateArr = array(
					'telephone' =>	$dataArr['phoneNumber']
				);
				pdo_update('deamx_food_members', $updateArr, array('openid' => $_W['openid']));
				$member = m("member")->getMemberInfo($_W['openid']);
				if($member == false){
					show_json(0, "未获取到用户信息");
				}
				$member['credit2'] = number_format($member['credit2'],'2');
				$memberInfo = pdo_get("mc_mapping_fans",array('uniacid'=>$_W['uniacid'],'openid'=>$_W['openid']));
				$condition .= " AND r.status='1' AND r.endtime>=".TIMESTAMP;
				$member['couponCount'] = pdo_fetchcolumn("SELECT count(*) FROM ".tablename('deamx_food_coupon_record')." r LEFT JOIN ".tablename('deamx_food_coupon')." c ON r.card_id=c.card_id AND r.uniacid=c.coupon_uniacid WHERE r.uniacid=:uniacid AND r.unionid=:unionid AND c.title != '' ".$condition,array(':uniacid'=>$settings['coupon_uniacid'],':unionid'=>$memberInfo['unionid']));
				show_json(1,$member);
			}else{
				show_json(0,'获取失败，请尝试重新授权登陆后再试！');
			}
		}
	}
	public function doPageApi(){
		global $_GPC, $_W;
		$operation = trim($_GPC['op']);
		load()->func('communication');
		if($operation == 'getaddr'){
			$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']));
			$settings['tencent_map_apikey'] = empty($settings['tencent_map_apikey']) ? "DOKBZ-4HHRX-JVR4Q-ZROIE-INAP3-7UFG3" : $settings['tencent_map_apikey'];
			$latitude = trim($_GPC['latitude']);
			$longitude = trim($_GPC['longitude']);
			$url = "http://apis.map.qq.com/ws/geocoder/v1/?location=".$latitude.",".$longitude."&key=".$settings['tencent_map_apikey'];
			$result = ihttp_get($url);
			$return = json_decode($result['content'],true);
			if(!empty($return['result']['formatted_addresses']['recommend'])){
				$address = $return['result']['formatted_addresses']['recommend'];
			}else{
				$address = $return['result']['address'];
			}
			
			show_json(1,array('address'=>$address));
		}
	}
	public function doPageDeampost(){
		global $_GPC, $_W;
		$openid = $_W['openid'];
		$operation = trim($_GPC['op']);
		if($operation == 'submitorder'){
			if(empty($_W['openid'])){
				show_json(0,'未获取到用户信息，无法提交订单！');
			}
			if($_GPC['pay_type'] == 'wechat'){
				$pay_type = '1';
			}elseif($_GPC['pay_type'] == 'balance'){
				$pay_type = '2';
			}else{//未更新小程序用户
				$pay_type = '1';
			}
			$address_info = htmlspecialchars_decode($_GPC['address']);

			$_GPC['cart'] = str_replace("null,", "", $_GPC['cart']);
			$cart = htmlspecialchars_decode($_GPC['cart']);
			$count = intval($_GPC['cartCount']);
			$totalPrice = @number_format($_GPC['cartPrice'],2,".","");
			$coupon_price = @number_format($_GPC['coupon_price'],2,".","");
			$enoughdeduct = @number_format($_GPC['enoughdeduct'],2,".","");
			$member_id = intval($_GPC['member_id']);
			$store_id = intval($_GPC['store_id']);
			$desk_id = intval($_GPC['desk_id']);
			$paySetting = uni_setting($_W['uniacid'], array('payment'));
			$storeinfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$store_id), array('id','name','payment'));
			if(!empty($storeinfo['payment'])){
                $storeinfo['payment'] = iunserializer($storeinfo['payment']);
            }
			//删除之前未支付订单
			pdo_delete("deamx_food_order",array('uniacid'=>$_W['uniacid'],'openid'=>$_W['openid'],'status'=>'0','createtime <=' => TIMESTAMP - 3600 * 24 * 7));
			//创建订单
			$member = m("member")->getMemberInfo($_W['openid']);
			if($member == false){
				show_json(0, "未获取到用户信息，无法提交订单！");
			}
			$orderno = "DM".sprintf("%03d",$_W['uniacid']).date("YmdHis").mt_rand(1000,9999);
			$insertArr = array(
				'uniacid'		=>	$_W['uniacid'],
				'member_id'		=>	$member['id'],
				'openid'		=>	$_W['openid'],
				'ordersn'		=>	$orderno,
				'store_id'		=>	$store_id,
				'desk_id'		=>	$desk_id,
				'goods_list'	=>	$cart,
				'count'			=>	$count,
				'price'			=>	$totalPrice + $coupon_price + $enoughdeduct,
				'enoughdeduct'	=>	@number_format($_GPC['enoughdeduct'],2,".",""),
				'use_coupon'	=>	intval($_GPC['use_coupon']),
				'coupon_price'	=>	$coupon_price,
				'status'		=>	'0',
				'remark'		=>	trim($_GPC['remark']),
				'createtime'	=>	TIMESTAMP,
				'is_prompt'		=>	'0',
				'pbox_fee'		=>	@number_format($_GPC['pbox_fee'],2,".",""),
				'order_type'	=>	trim($_GPC['order_type']) == 'takeout' ? '2' : '1',
				'pay_type'		=>	$pay_type,//1微信，2余额
				'send_fee'		=>	@number_format($_GPC['send_fee'],2,".",""),
				'address_info'	=>	$address_info,
				'getfood_time'	=>	trim($_GPC['getfood_time']),
			);
			if($insertArr['order_type'] == '2'){
				//判断是否写入配送地址
				$addressArr = json_decode($address_info, true);
				if(empty($addressArr['telphone']) || empty($addressArr['address']) ){
					show_json(0, "请先选择配送地址");
				}
			}
			$result = pdo_insert("deamx_food_order",$insertArr);
			$orderId = pdo_insertid();
			if($orderId){
				if($pay_type == '2'){//余额
					//减少余额
					$member = m("member")->getMemberInfo($_W['openid']);
					if($member == false){
						show_json(0, "未获取到用户信息，余额扣除失败");
					}
					load()->model('mc');
					$reduceResult = m("member")->credit_update($member['id'], "credit2", -$totalPrice, array($_W['fans']['uid'], "消费买单"));
					if(empty($reduceResult['errno'])){
						//更改订单状态
						$storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$store_id));
						$updateArr = array(
							'status' => empty($storeInfo['auto_order']) ? '1' : '2',
							'paytime' => TIMESTAMP,
							'receivetime' => TIMESTAMP,
							'pay_price'		=>	$totalPrice
						);
						pdo_update('deamx_food_order', $updateArr, array(
							'id' => $orderId
						));
						//增加销量
						m("order")->setGoodsStockAndSaleVolume($orderId);
						m("order")->dealOrder($orderId);
						//积分赠送
						$sendCredit1 = com('sale')->getCredit1($member['id'],$totalPrice,'1','1');
						
						show_json(1,array('order_id'=>$orderId));
					}else{
						show_json(0,'余额扣除失败，请重新尝试或更换支付方式。');
					}
				}elseif($pay_type == '1'){//微信
					/*创建微信订单开始*/
					$orderPrice = $totalPrice * 100;
					$unifiedorder_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
					$input['appid'] = $_W['account']['key'];
					$input['mch_id'] = $paySetting['payment']['wechat']['mchid'];
					$input['nonce_str'] = random(32);
					$input['body'] = $storeinfo['name'].'-小程序点餐';
					$input['out_trade_no'] = $orderno;
					$input['total_fee'] = intval($orderPrice);
					$input['spbill_create_ip'] = CLIENT_IP;
					$input['attach'] = $_W['uniacid'];
					$input['notify_url'] = MODULE_URL.'payment/wechat/notify.php';
					$input['trade_type'] = 'JSAPI';
					$input['openid'] = $_W['openid'];
					//判断是否为服务商支付
                    $oldInput = $input;
                    if($paySetting['payment']['wechat_facilitator']['status'] == '1' && $paySetting['payment']['wechat_facilitator']['main_type'] == '1'){
                        $input['sub_mch_id'] = $input['mch_id'];
                        $input['mch_id'] = $paySetting['payment']['wechat_facilitator']['mchid'];
                        $input['sub_appid'] = $input['appid'];
                        $input['appid'] = $paySetting['payment']['wechat_facilitator']['appid'];
                        $paySetting['payment']['wechat']['signkey'] = $paySetting['payment']['wechat_facilitator']['signkey'];
                        $input['sub_openid'] = $input['openid'];
                        unset($input['openid']);
                        pdo_update("deamx_food_order", array('wechat_paytype' => '1'), array('id' => $orderId));
                    }
                    //判断是否开启单独支付

                    if($paySetting['payment']['wechat_facilitator']['status'] == '1' && $storeinfo['payment']['status'] == '1'){
                        $input['sub_mch_id'] = $storeinfo['payment']['wechat']['mchid'];
                        $input['mch_id'] = $paySetting['payment']['wechat_facilitator']['mchid'];
                        $input['sub_appid'] = $oldInput['appid'];
                        $input['appid'] = $paySetting['payment']['wechat_facilitator']['appid'];
                        $paySetting['payment']['wechat']['signkey'] = $paySetting['payment']['wechat_facilitator']['signkey'];
                        $input['sub_openid'] = $oldInput['openid'];
                        unset($input['openid']);
                        pdo_update("deamx_food_order", array('wechat_paytype' => '2'), array('id' => $orderId));
                    }

					$unSignParaStr = formatQueryParaMap($input,false);
					$signStr = $unSignParaStr."&key=".$paySetting['payment']['wechat']['signkey'];
					$input['sign'] = strtoupper(md5($signStr));
					$orderinfo = simplexml_load_string(curl_post($unifiedorder_url,arrayToXml($input)),'SimpleXMLElement', LIBXML_NOCDATA);
					$orderinfo = json_decode(json_encode($orderinfo),true);
					//print_r($orderinfo);
					if($orderinfo['return_code']=='FAIL'){
                        show_json(0,array('message' => $orderinfo['return_msg']));
                    }
					if($orderinfo['result_code']=='FAIL'){
						//
						pdo_update("deamx_food_order",array('orderno'=>"DM".sprintf("%03d",$_W['uniacid']).date("YmdHis").mt_rand(1000,9999)),array('uniacid'=>$_W['uniacid'],'id'=>$orderId));
						show_json(0,array('message'=>"订单状态异常！请重新发起支付！"));
					}
					//保存prepay_id用来下发模板消息
					if(!empty($orderinfo['prepay_id'])){
                        pdo_update("deamx_food_order",array('prepay_id'=>$orderinfo['prepay_id']),array('id'=>$orderId));
                    }
					$wOpt['appId'] = $orderinfo['appid'];
                    if($paySetting['payment']['wechat_facilitator']['status'] == '1' && ($paySetting['payment']['wechat_facilitator']['main_type'] == '1' || $storeinfo['payment']['status'] == '1')){
                        $wOpt['appId'] = $orderinfo['sub_appid'];
                    }
					$wOpt['timeStamp'] = TIMESTAMP;
					$wOpt['nonceStr'] = random(32);
					$wOpt['package'] = 'prepay_id='.$orderinfo['prepay_id'];
					$wOpt['signType'] = 'MD5';
					$paySignParaStr = formatQueryParaMap($wOpt,false);
					$paysignStr = $paySignParaStr."&key=".$paySetting['payment']['wechat']['signkey'];
					$wOpt['order_id'] = $orderId;
					$wOpt['paySign'] = strtoupper(md5($paysignStr));
					$wOpt['success'] = true;
					$wOpt['weixin'] = true;
					$wOpt['jie'] = 0;
					$wOpt['order_id'] = $orderId;
					show_json(1,$wOpt);
				}
				
			}else{
				show_json(0,array('message'=>"创建订单失败，请重试！"));
			}
		}elseif($operation == 'recharge'){
			if(empty($_W['openid'])){
				show_json(0,'未获取到用户信息，无法充值！');
			}
			$paySetting = uni_setting($_W['uniacid'], array('payment'));
			//删除之前未支付订单
			pdo_delete("deamx_food_recharge_log",array('uniacid'=>$_W['uniacid'],'openid'=>$_W['openid'],'status'=>'0','createtime <=' => TIMESTAMP - 3600 * 24 * 7));
			//创建订单
			$orderno = "RE".sprintf("%03d",$_W['uniacid']).date("YmdHis").mt_rand(1000,9999);
			$member = m("member")->getMemberInfo($_W['openid']);
			if($member == false){
				show_json(0, "未获取到用户信息，无法充值！");
			}
			$insertArr = array(
				'uniacid'		=>	$_W['uniacid'],
				'openid'		=>	$_W['openid'],
				'title'			=>	"会员充值",
				'ordersn'		=>	$orderno,
				'uid'			=>	$member['id'],
				'price'			=>	@number_format($_GPC['price'],2,".",""),
				'createtime'	=>	TIMESTAMP,
				'status'		=>	'0'
			);
			$result = pdo_insert("deamx_food_recharge_log",$insertArr);
			$orderId = pdo_insertid();
			if($orderId){
				/*创建微信订单开始*/
				$orderPrice = intval($insertArr['price'] * 100);
				$unifiedorder_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
				$input['appid'] = $_W['account']['key'];
				$input['mch_id'] = $paySetting['payment']['wechat']['mchid'];
				$input['nonce_str'] = random(32);
				$input['body'] = '余额充值-小程序点餐';
				$input['out_trade_no'] = $orderno;
				$input['total_fee'] = intval($orderPrice);
				$input['spbill_create_ip'] = CLIENT_IP;
				$input['attach'] = $_W['uniacid'];
				$input['notify_url'] = MODULE_URL.'payment/wechat/recharge.php';
				$input['trade_type'] = 'JSAPI';
				$input['openid'] = $_W['openid'];
                //判断是否为服务商支付
                $oldInput = $input;
                if($paySetting['payment']['wechat_facilitator']['status'] == '1' && $paySetting['payment']['wechat_facilitator']['main_type'] == '1'){
                    $input['sub_mch_id'] = $input['mch_id'];
                    $input['mch_id'] = $paySetting['payment']['wechat_facilitator']['mchid'];
                    $input['sub_appid'] = $input['appid'];
                    $input['appid'] = $paySetting['payment']['wechat_facilitator']['appid'];
                    $paySetting['payment']['wechat']['signkey'] = $paySetting['payment']['wechat_facilitator']['signkey'];
                    $input['sub_openid'] = $input['openid'];
                    unset($input['openid']);
                }
				$unSignParaStr = formatQueryParaMap($input,false);
				$signStr = $unSignParaStr."&key=".$paySetting['payment']['wechat']['signkey'];
				$input['sign'] = strtoupper(md5($signStr));
				$orderinfo = simplexml_load_string(curl_post($unifiedorder_url,arrayToXml($input)),'SimpleXMLElement', LIBXML_NOCDATA);
				$orderinfo = json_decode(json_encode($orderinfo),true);
				if($orderinfo['result_code']=='FAIL'){
					//
					pdo_update("deamx_food_recharge_log",array('orderno'=>"RE".sprintf("%03d",$_W['uniacid']).date("YmdHis").mt_rand(1000,9999)),array('uniacid'=>$_W['uniacid'],'id'=>$orderId));
					show_json(0,array('message'=>"订单状态异常！请重新发起支付！"));
				}
				//保存prepay_id用来下发模板消息
                if(!empty($orderinfo['prepay_id'])){
                    pdo_update("deamx_food_recharge_log",array('prepay_id'=>$orderinfo['prepay_id']),array('id'=>$orderId));
                }
				$wOpt['appId'] = $orderinfo['appid'];
                if($paySetting['payment']['wechat_facilitator']['status'] == '1' && $paySetting['payment']['wechat_facilitator']['main_type'] == '1'){
                    $wOpt['appId'] = $orderinfo['sub_appid'];
                }
				$wOpt['timeStamp'] = TIMESTAMP;
				$wOpt['nonceStr'] = random(32);
				$wOpt['package'] = 'prepay_id='.$orderinfo['prepay_id'];
				$wOpt['signType'] = 'MD5';
				$paySignParaStr = formatQueryParaMap($wOpt,false);
				$paysignStr = $paySignParaStr."&key=".$paySetting['payment']['wechat']['signkey'];
				$wOpt['order_id'] = $orderId;
				$wOpt['paySign'] = strtoupper(md5($paysignStr));
				$wOpt['success'] = true;
				$wOpt['weixin'] = true;
				$wOpt['jie'] = 0;
				$wOpt['order_id'] = $orderId;
				show_json(1,$wOpt);
			}else{
				show_json(0,array('message'=>"创建订单失败，请重试！"));
			}
		}elseif($operation == 'submitaddr'){
			if(empty($_W['openid'])){
				show_json(0,'未获取到用户信息，无法提交信息');
			}
			$member_id = intval($_GPC['member_id']);
			$addr_id = intval($_GPC['addressid']);
			$realname = trim($_GPC['realname']);
			$telphone = trim($_GPC['telphone']);
			$address = trim($_GPC['address']);
			$name = trim($_GPC['name']);
			$latitude = trim($_GPC['latitude']);
			$longitude = trim($_GPC['longitude']);
			$addrnumber = trim($_GPC['addrnumber']);
			$postArr = array(
				'uniacid'		=>	$_W['uniacid'],
				'member_id'		=>	$member_id,
				'openid'		=>	$_W['openid'],
				'realname'		=>	$realname,
				'telphone'		=>	$telphone,
				'address'		=>	$name,
				'address_road'	=>	$address,
				'latitude'		=>	$latitude,
				'longitude'		=>	$longitude,
				'number'		=>	$addrnumber,
				'is_default'	=>	0,
				'createtime'	=>	TIMESTAMP
			);
			if(empty($addr_id)){
				$result = pdo_insert("deamx_food_address",$postArr);
			}else{
				unset($postArr['createtime']);
				$result = pdo_update("deamx_food_address",$postArr,array('id'=>$addr_id));
			}
			if(empty($result)){
				show_json(0,array('message'=>empty($addr_id) ? '添加失败' : '更新失败'));
			}else{
				show_json(1,array('message'=>empty($addr_id) ? '添加成功' : '更新成功'));
			}

		}elseif($operation == 'delteaddr'){
			if(empty($_W['openid'])){
				show_json(0,'未获取到用户信息，无法删除！');
			}
			$member_id = intval($_GPC['member_id']);
			$addr_id = intval($_GPC['addressid']);
			$result = pdo_delete("deamx_food_address",array('uniacid'=>$_W['uniacid'],'id'=>$addr_id,'openid'=>$_W['openid']));
			if(empty($result)){
				show_json(0,array('message'=>'删除失败'));
			}else{
				show_json(1,array('message'=>'删除成功'));
			}
		}elseif($operation == 'call_waiter') {
			$desk_id = intval($_GPC['desk_id']);
			$store_id = intval($_GPC['store_id']);
			if(!empty($desk_id)){
				$deskinfo = pdo_get("deamx_food_desknumber",array('uniacid'=>$_W['uniacid'],'id'=>$desk_id),array('id','name'));
				//$deskinfo['name']
				$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']),array('bell_settings'));
				$settings['bell_settings'] = json_decode($settings['bell_settings'],true);
				require_once DM_ROOT.'/vendor/aipSpeech/AipSpeech.php';
				$APP_ID = $settings['bell_settings']['appid'];
				$API_KEY = $settings['bell_settings']['apikey'];
				$SECRET_KEY = $settings['bell_settings']['secretkey'];
				if(empty($APP_ID) || empty($API_KEY) || empty($SECRET_KEY)){
					show_json(0,"呼叫功能尚未配置，请到总后台进行配置！");
				}
				$client = new AipSpeech($APP_ID, $API_KEY, $SECRET_KEY);
				$text = $settings['bell_settings']['getwaiter_front'].$deskinfo['name'].$settings['bell_settings']['getwaiter_behind'];
				$result = $client->synthesis($text, 'zh', 1, array(
				    'vol' => 15,
				    'per' => 0,
				    'spd' => 3,
				));
				if(!is_array($result)){
					$audio_name = "CALL_".md5($text).".mp3";
					$audio_dir = MODULE_ROOT."/data/audios/foodBell/{$_W['uniacid']}";
					if (!file_exists($audio_dir)){
					    @mkdir($audio_dir,0777,true);
					}
					$audio_status = @file_put_contents($audio_dir."/".$audio_name, $result);
					if(!empty($audio_status)){
						//show_json(1,"../addons/deam_food/data/audios/foodBell/{$_W['uniacid']}/{$audio_name}");
						$insertArr = array(
							'uniacid'		=>	$_W['uniacid'],
							'from_openid'	=>	$_W['openid'],
							'store_id'		=>	$store_id,
							'type'			=>	'1',
							'title'			=>	"桌号[{$deskinfo['name']}]呼叫服务员",
							'content'		=>	"audios/foodBell/{$_W['uniacid']}/{$audio_name}",
							'status'		=>	'0',
							'createtime'	=>	TIMESTAMP
						);
						pdo_insert("deamx_food_notice",$insertArr);
						show_json(1);
					}else{
						show_json(0,"文件保存失败，请检查目录".MODULE_ROOT."/data是否为可写(777)状态");
					}
				}else{
					show_json(0,$result['err_msg']);
				}

			}else{
				show_json(0,"扫描桌号二维码点餐才能呼叫服务员");
			}
			echo $_W['openid'];
		}elseif($operation == 'set_memberinfo'){
			$realname = trim($_GPC['realname']);
			if(empty($_W['openid'])){
				show_json(0, '未获取到用户信息，无法修改！');
			}
			if(!empty($realname)){
				pdo_update("deamx_food_members", array('realname' => $realname), array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid']));
				show_json(1);
			}else{
				show_json(0, '修改失败，请稍后再试！');
			}
			
		}
	}
	public function doPageCoupon(){
		global $_GPC, $_W;
		$operation = trim($_GPC['op']);
		load()->func('communication');
		load()->classs('wxapp.account');
		$accObj= WxappAccount::create($_W['uniacid']);
		$access_token = $accObj->getAccessToken();
		if($operation == 'test'){
			$cardId = "pGYIaxPRyaTooH1AVegRtmloiqzM";
			load()->classs('coupon');
			$coupon_api = new coupon('3');
			$cardTicket = $coupon_api->getCardTicket();
			$time = TIMESTAMP;
			$sign = array($cardId, $time);
			$signature = $coupon_api->SignatureCard($sign);
			//show_json(1,array('cardid'=>$cardId,'timestamp' => $time, 'signature' => $signature));
		}elseif($operation == 'choose'){
			$store_id = intval($_GPC['store_id']);
			$price = intval($_GPC['price']*100);//分
			$store_type = trim($_GPC['store_type']);
			$couponList = pdo_fetchall("SELECT r.*,COUNT(r.id) AS coupon_count,c.title,c.least_cost,c.reduce_cost FROM ".tablename("deamx_food_coupon_record")." r LEFT JOIN ".tablename('deamx_food_coupon')." c ON r.card_id=c.card_id AND r.uniacid=c.coupon_uniacid WHERE c.least_cost<=:least_cost AND r.starttime<=:time AND r.endtime>=:time AND c.title!='' AND c.uniacid=:uniacid AND r.unionid=:unionid AND r.status=:status GROUP BY r.card_id ORDER BY r.id DESC",array(':uniacid'=>$_W['uniacid'],':unionid'=>$_W['fans']['unionid'],':least_cost'=>$price,':time'=>TIMESTAMP,':status'=>'1'));
			foreach ($couponList as $key => &$row) {
				$row['max_use'] = intval($price / $row['least_cost']) >= $row['coupon_count'] ? $row['coupon_count'] : intval($price / $row['least_cost']);
				$row['total_reduce_cost'] = floatval($row['max_use'] * $row['reduce_cost']/100);
				$row['reduce_cost'] = $row['reduce_cost']/100;
				$row['least_cost'] = $row['least_cost']/100;
				$row['starttime'] = date("Y.m.d",$row['starttime']);
				$row['endtime'] = date("Y.m.d",$row['endtime']);

			}
			show_json(count($couponList),array('list'=>$couponList));
		}elseif($operation == 'upload_wechat'){
			$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']), array('template_status', 'template_id','sms_status','sms_type','sms_params','takeout_template_id','coupon_uniacid'));
			$couponid = intval($_GPC['couponid']);
			$couponinfo = pdo_get("deamx_food_coupon_record",array('uniacid'=>$settings['coupon_uniacid'],'id'=>$couponid));
			load()->classs('coupon');
			$coupon_api = new coupon($settings['coupon_uniacid']);
			$cardTicket = $coupon_api->getCardTicket();
			$time = TIMESTAMP;
			$sign = array($couponinfo['card_id'], $time);
			$signature = $coupon_api->SignatureCard($sign);
			show_json(1,array('cardid'=>$couponinfo['card_id'],'timestamp' => $time, 'signature' => $signature,'outer_str'=>$couponid));
		}
	}
	public function doPageSandbox(){
		global $_GPC, $_W;
		$operation = trim($_GPC['op']);
		if($operation == 'topay'){
			$paySetting = uni_setting($_W['uniacid'], array('payment'));
			$orderNo = 'DMT'.date("YmdHis").mt_rand(1000,9999);
			$getsignkey_url = "https://api.mch.weixin.qq.com/sandboxnew/pay/getsignkey";
			$input['mch_id'] = $paySetting['payment']['wechat']['mchid'];
			$input['nonce_str'] = random(32);
			$unSignParaStr = formatQueryParaMap($input,false);
			$signStr = $unSignParaStr."&key=".$paySetting['payment']['wechat']['signkey'];
			$input['sign'] = strtoupper(md5($signStr));
			$signkeyinfo = simplexml_load_string(curl_post($getsignkey_url,arrayToXml($input)),'SimpleXMLElement', LIBXML_NOCDATA);
			$signkeyinfo = json_decode(json_encode($signkeyinfo),true);

			unset($input);
			$unifiedorder_url = 'https://api.mch.weixin.qq.com/sandboxnew/pay/unifiedorder';
			$input['appid'] = $_W['account']['key'];
			$input['mch_id'] = $paySetting['payment']['wechat']['mchid'];
			$input['nonce_str'] = random(32);
			$input['body'] = '点沐-开通免充值代金券';
			$input['out_trade_no'] = $orderNo;
			$input['total_fee'] = '551';
			$input['spbill_create_ip'] = CLIENT_IP;
			$input['attach'] = $_W['uniacid'];
			$input['notify_url'] = MODULE_URL.'payment/wechat/notify_sandbox.php';
			$input['trade_type'] = 'JSAPI';
			//$input['openid'] = $_W['openid'];
			$input['openid'] = "";
			$unSignParaStr = formatQueryParaMap($input,false);
			$signStr = $unSignParaStr."&key=".$signkeyinfo['sandbox_signkey'];
			$input['sign'] = strtoupper(md5($signStr));
			$orderinfo = simplexml_load_string(curl_post($unifiedorder_url,arrayToXml($input)),'SimpleXMLElement', LIBXML_NOCDATA);
			$orderinfo = json_decode(json_encode($orderinfo),true);
			
			if($orderinfo['result_code']=='FAIL'){
				//
				
			}


			$orderquery_url = "https://api.mch.weixin.qq.com/sandboxnew/orderquery";
			$input = array();
			$input['appid'] = $_W['account']['key'];
			$input['mch_id'] = $paySetting['payment']['wechat']['mchid'];
			$input['out_trade_no'] = $orderNo;
			$input['nonce_str'] = random(32);
			
			$unSignParaStr = formatQueryParaMap($input,false);
			$signStr = $unSignParaStr."&key=".$signkeyinfo['sandbox_signkey'];
			$input['sign'] = strtoupper(md5($signStr));
			
			$orderQueryInfo = simplexml_load_string(curl_post($orderquery_url,arrayToXml($input)),'SimpleXMLElement', LIBXML_NOCDATA);//红包预下单返回信息
			$orderQueryInfo = json_decode(json_encode($orderQueryInfo),true);


			//1004
			$orderNo = 'DMT'.date("YmdHis").mt_rand(1000,9999);
			$getsignkey_url = "https://api.mch.weixin.qq.com/sandboxnew/pay/getsignkey";
			$input['mch_id'] = $paySetting['payment']['wechat']['mchid'];
			$input['nonce_str'] = random(32);
			$unSignParaStr = formatQueryParaMap($input,false);
			$signStr = $unSignParaStr."&key=".$paySetting['payment']['wechat']['signkey'];
			$input['sign'] = strtoupper(md5($signStr));
			$signkeyinfo = simplexml_load_string(curl_post($getsignkey_url,arrayToXml($input)),'SimpleXMLElement', LIBXML_NOCDATA);
			$signkeyinfo = json_decode(json_encode($signkeyinfo),true);

			unset($input);
			$unifiedorder_url = 'https://api.mch.weixin.qq.com/sandboxnew/pay/unifiedorder';
			$input['appid'] = $_W['account']['key'];
			$input['mch_id'] = $paySetting['payment']['wechat']['mchid'];
			$input['nonce_str'] = random(32);
			$input['body'] = '点沐-开通免充值代金券';
			$input['out_trade_no'] = $orderNo;
			$input['total_fee'] = '552';
			$input['spbill_create_ip'] = CLIENT_IP;
			$input['attach'] = $_W['uniacid'];
			$input['notify_url'] = MODULE_URL.'payment/wechat/notify_sandbox.php';
			$input['trade_type'] = 'JSAPI';
			//$input['openid'] = $_W['openid'];
			$input['openid'] = "";
			$unSignParaStr = formatQueryParaMap($input,false);
			$signStr = $unSignParaStr."&key=".$signkeyinfo['sandbox_signkey'];
			$input['sign'] = strtoupper(md5($signStr));
			$orderinfo = simplexml_load_string(curl_post($unifiedorder_url,arrayToXml($input)),'SimpleXMLElement', LIBXML_NOCDATA);
			$orderinfo = json_decode(json_encode($orderinfo),true);
			
			if($orderinfo['result_code']=='FAIL'){
				//
				
			}


			$orderquery_url = "https://api.mch.weixin.qq.com/sandboxnew/orderquery";
			$input = array();
			$input['appid'] = $_W['account']['key'];
			$input['mch_id'] = $paySetting['payment']['wechat']['mchid'];
			$input['out_trade_no'] = $orderNo;
			$input['nonce_str'] = random(32);
			
			$unSignParaStr = formatQueryParaMap($input,false);
			$signStr = $unSignParaStr."&key=".$signkeyinfo['sandbox_signkey'];
			$input['sign'] = strtoupper(md5($signStr));
			
			$orderQueryInfo = simplexml_load_string(curl_post($orderquery_url,arrayToXml($input)),'SimpleXMLElement', LIBXML_NOCDATA);//红包预下单返回信息
			$orderQueryInfo = json_decode(json_encode($orderQueryInfo),true);

			$refund_url = "https://api.mch.weixin.qq.com/sandboxnew/secapi/pay/refund";

			$setting = uni_setting_load('payment', $_W['uniacid']);
			$pay_setting = $setting['payment'];
			$cert =  authcode($pay_setting['wechat_refund']['cert'], 'DECODE');
			$key =  authcode($pay_setting['wechat_refund']['key'], 'DECODE');
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
			$refundsn = "RFT".sprintf("%03d",$_W['uniacid']).date("YmdHis").mt_rand(1000,9999);
			$refund_param = array(
				'appid' => $_W['account']['key'],
				'mch_id' => $paySetting['payment']['wechat']['mchid'],
				'out_trade_no' => $orderNo,
				'out_refund_no' => $refundsn,
				'total_fee' => '552',
				'refund_fee' => '552',
				'nonce_str' => random(8),
				'refund_desc' => $refund_reason
			);
			$unSignParaStr = formatQueryParaMap($refund_param,false);
			$signStr = $unSignParaStr."&key=".$signkeyinfo['sandbox_signkey'];
			$refund_param['sign'] = strtoupper(md5($signStr));
			$refundinfo = simplexml_load_string(curl_post_ssl($refund_url,arrayToXml($refund_param),$cert_dir."/".$cert_name,$cert_dir."/".$key_name),'SimpleXMLElement', LIBXML_NOCDATA);
			$refundinfo = json_decode(json_encode($refundinfo),true);
			//print_r($refundinfo);

			$refundquery_url = "https://api.mch.weixin.qq.com/sandboxnew/pay/refundquery";
			$input = array();
			$input['appid'] = $_W['account']['key'];
			$input['mch_id'] = $paySetting['payment']['wechat']['mchid'];
			$input['out_trade_no'] = $orderNo;
			$input['nonce_str'] = random(32);
			
			$unSignParaStr = formatQueryParaMap($input,false);
			$signStr = $unSignParaStr."&key=".$signkeyinfo['sandbox_signkey'];
			$input['sign'] = strtoupper(md5($signStr));
			
			$refundqueryInfo = simplexml_load_string(curl_post($refundquery_url,arrayToXml($input)),'SimpleXMLElement', LIBXML_NOCDATA);//红包预下单返回信息
			$refundqueryInfo = json_decode(json_encode($refundqueryInfo),true);
			//print_r($refundqueryInfo);

			$downloadbill_url = "https://api.mch.weixin.qq.com/sandboxnew/pay/downloadbill";
			$input = array();
			$input['appid'] = $_W['account']['key'];
			$input['mch_id'] = $paySetting['payment']['wechat']['mchid'];
			$input['nonce_str'] = random(32);
			$input['bill_type'] = "SUCCESS";
			$unSignParaStr = formatQueryParaMap($input,false);
			$signStr = $unSignParaStr."&key=".$signkeyinfo['sandbox_signkey'];
			$input['sign'] = strtoupper(md5($signStr));
			
			$downloadbillInfo = curl_post($downloadbill_url,arrayToXml($input));//红包预下单返回信息
			//print_r($downloadbillInfo);
			show_json(0,array('message'=>"开通成功！"));
		}
	}
	public function doPageImFox(){
	    global $_W, $_GPC;
        $router = trim($_GPC['r']);
        imx($router)->run();
    }
}
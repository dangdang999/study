<?php
/**
 * 门店管理后台
 *
 * @author cmszs
 * @url
 */
defined('IN_IA') or exit('Access Denied');
class Deal_DeamFoodManage extends imfoxMember
{
    public function printer(){
        global $_GPC,$_W;
        $orderId = intval($_GPC['order_id']);
        $storeId = $_W['deam_food']['manage']['user']['store_id'];
        $orderInfo = pdo_get("deamx_food_order",array('uniacid' => $_W['uniacid'], 'id' => $orderId, 'store_id' => $storeId));
        if(empty($orderInfo)){
            show_json(0, "订单不存在！");
        }

        $storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$orderInfo['store_id']));
        //获取打印机列表
        $printerArr = pdo_getall("deamx_food_printer",array('uniacid'=>$_W['uniacid'],'status'=>'1','store_id'=>$orderInfo['store_id']));
        $orderNumber = $orderInfo['order_number'];
        $printText = "手动打印订单，请注意不要重复制作";
        if(!empty($printerArr)){
            //打印
            com('printer')->deamPrint($orderId, $printText);
            show_json(0, "手动打印订单成功！");
        }else{
            show_json(0, "该门店还未设置打印机！");
        }

    }
    public function success(){
        global $_GPC,$_W;
        $orderId = intval($_GPC['order_id']);
        $this->successOrder($orderId);
    }

    public function confirm(){
        global $_GPC,$_W;
        $orderId = intval($_GPC['order_id']);
        $storeId = $_W['deam_food']['manage']['user']['store_id'];
        $orderInfo = pdo_get("deamx_food_order",array('uniacid' => $_W['uniacid'], 'id' => $orderId, 'store_id' => $storeId));
        if(empty($orderInfo)){
            show_json(0, "订单不存在！");
        }elseif($orderInfo['status'] != '1'){
            show_json(0,array('message' => '订单非可接单状态！'));
        }
        $storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$storeId), array('id','name', 'payment'));
        //获取模板消息设置
        $settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']), array('template_status', 'template_id','takeout_template_id','deliver_dada_status','deliver_dada_app_key','deliver_dada_app_secret','deliver_dada_shopid'));
        require DM_ROOT.'/common/print.class.php';
        //接单
        $updateResult = pdo_update('deamx_food_order', array(
            'status' => '2',
            'receivetime' => TIMESTAMP
        ), array(
            'id' => $orderId
        ));
        //判断是否为扫桌号小程序订餐
        if(!empty($orderInfo['desk_id'])){
            $deskInfo = pdo_get("deamx_food_desknumber",array('uniacid'=>$_W['uniacid'],'id'=>$orderInfo['desk_id'],'store_id'=>$orderInfo['store_id']),array('id','name'));
        }
        if($orderInfo['order_type'] != '2'){
            //获取今日已接单订单数
            $todaytime = strtotime(date('Y-m-d'));
            $orderCount = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('deamx_food_order')." WHERE uniacid=:uniacid and store_id=:store_id and status>=2 and receivetime>=:todaytime and order_type=:order_type",array(':uniacid'=>$_W['uniacid'],':store_id'=>$orderInfo['store_id'],':todaytime'=>$todaytime,':order_type'=>'1'));
            $orderNumber = "A".sprintf("%03d",$orderCount);//取单号
            $updateResult = pdo_update('deamx_food_order', array(
                'order_number' => $orderNumber
            ), array(
                'id' => $orderId
            ));
            $template_remark = TEMPLATE_REMARK;
        }
        if($orderInfo['order_type'] != '2'){
            if(!empty($settings['template_status']) && !empty($settings['template_id'])){

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
                $templateMessageUrl = "deam_food/pages/order/detail?id=".$orderId;
                $messageResult = wxappMessage($access_token,$orderInfo['openid'],$settings['template_id'],$templateMessageUrl,$orderInfo['prepay_id'],$templateMessageArr,"keyword1.DATA");
                if($messageResult['errcode'] == '0'){
                    pdo_update('deamx_food_order', array(
                        'message_count +=' => '1'
                    ), array(
                        'id' => $orderId
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
                    $addressinfo = json_decode($orderInfo['address_info'],true);
                    $addOrder = array(
                        'shop_no'	=>	$storeInfo['deliver_dada_shopno'],
                        'origin_id' =>	$orderInfo['ordersn'],
                        'city_code'	=>	$storeInfo['deliver_dada_citycode'],
                        'cargo_price'	=>	$orderInfo['price'],
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
                            'id' => $orderId
                        ));
                    }else{
                        $updateResult = pdo_update('deamx_food_order', array(
                            'deliver_type' => -1,
                            'deliver_dada_failreason' => $imdada->error_code($result['errorCode']),
                        ), array(
                            'id' => $orderId
                        ));
                        //记录文本日志
                        logging_run($orderInfo['ordersn']);
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
                $templateMessageUrl = "deam_food/pages/order/detail?id=".$orderId;
                $messageResult = wxappMessage($access_token,$orderInfo['openid'],$settings['takeout_template_id'],$templateMessageUrl,$orderInfo['prepay_id'],$templateMessageArr,"keyword1.DATA");
                //file_put_contents("messageResult.log", $messageResult);
                if($messageResult['errcode'] == '0'){
                    pdo_update('deamx_food_order', array(
                        'message_count +=' => '1'
                    ), array(
                        'id' => $orderId
                    ));
                }

            }
        }
        com('printer')->deamPrint($orderId);
        show_json(1, "接单成功！");
    }
    public function cancel(){
        global $_GPC,$_W;
        $orderId = intval($_GPC['order_id']);
        $storeId = $_W['deam_food']['manage']['user']['store_id'];
        $orderInfo = pdo_get("deamx_food_order",array('uniacid' => $_W['uniacid'], 'id' => $orderId, 'store_id' => $storeId));
        if(empty($orderInfo)){
            show_json(0, "订单不存在！");
        }elseif($orderInfo['status'] > '3'){
            show_json(0,array('message' => '订单非可取消状态！'));
        }
        $storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$storeId), array('id','name', 'payment'));
        if(!empty($storeinfo['payment'])){
            $storeInfo['payment'] = iunserializer($storeInfo['payment']);
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
                    $refund_param['sub_mch_id'] = $storeInfo['payment']['wechat']['mchid'];
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
                pdo_update("deamx_food_order",array('refund_fee +='=>$refund_fee),array('uniacid'=>$_W['uniacid'],'id'=>$orderId));
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
                    pdo_update("deamx_food_order",array('refund_fee +='=>$refund_fee),array('uniacid'=>$_W['uniacid'],'id'=>$orderId));
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
        show_json(1, "订单已取消！");
    }
    public function driving(){
        global $_GPC,$_W;
        $orderId = intval($_GPC['order_id']);
        $storeId = $_W['deam_food']['manage']['user']['store_id'];
        $orderInfo = pdo_get("deamx_food_order",array('uniacid' => $_W['uniacid'], 'id' => $orderId, 'store_id' => $storeId));
        if(empty($orderInfo)){
            show_json(0, "订单不存在！");
        }elseif($orderInfo['status'] != '2' && $orderInfo['order_type'] == '2'){
            show_json(0,array('message' => '订单非待配送状态！'));
        }
        $storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$storeId), array('id','name', 'payment'));
        //获取模板消息设置
        $settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']), array('template_status', 'template_id','takeout_template_id','deliver_dada_status','deliver_dada_app_key','deliver_dada_app_secret','deliver_dada_shopid'));

        pdo_update("deamx_food_order",array('status'=>'4'),array('uniacid'=>$_W['uniacid'],'id'=>$orderId));
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
            $templateMessageUrl = "deam_food/pages/order/detail?id=".$orderId;
            $messageResult = wxappMessage($access_token,$orderInfo['openid'],$settings['takeout_template_id'],$templateMessageUrl,$orderInfo['prepay_id'],$templateMessageArr,"keyword1.DATA");
            //file_put_contents("messageResult.log", $messageResult);
            if($messageResult['errcode'] == '0'){
                pdo_update('deamx_food_order', array(
                    'message_count +=' => '1'
                ), array(
                    'id' => $orderId
                ));
            }
        }
        show_json(1, "操作成功！");
    }
    public function drived(){
        global $_GPC,$_W;
        $orderId = intval($_GPC['order_id']);
        $this->successOrder($orderId);
    }
    protected function successOrder($order_id){
        global $_GPC,$_W;
        $orderId = $order_id;
        $storeId = $_W['deam_food']['manage']['user']['store_id'];
        $orderInfo = pdo_get("deamx_food_order",array('uniacid' => $_W['uniacid'], 'id' => $orderId, 'store_id' => $storeId));
        if(empty($orderInfo)){
            show_json(0, "订单不存在！");
        }elseif(($orderInfo['status'] != '2' && $orderInfo['order_type'] == '1') || ($orderInfo['status'] != '4' && $orderInfo['order_type'] == '2')){
            show_json(0,array('message' => '订单不可完成！'));
        }
        $storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$storeId), array('id','name', 'payment'));
        //获取模板消息设置
        $settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']), array('template_status', 'template_id','takeout_template_id','deliver_dada_status','deliver_dada_app_key','deliver_dada_app_secret','deliver_dada_shopid'));
        if($orderInfo['order_type'] == '1'){
            pdo_update("deamx_food_order",array('status'=>'3'),array('uniacid'=>$_W['uniacid'],'id'=>$orderId));
        }elseif($orderInfo['order_type'] == '2'){//外卖
            pdo_update("deamx_food_order",array('status'=>'3'),array('uniacid'=>$_W['uniacid'],'id'=>$orderId));
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
                $templateMessageUrl = "deam_food/pages/order/detail?id=".$orderId;
                $messageResult = wxappMessage($access_token,$orderInfo['openid'],$settings['takeout_template_id'],$templateMessageUrl,$orderInfo['prepay_id'],$templateMessageArr,"keyword1.DATA");
                //file_put_contents("messageResult.log", $messageResult);
                if($messageResult['errcode'] == '0'){
                    pdo_update('deamx_food_order', array(
                        'message_count +=' => '1'
                    ), array(
                        'id' => $orderId
                    ));
                }
            }
        }
        show_json(1, "操作成功！");
    }
}
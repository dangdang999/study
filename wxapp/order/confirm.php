<?php
/**
 * Created by PhpStorm.
 * User: 南京狸先生信息技术有限公司
 * Date: 2020-01-05
 * Time: 19:33
 */
if( !defined("IN_IA") )
{
    exit( "Access Denied" );
}
class Confirm extends ImFoxModule{
    public function getCartList(){
        global $_W, $_GPC;
        $cartJson = htmlspecialchars_decode($_GPC['cart']);
        $cartArr = json_decode($cartJson, true);
        if (empty($cartArr)) {
            show_json(0, "购物车是空的");
        }
        //获取商品id
        $goodsIdArr = [];
        $goodsOptionsIdArr = [];
        foreach ($cartArr as $index => &$cartInfo) {
            if(!in_array($cartInfo['goodsId'], $goodsIdArr)) {
                $goodsIdArr[] = $cartInfo['goodsId'];
            }
            if(!in_array($cartInfo['optionsRealId'], $goodsOptionsIdArr) && $cartInfo['optionsRealId'] != '0') {
                $goodsOptionsIdArr[] = $cartInfo['optionsRealId'];
            }
            $cartInfo['price'] = 0;
            $cartInfo['perPrice'] = 0;
        }
        if (empty($goodsIdArr)) {
            show_json(0, "购物车是空的");
        }
        $goodsArr = pdo_getall("deamx_food_goods", array('id' => $goodsIdArr, 'uniacid' => $_W['uniacid']), array('id', 'name', 'price', 'is_pbox', 'pbox_price', 'img'), 'id');
        if (!empty($goodsOptionsIdArr)) {
            $goodsOptionsArr = pdo_getall("deamx_food_goods_option", array('id' => $goodsOptionsIdArr, 'uniacid' => $_W['uniacid']), array('id', 'marketprice'), 'id');
        }
        $cartPrice = 0;
        $cartCount = 0;
        $pboxPrice = 0;
        foreach ($cartArr as $index => &$cartInfo) {
            if ($cartInfo['count'] > '0') {
                if ($cartInfo['optionsId'] == '0') {
                    //无多规格
                    if ($goodsArr[$cartInfo['goodsId']]['price'] > '0') {
                        $cartInfo['perPrice'] = floatval($goodsArr[$cartInfo['goodsId']]['price']);
                        $cartInfo['price'] = floatval($goodsArr[$cartInfo['goodsId']]['price'] * $cartInfo['count']);
                        $cartPrice += $cartInfo['price'];
                        $cartCount += $cartInfo['count'];
                        if ($goodsArr[$cartInfo['goodsId']]['is_pbox'] == '1') {
                            $pboxPrice += floatval($goodsArr[$cartInfo['goodsId']]['pbox_price'] * $cartInfo['count']);
                        }
                    } else {
                        unset($cartArr[$index]);
                    }
                } else {
                    //多规格
                    if ($goodsOptionsArr[$cartInfo['optionsRealId']]['marketprice'] > '0') {
                        $cartInfo['perPrice'] = floatval($goodsOptionsArr[$cartInfo['optionsRealId']]['marketprice']);
                        $cartInfo['price'] = floatval($goodsOptionsArr[$cartInfo['optionsRealId']]['marketprice'] * $cartInfo['count']);
                        $cartPrice += $cartInfo['price'];
                        $cartCount += $cartInfo['count'];
                        if ($goodsArr[$cartInfo['goodsId']]['is_pbox'] == '1') {
                            $pboxPrice += floatval($goodsArr[$cartInfo['goodsId']]['pbox_price'] * $cartInfo['count']);
                        }
                    } else {
                        unset($cartArr[$index]);
                    }
                }
                $cartInfo['imgUrl'] = empty($goodsArr[$cartInfo['goodsId']]['img']) ? '' : tomedia($goodsArr[$cartInfo['goodsId']]['img']);
            } else {
                unset($cartArr[$index]);
            }
        }
        show_json(1, array('cart' => $cartArr, 'cartPrice' => $cartPrice, 'cartCount' => $cartCount, 'pboxPrice' => $pboxPrice));
    }
    public function submitOrder(){
        global $_GPC, $_W;
        $openid = $_W['openid'];
        if(empty($openid)){
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

        $count = intval($_GPC['cart_count']);
        $payPrice = floatval($_GPC['pay_price']);
        $cartPrice = floatval($_GPC['cart_price']);
        $couponPrice = floatval($_GPC['coupon_price']);
        $enoughDeduct = floatval($_GPC['enough_deduct']);
        $pboxFee = floatval($_GPC['pbox_fee']);
        $sendFee = floatval($_GPC['send_fee']);

        $member_id = intval($_GPC['member_id']);
        $store_id = intval($_GPC['store_id']);
        $desk_id = intval($_GPC['desk_id']);

        $paySetting = uni_setting($_W['uniacid'], array('payment'));
        $storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$store_id), array('id','name','payment', 'auto_order'));
        if(!empty($storeInfo['payment'])){
            $storeInfo['payment'] = iunserializer($storeInfo['payment']);
        }
        //删除之前未支付订单
        pdo_delete("deamx_food_order",array('uniacid'=>$_W['uniacid'],'openid'=>$_W['openid'],'status'=>'0','createtime <=' => TIMESTAMP - 3600 * 24 * 3));
        //创建订单
        $member = m("member")->getMemberInfo($_W['openid']);
        if($member == false){
            show_json(0, "未获取到用户信息，无法提交订单！");
        }
        $orderNo = "DM".sprintf("%03d",$_W['uniacid']).date("YmdHis").mt_rand(1000,9999);
        $insertArr = array(
            'uniacid'		=>	$_W['uniacid'],
            'member_id'		=>	$member['id'],
            'openid'		=>	$_W['openid'],
            'ordersn'		=>	$orderNo,
            'store_id'		=>	$store_id,
            'desk_id'		=>	$desk_id,
            'goods_list'	=>	$cart,
            'count'			=>	$count,
            'price'			=>	$cartPrice,
            'need_pay'      =>	$payPrice,
            'enoughdeduct'	=>	$enoughDeduct,
            'use_coupon'	=>	intval($_GPC['use_coupon']),
            'coupon_price'	=>	$couponPrice,
            'status'		=>	'0',
            'remark'		=>	trim($_GPC['remark']),
            'createtime'	=>	TIMESTAMP,
            'is_prompt'		=>	'0',
            'pbox_fee'		=>	trim($_GPC['order_type']) == 'takeout' ? $pboxFee : 0,
            'order_type'	=>	trim($_GPC['order_type']) == 'takeout' ? '2' : '1',
            'pay_type'		=>	$pay_type,//1微信，2余额
            'send_fee'		=>	trim($_GPC['order_type']) == 'takeout' ? $sendFee : 0,
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
        if ($orderId) {
            if ($pay_type == '2') {//余额
                //减少余额
                $member = m("member")->getMemberInfo($_W['openid']);
                if($member == false){
                    show_json(0, "未获取到用户信息，余额扣除失败");
                }
                load()->model('mc');
                $reduceResult = m("member")->credit_update($member['id'], "credit2", -$payPrice, array($_W['fans']['uid'], "消费买单"));
                if(empty($reduceResult['errno'])){
                    //更改订单状态

                    $updateArr = array(
                        'status' => empty($storeInfo['auto_order']) ? '1' : '2',
                        'paytime' => TIMESTAMP,
                        'receivetime' => TIMESTAMP,
                        'pay_price'		=>	$payPrice
                    );
                    pdo_update('deamx_food_order', $updateArr, array(
                        'id' => $orderId
                    ));
                    //增加销量
                    m("order")->setGoodsStockAndSaleVolume($orderId);
                    m("order")->dealOrder($orderId);
                    //积分赠送
                    $sendCredit1 = com('sale')->getCredit1($member['id'],$payPrice,'1','1');

                    show_json(1,array('order_id'=>$orderId));
                }else{
                    show_json(0,'余额扣除失败，请重新尝试或更换支付方式。');
                }
            } elseif ($pay_type == '1') {//微信
                /*创建微信订单开始*/
                $orderPrice = $payPrice * 100;
                $unifiedOrder_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
                $input['appid'] = $_W['account']['key'];
                $input['mch_id'] = $paySetting['payment']['wechat']['mchid'];
                $input['nonce_str'] = random(32);
                $input['body'] = $storeInfo['name'].'-小程序点餐';
                $input['out_trade_no'] = $orderNo;
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

                if($paySetting['payment']['wechat_facilitator']['status'] == '1' && $storeInfo['payment']['status'] == '1'){
                    $input['sub_mch_id'] = $storeInfo['payment']['wechat']['mchid'];
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
                $orderInfo = simplexml_load_string(curl_post($unifiedOrder_url,arrayToXml($input)),'SimpleXMLElement', LIBXML_NOCDATA);
                $orderInfo = json_decode(json_encode($orderInfo),true);
                //print_r($orderinfo);
                if($orderInfo['return_code'] == 'FAIL'){
                    show_json(0,array('message' => $orderInfo['return_msg']));
                }
                if($orderInfo['result_code'] == 'FAIL'){
                    //
                    pdo_update("deamx_food_order",array('orderno'=>"DM".sprintf("%03d", $_W['uniacid']).date("YmdHis").mt_rand(1000,9999)),array('uniacid'=>$_W['uniacid'], 'id' => $orderId));
                    show_json(0,array('message'=>"订单状态异常！请重新发起支付！"));
                }

                $wOpt['appId'] = $orderInfo['appid'];
                if($paySetting['payment']['wechat_facilitator']['status'] == '1' && ($paySetting['payment']['wechat_facilitator']['main_type'] == '1' || $storeInfo['payment']['status'] == '1')){
                    $wOpt['appId'] = $orderInfo['sub_appid'];
                }
                $wOpt['timeStamp'] = TIMESTAMP;
                $wOpt['nonceStr'] = random(32);
                $wOpt['package'] = 'prepay_id='.$orderInfo['prepay_id'];
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
        } else {
            show_json(0,array('message'=>"创建订单失败，请重试！"));
        }
        print_r($_GPC);
    }
}
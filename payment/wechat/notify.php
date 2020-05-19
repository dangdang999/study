<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
define('IN_MOBILE', true);
require_once '../../../../framework/bootstrap.inc.php';
require_once '../../../../addons/deam_food/common/defines.php';
require_once '../../../../addons/deam_food/common/functions.php';

$input = file_get_contents('php://input');
$isxml = true;
if (!empty($input) && empty($_GET['out_trade_no'])) {
	$obj = isimplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
	$res = $data = json_decode(json_encode($obj), true);
	if (empty($data)) {
		$result = array(
			'return_code' => 'FAIL',
			'return_msg' => ''
		);
		echo array2xml($result);
		exit;
	}
	if ($data['result_code'] != 'SUCCESS' || $data['return_code'] != 'SUCCESS') {
		$result = array(
			'return_code' => 'FAIL',
			'return_msg' => empty($data['return_msg']) ? $data['err_code_des'] : $data['return_msg']
		);
		echo array2xml($result);
		exit;
	}
	$get = $data;
} else {
	$isxml = false;
	$get = $_GET;
}
load()->web('common');
load()->model('mc');
load()->func('communication');
$uniacid = $_W['uniacid'] = $_W['weid'] = intval($get['attach']);
load()->func('logging');
$_W['uniaccount'] = $_W['account'] = uni_fetch($_W['uniacid']);
$_W['acid'] = $_W['uniaccount']['acid'];
$paySetting = uni_setting($_W['uniacid'], array('payment'));
if($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS' ){
	$logno = trim($res['out_trade_no']);
	if (empty($logno)) {
		exit;
	}
	$log = pdo_fetch('SELECT * FROM ' . tablename('deamx_food_order') . ' WHERE `uniacid`=:uniacid and `ordersn`=:ordersn limit 1', array(
		':uniacid' => $_W['uniacid'],
		':ordersn' => $logno
	));
	$orderid = $log['id'];
	if(!empty($log) && $log['status']==0){
		$params['appid'] = $res['appid'];
		$params['mch_id'] = $res['mch_id'];
		$params['out_trade_no'] = $res['out_trade_no'];
		$wechat['apikey'] = $paySetting['payment']['wechat']['signkey'];
        if($paySetting['payment']['wechat_facilitator']['status'] == '1' && ($log['wechat_paytype'] == '1' || $log['wechat_paytype'] == '2')){
            $params['sub_mch_id'] = $res['sub_mch_id'];
            $wechat['apikey'] = $paySetting['payment']['wechat_facilitator']['signkey'];
        }
		$orderQueryInfo = orderquery($params,$wechat,$type);

		if($orderQueryInfo['return_code'] == 'SUCCESS' && $orderQueryInfo['result_code'] == 'SUCCESS' && $orderQueryInfo['trade_state'] == 'SUCCESS'){
			//获取门店信息
			$store_id = $log['store_id'];
			$storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$store_id));
			$payMoney = $orderQueryInfo['total_fee'] / 100;
			$updateArr = array(
				'status' => empty($storeInfo['auto_order']) ? '1' : '2',
				'paytime' => TIMESTAMP,
				'receivetime' => TIMESTAMP,
				'pay_price' => $payMoney
			);
            com('message')->saveFormId($log['prepay_id'], $log['openid'], '3');
			//判断是否使用免充值代金券
			if(!empty($orderQueryInfo['settlement_total_fee'])){
				$updateArr['pay_price'] = $orderQueryInfo['settlement_total_fee'] / 100;
				$updateArr['coupon_type'] = '1';
				$updateArr['coupon_price'] = $orderQueryInfo['coupon_fee'] / 100;
			}
			$updateResult = pdo_update('deamx_food_order', $updateArr, array(
				'id' => $orderid
			));
			//增加销量&&
			$goodsArr = json_decode($log['goods_list'],true);
			
			m("order")->setGoodsStockAndSaleVolume($orderid);
			m("order")->dealOrder($orderid);
			//积分赠送
			$sendCredit1 = com('sale')->getCredit1($log['member_id'],$payMoney,'21','1');
			
			if($sendCredit1 > 0){
				//微信会员卡预留
			}
			$result = array(
				'return_code' => 'SUCCESS',
				'return_msg' => 'OK'
			);
			echo array2xml($result);
			exit;
		}
	}else{
		//订单已经处理过了
		$result = array(
			'return_code' => 'SUCCESS',
			'return_msg' => 'OK'
		);
		echo array2xml($result);
		exit;
	}
}
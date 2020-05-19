<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
define('IN_MOBILE', true);
require '../../../../framework/bootstrap.inc.php';
require '../../../../addons/deam_food/common/defines.php';
require '../../../../addons/deam_food/common/functions.php';

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
	$log = pdo_fetch('SELECT * FROM ' . tablename('deamx_food_recharge_log') . ' WHERE `uniacid`=:uniacid and `ordersn`=:ordersn limit 1', array(
		':uniacid' => $_W['uniacid'],
		':ordersn' => $logno
	));
	$orderid = $log['id'];
	if(!empty($log) && $log['status']==0){
		$params['appid'] = $res['appid'];
		$params['mch_id'] = $res['mch_id'];
		$params['out_trade_no'] = $res['out_trade_no'];
		$wechat['apikey'] = $paySetting['payment']['wechat']['signkey'];
        if($paySetting['payment']['wechat_facilitator']['status'] == '1' && !empty($res['sub_mch_id'])){
            $params['sub_mch_id'] = $res['sub_mch_id'];
            $wechat['apikey'] = $paySetting['payment']['wechat_facilitator']['signkey'];
        }
		$orderQueryInfo = orderquery($params,$wechat,$type);
		if($orderQueryInfo['return_code'] == 'SUCCESS' && $orderQueryInfo['result_code'] == 'SUCCESS' && $orderQueryInfo['trade_state'] == 'SUCCESS'){
			//获取门店信息
			$payMoney = $orderQueryInfo['total_fee'] / 100;
			$updateArr = array(
				'status' => '1',
				'paytime' => TIMESTAMP,
				'price' => $payMoney
			);
			pdo_update("deamx_food_recharge_log",$updateArr,array('id'=>$orderid));
            com('message')->saveFormId($log['prepay_id'], $log['openid'], '3');
			//增加余额
			load()->model('mc');
			$rechargeResult = m('member')->credit_update($log['uid'], "credit2", $payMoney, array($log['uid'], "线上充值"));

			if(empty($rechargeResult['errno'])){
				//充值成功
				//积分赠送
				$sendCredit1 = com('sale')->getCredit1($log['uid'],$payMoney,'21','2');

				//判断是否有充值优惠
				com_run("sale::setRechargeActivity", $log);
				
	        	

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
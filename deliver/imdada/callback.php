<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
define('IN_MOBILE', true);
require '../../../../framework/bootstrap.inc.php';
require '../../../../addons/deam_food/common/defines.php';
require '../../../../addons/deam_food/common/functions.php';
load()->func('logging');
$input = file_get_contents('php://input');
$isxml = true;
if(empty($input)){
	exit("fail");
}
$res = json_decode($input,true);
$insertArr = array(
	'order_id'		=>	$res['order_id'],
	'remark'		=>	$input,
	'status'		=>	$res['order_status'],
	'updatetime'	=>	$res['update_time'],
);
pdo_insert("deamx_food_deliver_log",$insertArr);
$orderinfo = pdo_get("deamx_food_order",array('ordersn'=>$res['order_id']));
load()->web('common');
load()->model('mc');
load()->func('communication');
$_W['uniacid'] = $_W['weid'] = intval($orderinfo['uniacid']);
load()->func('logging');
$_W['uniaccount'] = $_W['account'] = uni_fetch($_W['uniacid']);
$_W['acid'] = $_W['uniaccount']['acid'];
$paySetting = uni_setting($_W['uniacid'], array('payment'));
$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']), array('template_status', 'template_id','takeout_template_id'));
$storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$orderinfo['store_id']));
$orderid = $orderinfo['id'];
if($res['order_status'] == '3'){
	//配送员已取货
	//更新订单状态&&发送模板消息
	if ($orderinfo['status'] == '2') {
		pdo_update("deamx_food_order",array('status'=>'4'),array('uniacid'=>$_W['uniacid'],'id'=>$orderid));
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
	}
}elseif($res['order_status'] == '4'){
	//配送完成
	if ($orderinfo['status'] == '4') {
		pdo_update("deamx_food_order",array('status'=>'3'),array('uniacid'=>$_W['uniacid'],'id'=>$orderid));
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
	}
}elseif($res['order_status'] == '5'){
	//配送订单取消
	pdo_update("deamx_food_order",array('deliver_type'=>'-1','deliver_dada_failreason'=>$res['cancel_reason']."|".$res['cancel_from']),array('ordersn'=>$res['order_id']));
	
}
exit("SUCCESS");
?>
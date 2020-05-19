<?php
defined('IN_IA') or exit('Access Denied');
$operation = empty($operation) ? 'display' : $operation;
$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']));
load()->model('mc');
load()->classs('coupon');
$code_types = array(COUPON_CODE_TYPE_TEXT => 'CODE_TYPE_TEXT', COUPON_CODE_TYPE_QRCODE => 'CODE_TYPE_QRCODE',COUPON_CODE_TYPE_BARCODE => 'CODE_TYPE_BARCODE');
$card_type = "general_coupon";
if($operation == 'display'){
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = '';
	$condition .= " AND coupon_uniacid=".$settings['coupon_uniacid'];
	$condition .= " AND from_storeid='".$store_id."'";
	$list = pdo_fetchall("SELECT * FROM " . tablename('deamx_food_coupon') . " WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY displayorder DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	foreach($list as $key=>&$row) {
		$row['date_info'] = iunserializer($row['date_info']);
		if ($row['date_info']['time_type'] == 1) {
			$row['date_info'] = $row['date_info']['time_limit_start'].'-'. $row['date_info']['time_limit_end'];
		} elseif($row['date_info']['time_type'] == 2) {
			if($row['date_info']['deadline'] > '0'){
				$day = $row['date_info']['deadline']."天后";
			}else{
				$day = "当天起";
			}
			$row['date_info'] = '领取'.$day.'，有效'.$row['date_info']['limit'].'天';
		}
	}
	if (checksubmit()) {
		if (!empty($_GPC['sortid'])) {
	        foreach ($_GPC['sortid'] as $id => $displayorder) {
	            pdo_update('deamx_food_coupon', array(
	                'displayorder' => $displayorder
	            ), array(
	                'id' => $id
	            ));
	        }
	        itoast('排序更新成功！', "", 'success');
	    }
	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('deamx_food_coupon') . " WHERE uniacid = '{$_W['uniacid']}' $condition");
	$pager = pagination($total, $pindex, $psize);
}elseif($operation == 'add'){
	if(empty($settings['coupon_uniacid'])){
		itoast("请先到总后台绑定公众号后再来添加/编辑卡券！",manage_url(array('r'=>'sales','ac'=>'index','op'=>'display')),'info');
	}
	//获取商品列表
	$goodsList = pdo_getall("deamx_food_goods", array('uniacid' => $_W['uniacid'], 'store_id' => $store_id));
}elseif($operation == 'post_data'){
	$coupon_api = new coupon($settings['coupon_uniacid']);
	$coupon = array();
	if (intval($_GPC['time_type']) == 1) {
		$starttime = strtotime($_GPC['time_limit']['start']);
		if($card_type == 'cash'){
			if($starttime<=TIMESTAMP){
				$starttime = TIMESTAMP+60;
			}
		}
		$date_info = array(
			'type' => 'DATE_TYPE_FIX_TIME_RANGE',
			'begin_timestamp' => $starttime,
			'end_timestamp' => strtotime($_GPC['time_limit']['end'])
		);
	} else {
		$date_info = array(
			'type' => 'DATE_TYPE_FIX_TERM',
			'fixed_term' => $_GPC['limit'],
			'fixed_begin_term' => $_GPC['deadline'],
		);
	}
	$carddata = array(
		'base_info' 			=> array(
			'logo_url'			=>	urlencode(tomedia($_GPC['logo_url'])),
			'brand_name'		=>	trim($_GPC['brand_name']),
			'title'				=>	substr(trim($_GPC['title']), 0,27),
			'color'				=>	empty($_GPC['color']) ? 'Color082' : $_GPC['color'],
			'notice'			=>	trim($_GPC['notice']),
			'description'		=>	trim($_GPC['description']),
			'sku'				=>	array('quantity'=>intval($_GPC['quantity'])),
			'date_info'			=>	$date_info,
			'get_limit'			=>	intval($_GPC['get_limit']),
			'can_share'			=>	intval($_GPC['can_share']) ? true : false,
			'can_give_friend'	=>	intval($_GPC['can_give_friend']) ? true : false,
			'use_custom_code'	=>	false,//自定义code
			'bind_openid'		=>	false,//指定openid
			'code_type'			=>	$code_types[$_GPC['code_type']],
		),
		'default_detail'		=>	trim($_GPC['default_detail'])
	);
	$carddata['advanced_info']['use_condition'] = array(
		'can_use_with_other_discount'	=>	intval($_GPC['can_use_with_other_discount']) ? true : false
	);
	$coupon = array(
		'card'	=>	array(
			'card_type'			=>	strtoupper($card_type),
			$card_type	=>	$carddata
		)
	);
	$status = $coupon_api->CreateCard($coupon);
	if(is_error($status)) {
		show_json(0, $status['message']);
		exit();
	}
	$coupon_card_id = $status['card_id'];
	$coupon_source = 2;
	//获取卡券状态
	load()->func('communication');
	load()->classs('wxapp.account');
	$accObj= WxappAccount::create($settings['coupon_uniacid']);
	$wxapp_access_token = $accObj->getAccessToken();
	$getStatus_url = "https://api.weixin.qq.com/card/get?access_token=".$wxapp_access_token;
	$postArr = array(
		'card_id'	=>	$coupon_card_id
	);
	$postJson = json_encode_ex($postArr);
	$getStatusResult = ihttp_post($getStatus_url,$postJson);
	$cardInfo = json_decode($getStatusResult['content'],true);
	$cardStatus = $cardInfo['card']['general_coupon']['base_info']['status'];
	if($cardStatus == "CARD_STATUS_NOT_VERIFY"){
		$coupon_status = 1;
	}elseif($cardStatus == "CARD_STATUS_VERIFY_FAIL"){
		$coupon_status = 2;
	}elseif($cardStatus == "CARD_STATUS_VERIFY_OK"){
		$coupon_status = 3;
	}elseif($cardStatus == "CARD_STATUS_DELETE"){
		$coupon_status = 4;
	}elseif($cardStatus == "CARD_STATUS_DISPATCH"){
		$coupon_status = 5;
	}else{
		$coupon_status = 0;
	}
	$cardinsert = array(
		'card_id' => $coupon_card_id,
		'type' => ($card_type == 'cash') ? '2' : '5',//2:代金券，5:优惠券
		'logo_url' => tomedia($_GPC['logo_url']),
		'code_type' => $_GPC['code_type'],
		'brand_name' => trim($_GPC['brand_name']),
		'title' => substr(trim($_GPC['title']), 0,27),
		'sub_title' => "",
		'color' => empty($_GPC['color']) ? 'Color082' : $_GPC['color'],
		'notice' => trim($_GPC['notice']),
		'description' => trim($_GPC['description']),
		'quantity' => intval($_GPC['quantity']),
		'use_custom_code' => 0,
		'bind_openid' => 0,
		'can_share' => intval($_GPC['can_share']),
		'can_give_friend' => intval($_GPC['can_give_friend']),
		'get_limit' => intval($_GPC['get_limit']),
		'status' => $coupon_status,
		'is_display' => '1',
		'is_selfconsume' => '0',
		'source' => 2,//1系统2微信
		'can_use_with_other_discount' => intval($_GPC['can_use_with_other_discount']),
		'reduce_cost' => intval($_GPC['reduce_cost']*100),
		'least_cost' => intval($_GPC['least_cost']*100)
	);
	$cardinsert['date_info'] = array(
		'time_type' => $date_info['type'] == 'DATE_TYPE_FIX_TIME_RANGE' ? 1 : 2,
		'time_limit_start' => date('Y.m.d', $date_info['begin_timestamp']),
		'time_limit_end' => date('Y.m.d', $date_info['end_timestamp']),
		'deadline' => $date_info['fixed_begin_term'],
		'limit' => $date_info['fixed_term'],
	);
	$cardinsert['date_info'] = iserializer($cardinsert['date_info']);
	$cardinsert['extra'] = iserializer(array('default_detail'=>trim($_GPC['default_detail'])));

	$cardinsert['uniacid'] = $_W['uniacid'];
	$cardinsert['coupon_uniacid'] = $settings['coupon_uniacid'];
	$cardinsert['createtime'] = TIMESTAMP;

	$cardinsert['limitdiscounttype'] = intval($_GPC['limitdiscounttype']);
	$cardinsert['limitgoodstype'] = intval($_GPC['limitgoodstype']);
	$cardinsert['store'] = iserializer($_GPC['store']);
	$cardinsert['goods'] = iserializer($_GPC['goods']);
	$cardinsert['gettype'] = intval($_GPC['gettype']);
	$cardinsert['day_send'] = intval($_GPC['day_send']);
	//
	$cardinsert['coupon_type'] = intval($_GPC['coupon_type']);
	$cardinsert['discount'] = intval($_GPC['discount']*100);
	$cardinsert['from_storeid'] = $store_id;

	//
	$card_exists = pdo_get('deamx_food_coupon', array('card_id' => $coupon_card_id), array('id'));

	if(empty($card_exists)) {
		pdo_insert('deamx_food_coupon', $cardinsert);
		$cardid = pdo_insertid();
	} else {
		$cardid = $card_exists['id'];
		pdo_update('deamx_food_coupon', $cardinsert, array('id' => $cardid));
	}
	show_json(1,"卡券创建成功！");
	//itoast("卡券创建成功！",$this->createWebUrl('coupon',array('op'=>'display','version_id'=>intval($_GPC['version_id']))),"success");
}elseif($operation == 'detail'){
	$id = intval($_GPC['id']);
	$coupon_info = pdo_get("deamx_food_coupon",array('uniacid'=>$_W['uniacid'],'id'=>$id));
	if(empty($coupon_info)) {
		itoast('抱歉，卡券不存在或是已经被删除！',$this->createWebUrl('coupon',array('op'=>'display','version_id'=>intval($_GPC['version_id']))),"error");
	}
	$coupon_info['store'] = iunserializer($coupon_info['store']);
	$coupon_info['goods'] = iunserializer($coupon_info['goods']);
	//获取商品列表
	$goodsList = pdo_getall("deamx_food_goods", array('uniacid' => $_W['uniacid'], 'store_id' => $store_id));
	
	if(checksubmit()){
		$updateArr = array(
			'limitdiscounttype'			=>	intval($_GPC['limitdiscounttype']),
			'limitgoodstype'			=>	intval($_GPC['limitgoodstype']),
			'store'						=>	iserializer($_GPC['store']),
			'goods'						=>	iserializer($_GPC['goods']),
			'gettype'					=>	intval($_GPC['gettype']),
			'day_send'					=>	intval($_GPC['day_send']),
		);
		pdo_update("deamx_food_coupon",$updateArr,array('id'=>$coupon_info['id']));
		itoast("更新成功","","success");
	}
	$coupon_info['date_info'] = iunserializer($coupon_info['date_info']);
	if ($coupon_info['date_info']['time_type'] == 1) {
		$coupon_info['date_info'] = $coupon_info['date_info']['time_limit_start'].'-'. $coupon_info['date_info']['time_limit_end'];
	} elseif($coupon_info['date_info']['time_type'] == 2) {
		if($coupon_info['date_info']['deadline'] > '0'){
			$day = $coupon_info['date_info']['deadline']."天后";
		}else{
			$day = "当天起";
		}
		$coupon_info['date_info'] = '领取'.$day.'，有效'.$coupon_info['date_info']['limit'].'天';
	}
	$coupon_info['extra'] = iunserializer($coupon_info['extra']);
	$coupon_colors = activity_get_coupon_colors();
}elseif($operation == 'log'){
	$id = intval($_GPC['couponid']);
	$coupon_info = pdo_get("deamx_food_coupon",array('uniacid'=>$_W['uniacid'],'id'=>$id));
	if(empty($coupon_info)) {
		itoast('抱歉，卡券不存在或是已经被删除！',$this->createWebUrl('coupon',array('op'=>'display','version_id'=>intval($_GPC['version_id']))),"error");
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' AND couponid='.$id;
	$list = pdo_fetchall("SELECT * FROM " . tablename('deamx_food_coupon_record') . " WHERE uniacid = '{$coupon_info['coupon_uniacid']}' $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	
	
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('deamx_food_coupon_record') . " WHERE uniacid = '{$coupon_info['coupon_uniacid']}' $condition");
	$pager = pagination($total, $pindex, $psize);
}elseif($operation == 'log_delete'){
	$recid = intval($_GPC['id']);
	$record = pdo_get('deamx_food_coupon_record', array('uniacid' => $settings['coupon_uniacid'], 'id' => $recid));
	if (empty($record)) {
		message(error(-1, '没有要删除的记录'), '', 'ajax');
	}

	$coupon_api = new coupon($record['uniacid']);
	$status = $coupon_api->UnavailableCode(array('code' => $record['code']));
	if (is_error($status)) {
		message(error(-1, $status['message']), referer(), 'ajax');
	}
	$status = pdo_delete('deamx_food_coupon_record', array('uniacid' => $settings['coupon_uniacid'], 'id' => $recid));
	if (!empty($status)) {
		message(error(0, '删除成功'), referer(), 'ajax');
	}
}elseif($operation == 'delete'){
	$coupon_api = new coupon($settings['coupon_uniacid']);
	$id = intval($_GPC['id']);
	$coupon_info = pdo_get('deamx_food_coupon', array('uniacid' => $_W['uniacid'], 'id' => $id));
	if (empty($coupon_info)) {
		itoast('抱歉，卡券不存在或是已经被删除！',"",'error');
	}
	$status = $coupon_api->DeleteCard($coupon_info['card_id']);
	if(is_error($status)) {
		itoast('删除卡券失败，错误为' . $status['message'], '', 'error');
	}
	pdo_delete('deamx_food_coupon', array('uniacid' => $_W['uniacid'], 'id' => $id));
	
	itoast('卡券删除成功！', referer(), 'success');
}
include manage_template('sales/index');
?>
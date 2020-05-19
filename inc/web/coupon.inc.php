<?php
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$operation = empty($_GPC['op']) ? 'rules' : trim($_GPC['op']);
$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']));
$uniacid = $_W['uniacid'];
load()->model('mc');
load()->classs('coupon');

$code_types = array(COUPON_CODE_TYPE_TEXT => 'CODE_TYPE_TEXT', COUPON_CODE_TYPE_QRCODE => 'CODE_TYPE_QRCODE',COUPON_CODE_TYPE_BARCODE => 'CODE_TYPE_BARCODE');
if($operation == 'bind_rules'){

}elseif($operation == 'upgrade_api_qrcode'){
	//
	load()->func('communication');
	load()->classs('wxapp.account');
	$accObj= WxappAccount::create($_W['account']['acid']);
	$access_token = $accObj->getAccessToken();

	$wxacode_dir = MODULE_ROOT."/data/wxacode/{$_W['uniacid']}";
	if (!file_exists($wxacode_dir)){
	    @mkdir($wxacode_dir,0777,true);
	}
	$qrcode_image_dir = $wxacode_dir."/sandbox.jpg";
	if (!file_exists($qrcode_image_dir)){
	    //生成文件
	    $wxacodeUrl = "https://api.weixin.qq.com/wxa/getwxacode?access_token=".$access_token;
	    $path = "deam_food/pages/sandbox/wechat";
	    $postArr = array(
			'path'			=>	$path,
			'width'			=>	800,
			'auto_color'	=>	true
		);
		$postJson = json_encode_ex($postArr);
		$wxacodeResult = ihttp_post($wxacodeUrl,$postJson);
		$wxacode = $wxacodeResult['content'];
		$wxacodeInfo = json_decode($wxacode,true);
		if(empty($wxacodeInfo['errcode'])){
			@file_put_contents($qrcode_image_dir, $wxacode);
			$qrcode_image_url['url'] = $_W['siteroot']."addons/deam_food/data/wxacode/{$_W['uniacid']}/sandbox.jpg";
			message(error('0', array('coupon' => $qrcode_image_url, 'record' => $qrcode_list, 'total' => $total)), '', 'ajax');
		}else{
			message(error('1', '生成二维码失败，' . $wxacodeInfo['errcode']), '', 'ajax');
		}
	}else{
		$qrcode_image_url['url'] = $_W['siteroot']."addons/deam_food/data/wxacode/{$_W['uniacid']}/sandbox.jpg";
		message(error('0', array('coupon' => $qrcode_image_url, 'record' => $qrcode_list, 'total' => $total)), '', 'ajax');
	}
	
	
}elseif($operation == 'rules'){
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = '';
	$list = pdo_fetchall("SELECT * FROM " . tablename('deamx_food_coupon_rules') . " WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('deamx_food_coupon_rules') . " WHERE uniacid = '{$_W['uniacid']}' $condition");
	$pager = pagination($total, $pindex, $psize);
}elseif($operation == 'rules_post'){
	$id = intval($_GPC['id']);
	if(!empty($id)){
		$rules_info = pdo_get('deamx_food_coupon_rules',array('id'=>$id,'uniacid'=>$_W['uniacid']));
		$rules_info['coupon_info'] = iunserializer($rules_info['coupon_info']);
		foreach ($rules_info['coupon_info'] as $key => &$value) {
			$coupon_info = pdo_get("deamx_food_coupon",array('uniacid'=>$_W['uniacid'],'id'=>$key));
			$count = $value;
			$value = array();
			$value['id'] = $key;
			$value['title'] = $coupon_info['title'];
			$value['least_cost'] = $coupon_info['least_cost'];
			$value['reduce_cost'] = $coupon_info['reduce_cost'];
			$value['count'] = $count;
			//
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
			$value['date_info'] = $coupon_info['date_info'];
		}
	}
	if(checksubmit()){
		//deamx_food_coupon_rules
		$postArr = array(
			'uniacid'	=>	$_W['uniacid'],
			'title'		=>	trim($_GPC['title']),
			'starttime'		=>	strtotime($_GPC['act_time']['start']),
			'endtime'		=>	strtotime($_GPC['act_time']['end']),
			'reduce_cost'	=>	trim($_GPC['reduce_cost']),
			'repeat_send'	=>	intval($_GPC['repeat_send']),
			'limit_send'	=>	intval($_GPC['limit_send']),
			'createtime'	=>	TIMESTAMP,
			'coupon_info'	=>	iserializer($_GPC['coupon_count']),
			'status'		=>	'0',
		);
		if(empty($id)){
			pdo_insert("deamx_food_coupon_rules",$postArr);
		}else{
			unset($postArr['createtime']);
			pdo_update("deamx_food_coupon_rules",$postArr,array('id'=>$id));

		}
		itoast("规则更新成功！",$this->createWebUrl('coupon',array('op'=>'rules','version_id'=>intval($_GPC['version_id']))),"success");
	}
	$list = pdo_fetchall("SELECT * FROM " . tablename('deamx_food_coupon') . " WHERE uniacid = '{$_W['uniacid']}' AND type='5' ORDER BY id DESC");
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
}elseif($operation == 'rules_status'){
	$id = intval($_GPC['id']);
	$status = intval($_GPC['status']);
	if($status == '1'){
		//暂停所有的规则
		pdo_update("deamx_food_coupon_rules",array('status'=>'0'),array('uniacid'=>$_W['uniacid']));
		//启用当前规则
		pdo_update("deamx_food_coupon_rules",array('status'=>'1'),array('uniacid'=>$_W['uniacid']));
		itoast("启用成功",$this->createWebUrl('coupon',array('op'=>'rules','version_id'=>intval($_GPC['version_id']))),"success");
	}else{
		//暂停当前规则
		pdo_update("deamx_food_coupon_rules",array('status'=>'0'),array('uniacid'=>$_W['uniacid']));
		itoast("规则已暂停",$this->createWebUrl('coupon',array('op'=>'rules','version_id'=>intval($_GPC['version_id']))),"success");
	}
}elseif($operation == 'display'){
	if(empty($settings['coupon_uniacid'])){
		itoast("请先绑定公众号后再来管理卡券！",$this->createWebUrl('coupon',array('op'=>'settings','version_id'=>intval($_GPC['version_id']))),'info');
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = '';
	$condition .= " AND coupon_uniacid=".$settings['coupon_uniacid'];
	$list = pdo_fetchall("SELECT * FROM " . tablename('deamx_food_coupon') . " WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
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
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('deamx_food_coupon') . " WHERE uniacid = '{$_W['uniacid']}' $condition");
	$pager = pagination($total, $pindex, $psize);
}elseif($operation == 'post'){
	$coupon_api = new coupon($settings['coupon_uniacid']);
	if(empty($settings['coupon_uniacid'])){
		itoast("请先绑定公众号后再来添加/编辑卡券！",$this->createWebUrl('coupon',array('op'=>'settings','version_id'=>intval($_GPC['version_id']))),'info');
	}
	$card_type = trim($_GPC['card_type']);
	if($card_type == 'cash' && (empty($_W['account']['original']))){
		itoast("请先设置小程序原始id",$this->createWebUrl('coupon',array('op'=>'display','version_id'=>intval($_GPC['version_id']))),'info');
	}
	if($card_type == 'cash' && (empty($_W['account']['setting']['payment']['wechat']['mchid']))){
		itoast("请先设置小程序支付参数",$this->createWebUrl('coupon',array('op'=>'display','version_id'=>intval($_GPC['version_id']))),'info');
	}
	if(checksubmit()){
		//
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
		if($card_type == 'cash'){
			$carddata['base_info']['pay_info']['swipe_card'] = array(
				'use_mid_list'		=>	array($_W['account']['setting']['payment']['wechat']['mchid']),//$_W['account']['setting']['payment']['wechat']['mchid']
				'create_mid'		=>	$_W['account']['setting']['payment']['wechat']['mchid'],//1277484201
				'is_swipe_card'		=>	true
			);
			$carddata['base_info']['center_title'] = "立即使用";
			$carddata['base_info']['center_app_brand_user_name'] = $_W['account']['original']."@app";
			$carddata['base_info']['center_app_brand_pass'] = "deam_food/pages/index/index";
			unset($carddata['default_detail']);
			unset($carddata['base_info']['use_custom_code']);
			unset($carddata['base_info']['bind_openid']);
			unset($carddata['base_info']['notice']);
			$carddata['base_info']['code_type'] = "CODE_TYPE_NONE";
			$carddata['reduce_cost'] = intval($_GPC['reduce_cost']*100);
			$carddata['least_cost'] = intval($_GPC['least_cost']*100);
		}
		$coupon = array(
			'card'	=>	array(
				'card_type'			=>	strtoupper($card_type),
				$card_type	=>	$carddata
			)
		);
		$status = $coupon_api->CreateCard($coupon);
		if(is_error($status)) {
			itoast($status['message'],"",'error');
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
		$card_exists = pdo_get('deamx_food_coupon', array('card_id' => $coupon_card_id), array('id'));

		if(empty($card_exists)) {
			pdo_insert('deamx_food_coupon', $cardinsert);
			$cardid = pdo_insertid();
		} else {
			$cardid = $card_exists['id'];
			pdo_update('deamx_food_coupon', $cardinsert, array('id' => $cardid));
		}
		itoast("卡券创建成功！",$this->createWebUrl('coupon',array('op'=>'display','version_id'=>intval($_GPC['version_id']))),"success");
	}
}elseif($operation == 'modifystock'){
	$id = intval($_GPC['id']);
	$quantity = intval($_GPC['quantity']);
	$coupon = pdo_get("deamx_food_coupon",array('uniacid'=>$_W['uniacid'],'id'=>$id));
	if(empty($coupon)) {
		message('抱歉，卡券不存在或是已经被删除！');
	}
	pdo_update('deamx_food_coupon', array('quantity' => $quantity), array('id' => $id, 'uniacid' => $_W['uniacid']));
	message(error(0, '修改库存成功'), referer(), 'ajax');
}elseif($operation == 'detail'){
	$id = intval($_GPC['id']);
	$coupon_info = pdo_get("deamx_food_coupon",array('uniacid'=>$_W['uniacid'],'id'=>$id));
	if(empty($coupon_info)) {
		itoast('抱歉，卡券不存在或是已经被删除！',$this->createWebUrl('coupon',array('op'=>'display','version_id'=>intval($_GPC['version_id']))),"error");
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
}elseif($operation == 'coupon_consume'){
	$recid = intval($_GPC['id']);
	$record = pdo_get('deamx_food_coupon_record', array('uniacid' => $settings['coupon_uniacid'], 'id' => $recid));
	if (empty($record)) {
		message(error(-1, '优惠券不存在'), '', 'ajax');
	}
	$clerk_name = trim($_W['user']['name']) ? trim($_W['user']['name']) : trim($_W['user']['username']);
	$update = array(
		'status' => 3,
		'usetime' => TIMESTAMP,
		'clerk_id' => $_W['user']['clerk_id'],
		'clerk_type' => $_W['user']['clerk_type'],
		'store_id' => $_W['user']['store_id'],
		'clerk_name' => $clerk_name,
	);
	$coupon_api = new coupon($record['uniacid']);
	$status = $coupon_api->ConsumeCode(array('code' => $record['code']));
	if (is_error($status)) {
		if (strexists($status['message'], '40127')) {
			$status['message'] = '卡券已失效';
			pdo_update('deamx_food_coupon_record', array('status' => '2'), array('uniacid' => $settings['coupon_uniacid'], 'id' => $recid));
		}
		if (strexists($status['message'], '40099')) {
			$status['message'] = '卡券已被核销';
			pdo_update('deamx_food_coupon_record', array('status' => '3'), array('uniacid' => $settings['coupon_uniacid'], 'id' => $recid));
		}
		message(error(-1, $status['message']), '', 'ajax');
	}
	$status = pdo_update('deamx_food_coupon_record', $update, array('uniacid' => $settings['coupon_uniacid'], 'id' => $recid));
	if (!empty($status)) {
		message(error(0, '核销成功'), referer(), 'ajax');
	}
}elseif($operation == 'settings'){
	//获取符合条件的公众号列表
//	$owned_account_Arr = uni_owned();
	if(!empty($settings['coupon_uniacid'])){
		$account = pdo_get("account_wechats",array('uniacid'=>$settings['coupon_uniacid']));
	}
	if(checksubmit()){
		$postArr = array(
			'uniacid'			=>	intval($_W['uniacid']),
			'coupon_uniacid'	=>	intval($_GPC['uniacid']),
		);
		if(empty($settings)){
			pdo_insert("deamx_food_settings",$postArr);
		}else{
			pdo_update("deamx_food_settings",$postArr,array('uniacid'=>$uniacid));
		}
		itoast("保存成功！","","success");
	}
}elseif ($operation == 'get_uniacid_info') {
    $uniacid = intval($_GPC['uniacid']);
    if (empty($uniacid)) {
        show_json(0, "请输入正确的uniacid");
    }
    $account = pdo_get("account_wechats",array('uniacid'=>$uniacid));
    if (empty($account)) {
        show_json(0, "未找到相应公众号，请检查uniacid是否正确！");
    }
    show_json(1, array('account' => $account));
}elseif ($operation == 'post_coupon_uniacid') {
    $postArr = array(
        'uniacid'			=>	intval($_W['uniacid']),
        'coupon_uniacid'	=>	intval($_GPC['uniacid']),
    );
    if(empty($settings)){
        pdo_insert("deamx_food_settings",$postArr);
    }else{
        pdo_update("deamx_food_settings",$postArr,array('uniacid'=>$uniacid));
    }
    show_json(1, "操作成功！");
}
elseif($operation == 'publish'){
	$couponid = intval($_GPC['cid']);
	$coupon = pdo_get('deamx_food_coupon', array('id' => $couponid));
	if(empty($coupon)) {
		return message('卡券不存在或已经删除', '', 'error');
	}
	//二维码投入场景Id,文档中是写的19位的longint型，实际测试大于14位会丢失精度
	$qrcode_sceneid = sprintf('11%012d', $couponid);
	$coupon_qrcode = pdo_get('qrcode', array('qrcid' => $qrcode_sceneid, 'type' => 'card'));
	if (empty($coupon_qrcode)) {
		$insert = array(
			'uniacid' => $settings['coupon_uniacid'],
			'acid' => $settings['coupon_uniacid'],
			'qrcid' => $qrcode_sceneid,
			'keyword' => '',
			'name' => $coupon['title'],
			'model' => 1,
			'ticket' => '',
			'expire' => '',
			'url' => '',
			'createtime' => TIMESTAMP,
			'status' => '1',
			'type' => 'card',
		);
		pdo_insert('qrcode', $insert);
		$coupon_qrcode['id'] = pdo_insertid();
	}
	$response = ihttp_request($coupon_qrcode['url']);
	$coupon_api = new coupon($settings['coupon_uniacid']);
	if ($response['code'] != '200' || empty($coupon_qrcode['url'])) {
		$coupon_qrcode_image = $coupon_api->QrCard($coupon['card_id'], $qrcode_sceneid);
		if (is_error($coupon_qrcode_image)) {
			if ($coupon_qrcode_image['errno'] == '40078') {
				pdo_update('deamx_food_coupon', array('status' => 2), array('id' => $couponid));
			}
			message(error('1', '生成二维码失败，' . $coupon_qrcode_image['message']), '', 'ajax');
		}
		$couponid = $coupon_qrcode['id'];
		unset($coupon_qrcode['id']);
	
		$coupon_qrcode['url'] = $coupon_qrcode_image['show_qrcode_url'];
		$coupon_qrcode['ticket'] = $coupon_qrcode_image['ticket'];
		$coupon_qrcode['expire'] = TIMESTAMP + $coupon_qrcode_image['expire_seconds'];
		pdo_update('qrcode', $coupon_qrcode, array('id' => $couponid));
	}
	message(error('0', array('coupon' => $coupon_qrcode, 'record' => $qrcode_list, 'total' => $total)), '', 'ajax');
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
}elseif($operation == 'delete_rules'){
	$id = intval($_GPC['id']);
	$rules_info = pdo_get('deamx_food_coupon_rules',array('id'=>$id,'uniacid'=>$_W['uniacid']));
	if (empty($rules_info)) {
		itoast('抱歉，该营销规则不存在或是已经被删除！',"",'error');
	}
	pdo_delete('deamx_food_coupon_rules', array('uniacid' => $_W['uniacid'], 'id' => $id));
	
	itoast('营销规则删除成功！', referer(), 'success');
}elseif($operation == 'recharge'){
	$data = m("common")->getPluginset("sale");
	$recharges = iunserializer($data["recharges"]);
	if(checksubmit()){
		$recharges = array( );
		$datas = (is_array($_GPC["enough"]) ? $_GPC["enough"] : array( ));
		foreach( $datas as $key => $value){
			$enough = trim($value);
			if(!empty($enough)){
				$recharges[] = array( "enough" => trim($_GPC["enough"][$key]), "give" => trim($_GPC["give"][$key]) );
			}
		}
		$data["recharges"] = iserializer($recharges);
		m("common")->updatePluginset(array( "sale" => $data ));
		
		itoast("操作成功！","","success");
	}
	
}elseif($operation == 'credit1'){
	$data = m("common")->getPluginset("sale");
	$credit1 = iunserializer($data["credit1"]);
	$enough1 = (empty($credit1["enough1"]) ? array() : $credit1["enough1"]);
	$enough2 = (empty($credit1["enough2"]) ? array() : $credit1["enough2"]);
	//print_r($credit1);
	if(checksubmit()){
		$enough1 = array( );
		$postenough1 = (is_array($_GPC["enough1_1"]) ? $_GPC["enough1_1"] : array( ));
		foreach( $postenough1 as $key => $value){
			$enough = floatval($value);
			if($enough > 0){
				$enough1[] = array( "enough1_1" => floatval($_GPC["enough1_1"][$key]), "enough1_2" => floatval($_GPC["enough1_2"][$key]), "give1" => intval($_GPC["give1"][$key]) );
			}
		}
		$data["isgoodspoint"] = intval($_GPC["isgoodspoint"]);
		$data["enough1"] = $enough1;
		$enough2 = array( );
		$postenough2 = (is_array($_GPC["enough2_1"]) ? $_GPC["enough2_1"] : array( ));
		foreach( $postenough2 as $key => $value ){
			$enough = floatval($value);
			if($enough >0){
				$enough2[] = array( "enough2_1" => floatval($_GPC["enough2_1"][$key]), "enough2_2" => floatval($_GPC["enough2_2"][$key]), "give2" => intval($_GPC["give2"][$key]) );
			}
		}
		if(!empty($enough2)) {
			m("common")->updateSysset(array( "trade" => array( "credit" => 0 ) ));
		}
		$data["enough1"] = $enough1;
		$data["enough2"] = $enough2;
		$data["paytype"] = (is_array($_GPC["paytype"]) ? $_GPC["paytype"] : array());
		m("common")->updatePluginset(array("sale" => array( "credit1" => iserializer($data))));
		
		itoast("操作成功！", "", "success");
	}
	
}

include $this->template('system/sale/coupon');
?>
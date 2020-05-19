<?php
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$operation = empty($_GPC['op']) ? 'index' : trim($_GPC['op']);
$openid = trim($_GPC['openid']);
$cardid = trim($_GPC['cardid']);
$wxcardInfo = pdo_get("deamx_food_wxcard", array('wx_openid' => $openid, 'card_id' => $cardid));
//print_r($wxcardInfo);
$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']));
if($operation == 'index'){
	if(empty($wxcardInfo)){
		exit("会员卡不存在！");
	}
}elseif($operation == 'post_data'){
	$telephone = trim($_GPC['telephone']);
	$card_code = trim($_GPC['card_code']);
	$member_cardid = trim($_GPC['member_cardid']);
	$condition = array('uniacid' => $_W['uniacid'], 'telephone' => $telephone);
	if(!empty($card_code)){
		$condition['card_code'] = $card_code;
	}
	if(!empty($member_cardid)){
		$condition['cardid'] = $member_cardid;
	}
	if(empty($card_code) && empty($member_cardid)){
		show_json(0, "会员卡号和身份证号码至少填写一项！");
	}
	$getOldMember = pdo_get("deamx_food_oldmember_data", $condition);
	if(empty($getOldMember)){
		show_json(0, "未找到会员信息！");
	}else{
		if($getOldMember['status']){
			show_json(0, "该会员信息已被绑定过了！");
		}
		if(empty($getOldMember['card_code'])){
			$getOldMember['card_code'] = $wxcardInfo['code'];
		}
		$dataArr = array(
			'init_bonus'				=>	intval($getOldMember['credit1']),
			'init_bonus_record'			=>	"旧积分同步",
			'membership_number'			=>	$getOldMember['card_code'],
			'code'						=>	$wxcardInfo['code'],
			'card_id'					=>	$cardid,
			'init_custom_field_value1'	=>	$getOldMember['credit2']
		);
		$result = com('wxcard')->wxMembercardActivate($dataArr,$settings['coupon_uniacid']);
		if (!is_wxerror($result)) {
			pdo_update("deamx_food_oldmember_data", array('status' => '1'), array('id' => $getOldMember['id']));
			//信息更新至会员表
			pdo_update("deamx_food_wxcard",array('status'=>'1','membership_number' => $getOldMember['card_code'],'realname' => $getOldMember['realname'], 'telephone' => $telephone, 'cardid' => $member_cardid),array('id'=>$wxcardInfo['id']));
			if($dataArr['init_custom_field_value1'] > '0'){
				//更新余额
				m('member')->credit_update($wxcardInfo['id'], 'credit1', $dataArr['init_custom_field_value1'], array(0, "老会员余额同步", 'user', $wxcardInfo['id'], $wxcardInfo['id'], $wxcardInfo['id']));
			}
			if($dataArr['init_bonus'] > '0'){
				//更新积分
				m('member')->credit_update($wxcardInfo['id'], 'credit2', $dataArr['init_bonus'], array(0, "老会员积分同步", 'user', $wxcardInfo['id'], $wxcardInfo['id'], $wxcardInfo['id']));
			}
			//激活会员余额&&积分同步问题
			show_json(1, "绑定成功！");
		}else{
			show_json(0, "绑定失败！");
			// print_r($wxcardInfo);
			// print_r($result);
		}
		
	}
	exit;
}
include $this->template('member/bind_card');
?>
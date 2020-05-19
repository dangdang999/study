<?php
if(!defined("IN_IA")){
	exit("Access Denied");
}
class Member_DeamFoodModel{
	function getMemberInfo($openid = ""){
		global $_W;
		$uid = intval($openid);
		if(!empty($uid)){
			$memberInfo = pdo_get("deamx_food_members", array('uniacid' => $_W['uniacid'], 'id' => $uid));
		}else{
			$memberInfo = pdo_get("deamx_food_members", array('uniacid' => $_W['uniacid'], 'openid' => $openid));
		}
		if(empty($openid)){
			return false;
		}
		if(empty($memberInfo['id'])){
			$memberInfo = array(
				'uniacid'	=>	$_W['uniacid'],
				'openid'	=>	$openid,
				'credit1'	=>	'0',
				'credit2'	=>	'0.00',
				'realname'	=>	'',
				'telephone'	=>	''
			);
			pdo_insert("deamx_food_members",$memberInfo);
			$memberInfo['id'] = pdo_insertid();
		}
		$memberInfo['credit1'] = intval($memberInfo['credit1']);
		return $memberInfo;
	}
	function credit_update($uid, $creditType, $creditVal = 0, $log = array()) {
		global $_W;
		$creditType = trim($creditType);
		
		$clerk_types = array(
			'1' => '线上操作',
			'2' => '系统后台',
			'3' => '店员',
		);
		$creditVal = floatval($creditVal);
		if (empty($creditVal)) {
			return true;
		}
		$value = pdo_getcolumn('deamx_food_members', array('id' => $uid), $creditType);
		if ($creditVal > 0 || ($value + $creditVal >= 0) ) {
			pdo_update('deamx_food_members', array($creditType => $value + $creditVal), array('id' => $uid));
		} else {
			return error('-1', "积分类型为“{$creditType}”的积分不够，无法操作。");
		}
		if (empty($log) || !is_array($log)) {
            $log = array($uid, '未记录', 0, 0);
		}
		if ($creditType == 'credit1') {

		} elseif ($creditType == 'credit2') {

		}
		if (empty($log[1])) {
			if ($creditVal > 0) {
				$log[1] = $clerk_types[$log[5]] . ': 添加' . $creditVal;
			} else {
				$log[1] = $clerk_types[$log[5]] . ': 减少' . -$creditVal;
			}

		}
		$clerk_type = intval($log[5]) ? intval($log[5]) : 1;
		$data = array(
			'uid' => $uid,
			'credittype' => $creditType,
			'uniacid' => $_W['uniacid'],
			'num' => $creditVal,
			'createtime' => TIMESTAMP,
			'operator' => intval($log[0]),
			'module' => trim($log[2]),
			'clerk_id' => intval($log[3]),
			'store_id' => intval($log[4]),
			'clerk_type' => $clerk_type,
			'remark' => $log[1],
			'real_uniacid' => $_W['uniacid']
		);
		pdo_insert('deamx_food_credits_record', $data);

		return true;
	}
	function credit_fetch($uid, $types = array()) {
		if (empty($types) || $types == '*') {
			$select = array('credit1', 'credit2');
		} else {
			$select = $types;
		}
		return pdo_get('deamx_food_members', array('id' => $uid), $select);
	}
}
<?php
defined('IN_IA') or exit('Access Denied');
$operation = empty($operation) ? 'basic' : $operation;

if(@in_array("store_store_view", $user_permissionArr) && !@in_array("store_store_edit", $user_permissionArr)){
	$readonly = true;
}
$store = pdo_get("deamx_food_store",array('uniacid'=>$uniacid,'id'=>$store_id));
$store['takeout_open_time'] = iunserializer($store['takeout_open_time']);
if($operation == 'basic'){
	if(checksubmit()){
		if(!in_array("store_store_edit", $user_permissionArr)){
			itoast('您没有相应操作权限！',"",'error');
		}
		$store = $_GPC['store'];
		$postArr = array(
			'uniacid'				=>	$uniacid,
			'name'					=>	trim($store['name']),
			'province'				=>	trim($store['province']),
			'city'					=>	trim($store['city']),
			'district'				=>	trim($store['district']),
			'district_edit_self'	=>	intval($store['district_edit_self']),
			'address'				=>	trim($store['address']),
			'longitude'				=>	trim($store['longitude']),
			'latitude'				=>	trim($store['latitude']),
			'status'				=>	intval($store['status']),
			'is_getself'			=>	intval($store['is_getself']),
			'is_takeout'			=>	intval($store['is_takeout']),
			'close_reason'			=>	trim($store['close_reason']),
			'starttime'				=>	trim($store['starttime']),
			'endtime'				=>	trim($store['endtime']),
			'bg_color'				=>	trim($store['bg_color']),
			'fg_color'				=>	trim($store['fg_color']),
			'imgs'					=>	iserializer($store['imgs']),
			'remark_text'			=>	trim($store['remark_text']),
			'notice_tel'			=>	trim($store['notice_tel']),
			'start_price'			=>	@number_format($store['start_price'],2,".",""),
			'send_limit'			=>	@number_format($store['send_limit'],2,".",""),
			'send_fee'				=>	@number_format($store['send_fee'],2,".",""),
			'auto_order'			=>	intval($store['auto_order']),
			'deliver_type'			=>	intval($store['deliver_type']),
			'deliver_dada_shopno'	=>	trim($store['deliver_dada_shopno']),
			'deliver_dada_citycode'	=>	trim($store['deliver_dada_citycode']),
            'telephone'             =>  trim($store['telephone']),
		);
        $postArr['takeout_open_time'] = iserializer($store['takeout_open_time']);
		$result = pdo_update("deamx_food_store",$postArr,array('uniacid'=>$uniacid,'id'=>$store_id));
		itoast('信息更新成功！',"",'success');
	}
}elseif($operation == 'enough'){
	if(checksubmit()){
		if(!in_array("store_store_edit", $user_permissionArr)){
			itoast('您没有相应操作权限！',"",'error');
		}
		$store = $_GPC['store'];
		if($store['enoughmoney']>0 && $store['enoughdeduct']>0){
			if($store['enoughmoney']<=$store['enoughdeduct']){
				itoast("满减设置最低消费必须大于减免金额","",'error');
			}
		}
		$postArr = array(
			'enoughmoney'			=>	@number_format($store['enoughmoney'],2,".",""),
			'enoughdeduct'			=>	@number_format($store['enoughdeduct'],2,".",""),
		);
		$result = pdo_update("deamx_food_store",$postArr,array('uniacid'=>$uniacid,'id'=>$store_id));
		itoast('信息更新成功！',"",'success');
	}
}

include manage_template("store/settings");
?>
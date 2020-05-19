<?php
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC, $top_nav;
$uniacid = $_W['uniacid'];
//print_r($_W);
$operation = empty($_GPC['op']) ? 'basic' : trim($_GPC['op']);
$deam_frames = array(
	array(
		'title'	=>	'基础设置',
		'url'	=>	$this->createWebUrl('settings',array('op'=>'basic')),
		'active'=>	$operation == 'basic' ? 'active' : '',
	),
//	array(
//		'title'	=>	'授权页面管理',
//		'url'	=>	$this->createWebUrl('settings',array('op'=>'auth')),
//		'active'=>	$operation == 'auth' ? 'active' : '',
//	),
    array(
		'title'	=>	'功能设置',
		'url'	=>	$this->createWebUrl('settings',array('op'=>'function')),
		'active'=>	$operation == 'function' ? 'active' : '',
	),
	array(
		'title'	=>	'消息推送设置',
		'url'	=>	$this->createWebUrl('settings',array('op'=>'message')),
		'active'=>	$operation == 'message' ? 'active' : '',
	),
	array(
		'title'	=>	'呼叫管理',
		'url'	=>	$this->createWebUrl('settings',array('op'=>'bell')),
		'active'=>	$operation == 'bell' ? 'active' : '',
	),
	array(
		'title'	=>	'第三方配送',
		'url'	=>	$this->createWebUrl('settings',array('op'=>'deliver')),
		'active'=>	$operation == 'deliver' ? 'active' : '',
	),
	array(
		'title'	=>	'支付管理',
		'url'	=>	$this->createWebUrl('settings',array('op'=>'payset')),
		'active'=>	$operation == 'payset' ? 'active' : '',
	),
	array(
		'title'	=>	'广告管理',
		'url'	=>	$this->createWebUrl('settings',array('op'=>'advertisement')),
		'active'=>	$operation == 'advertisement' ? 'active' : '',
	),
);
if($_W['role'] == 'founder'){
	$deam_frames[] = array(
		'title'	=>	'系统管理',
		'url'	=>	$this->createWebUrl('settings',array('op'=>'system')),
		'active'=>	$operation == 'system' ? 'active' : '',
	);
}
$settings = pdo_get("deamx_food_settings",array('uniacid'=>$uniacid));
if($operation == 'basic'){
	$storeList = pdo_getall("deamx_food_store",array('uniacid'=>$uniacid));
	if(checksubmit()){
		$postArr = array(
			'uniacid'				=>	$uniacid,
			'name'					=>	trim($_GPC['name']),
			'type'					=>	intval($_GPC['type']),
			'bg_color'				=>	trim($_GPC['bg_color']),
			'fg_color'				=>	trim($_GPC['fg_color']),
			'share_title'			=>	trim($_GPC['share_title']),
			'area_limit'			=>	intval($_GPC['area_limit']),
			'tencent_map_apikey'	=>	trim($_GPC['tencent_map_apikey']),
			'single_storeid'		=>	intval($_GPC['single_storeid']),
			'store_blogo'			=>	trim($_GPC['store_blogo']),
			'about_us'				=>	htmlspecialchars_decode($_GPC['about_us']),
		);
		if(empty($settings)){
			pdo_insert("deamx_food_settings",$postArr);
		}else{
			pdo_update("deamx_food_settings",$postArr,array('uniacid'=>$uniacid));
		}
		itoast("保存成功！","","success");
	}
}elseif ($operation == 'auth') {
	$settings['auth_setting'] = iunserializer($settings['auth_setting']);
	if(checksubmit()){
		$postArr = array(
			'uniacid'				=>	$uniacid,
			'auth_setting'			=>	iserializer($_GPC['auth_setting']),
		);
		if(empty($settings)){
			pdo_insert("deamx_food_settings",$postArr);
		}else{
			pdo_update("deamx_food_settings",$postArr,array('uniacid'=>$uniacid));
		}
		itoast("保存成功！","","success");
	}
}elseif ($operation == 'function') {
	if(checksubmit()){
		$postArr = array(
			'wxapp_scan'			=>	intval($_GPC['wxapp_scan']),
			'wxapp_scan_name'		=>	empty($_GPC['wxapp_scan_name']) ? "堂食" : trim($_GPC['wxapp_scan_name']),
			'wxapp_scan_color'		=>	empty($_GPC['wxapp_scan_color']) ? "#ffa1a1" : trim($_GPC['wxapp_scan_color']),
			'wxapp_scan_intro'		=>	empty($_GPC['wxapp_scan_intro']) ? "点我扫码进入" : trim($_GPC['wxapp_scan_intro']),
			'wxapp_scan_logo'		=>	trim($_GPC['wxapp_scan_logo']),
			'wxapp_takeout'			=>	intval($_GPC['wxapp_takeout']),
			'wxapp_takeout_name'	=>	empty($_GPC['wxapp_takeout_name']) ? "外卖" : trim($_GPC['wxapp_takeout_name']),
			'wxapp_takeout_color'	=>	empty($_GPC['wxapp_takeout_color']) ? "#faa040" : trim($_GPC['wxapp_takeout_color']),
			'wxapp_takeout_intro'	=>	empty($_GPC['wxapp_takeout_intro']) ? "点餐快速配送" : trim($_GPC['wxapp_takeout_intro']),
			'wxapp_takeout_logo'	=>	trim($_GPC['wxapp_takeout_logo']),
			'wxapp_getself'			=>	intval($_GPC['wxapp_getself']),
			'wxapp_getself_name'	=>	empty($_GPC['wxapp_getself_name']) ? "自取" : trim($_GPC['wxapp_getself_name']),
			'wxapp_getself_color'	=>	empty($_GPC['wxapp_getself_color']) ? "#76bdef" : trim($_GPC['wxapp_getself_color']),
			'wxapp_getself_intro'	=>	empty($_GPC['wxapp_getself_intro']) ? "点我开始订餐" : trim($_GPC['wxapp_getself_intro']),
			'wxapp_getself_logo'	=>	trim($_GPC['wxapp_getself_logo']),
		);
		if(empty($settings)){
			pdo_insert("deamx_food_settings",$postArr);
		}else{
			pdo_update("deamx_food_settings",$postArr,array('uniacid'=>$uniacid));
		}
		itoast("保存成功！","","success");
	}
}elseif ($operation == 'message') {
	$settings['sms_params'] = json_decode($settings['sms_params'],true);

    $settings['template_id_param'] = explode(",", $settings['template_id_param']);
    $settings['get_food_template_id_param'] = explode(",", $settings['get_food_template_id_param']);
    $settings['takeout_template_id_param'] = explode(",", $settings['takeout_template_id_param']);

	if(checksubmit()){
		$postArr = array(
			'uniacid'				=>	$uniacid,
			'template_status'		=>	intval($_GPC['template_status']),
            'template_id'			=>	trim($_GPC['template_id']),
            'template_id_param'     =>  implode(",", $_GPC['template_id_param']),
            'get_food_template_id'  =>	trim($_GPC['get_food_template_id']),
            'get_food_template_id_param'     =>  implode(",", $_GPC['get_food_template_id_param']),
			'takeout_template_id'	=>	trim($_GPC['takeout_template_id']),
            'takeout_template_id_param'     =>  implode(",", $_GPC['takeout_template_id_param']),
			'sms_status'			=>	intval($_GPC['sms_status']),
			'sms_type'				=>	intval($_GPC['sms_type']),
			'sms_params'			=>	json_encode($_GPC['sms_params'])
		);
		if(empty($settings)){
			pdo_insert("deamx_food_settings",$postArr);
		}else{
			pdo_update("deamx_food_settings",$postArr,array('uniacid'=>$uniacid));
		}
		itoast("保存成功！","","success");
	}
}elseif ($operation == 'bell') {
	$settings['bell_settings'] = json_decode($settings['bell_settings'],true);
	if(empty($settings['bell_settings']['getfood_front'])){
		$settings['bell_settings']['getfood_front'] = "请";
	}
	if(empty($settings['bell_settings']['getfood_behind'])){
		$settings['bell_settings']['getfood_behind'] = "号到服务台取餐。";
	}
	if(checksubmit()){
		$postArr = array(
			'uniacid'				=>	$uniacid,
			'bell_settings'			=>	json_encode($_GPC['bell'])
		);
		if(empty($settings)){
			pdo_insert("deamx_food_settings",$postArr);
		}else{
			pdo_update("deamx_food_settings",$postArr,array('uniacid'=>$uniacid));
		}
		itoast("保存成功！","","success");
	}
}elseif($operation == 'system'){
	if($_W['role'] != 'founder'){
		itoast("无相应操作权限",$this->createWebUrl('settings'),'error');
	}
	if(checksubmit()){
		$postArr = array(
			'store_count'	=>	intval($_GPC['store_count']),
			'copyright'		=>	trim($_GPC['copyright']),
			'login_logo'	=>	trim($_GPC['login_logo']),
			'login_bg'		=>	trim($_GPC['login_bg']),
            'wxacode_color' =>  intval($_GPC['wxacode_color'])
		);
		if(empty($settings)){
			pdo_insert("deamx_food_settings",$postArr);
		}else{
			pdo_update("deamx_food_settings",$postArr,array('uniacid'=>$uniacid));
		}
		itoast("保存成功！","","success");
	}
}elseif($operation == 'payset'){
	$method = empty($_GPC['method']) ? "payment" : trim($_GPC['method']);
	$settings['sets'] = iunserializer($settings['sets']);
	if ($method == 'payment') {
        $setting = uni_setting_load('payment', $_W['uniacid']);
        $pay_setting = $setting['payment'];
        if(empty($pay_setting['wechat']['mchid'])){
            $pay_setting['wechat']['mchid'] = "";
        }
        if(empty($pay_setting['wechat']['signkey'])){
            $pay_setting['wechat']['signkey'] = "";
        }
		if(checksubmit("wechat_submit")){
			$param['account'] = $_W['acid'];
			$param['signkey'] = trim($_GPC['wechat']['signkey']);
			$param['mchid'] = trim($_GPC['wechat']['mchid']);
			$param['recharge_switch'] = 0;
			$param['pay_switch'] = 0;
			$pay_setting["wechat"] = $param;
			$payment = iserializer($pay_setting);
			uni_setting_save('payment', $payment);
			itoast("保存成功！","","success");
		}
        if(checksubmit("wechat_fa_submit")){
            $param['status'] = intval($_GPC['wechat_fa']['status']);
            $param['main_type'] = intval($_GPC['wechat_fa']['main_type']);
            $param['signkey'] = trim($_GPC['wechat_fa']['signkey']);
            $param['mchid'] = trim($_GPC['wechat_fa']['mchid']);
            $param['appid'] = trim($_GPC['wechat_fa']['appid']);

            //证书
            load()->classs('uploadedfile');
            $files = UploadedFile::createFromGlobal();
            $cert = isset($files['cert']) ? $files['cert'] : null;
            $private_key = isset($files['key']) ? $files['key'] : null;

            $cert_content = $pay_setting['wechat_facilitator']['wechat_refund']['cert'];
            $private_key_content = $pay_setting['wechat_facilitator']['wechat_refund']['key'];
            if($cert && $cert->isOk()) {
                $cert_content = $cert->getContent();
                $cert_content = authcode($cert_content, 'ENCODE');
            }

            if($private_key && $private_key->isOk()) {
                $key_content = $private_key->getContent();
                $private_key_content = authcode($key_content, 'ENCODE');
            }
            if($param['status'] != '0'){
                if(! $cert_content) {
                    itoast('为确保可正常退款，请上传apiclient_cert.pem证书', '', 'info');
                }
                if(! $private_key_content) {
                    itoast ('为确保可正常退款，请上传apiclient_key.pem证书', '', 'info');
                }
            }

            $param['wechat_refund'] = array('cert' => $cert_content,'key' => $private_key_content, 'switch' => 1, 'version' => 1);
            $pay_setting["wechat_facilitator"] = $param;
            $payment = iserializer($pay_setting);
            uni_setting_save('payment', $payment);
            itoast("保存成功！","","success");
        }
		
	}elseif($method == 'post_balance'){
		$settings['sets']['open_banlance'] = empty($settings['sets']['open_banlance']) ? '1' : '0';
		$sets = iserializer($settings['sets']);
		pdo_update('deamx_food_settings', array('sets' => $sets), array('id' => $settings['id']));
		if($settings['sets']['open_banlance'] == '1'){
			show_json(1,"开启成功");
		}else{
			show_json(1,"关闭成功");
		}
		
	}
	
}elseif($operation == 'advertisement'){
	$sysset = m('common')->getSysset();
	$advertisement = $sysset['advertisement'];
	if(checksubmit()){
		$advertisement = array();
		$advertisement = $_GPC['ad'];
		$advertisement['index_bottom_0']['status'] = intval($advertisement['index_bottom_0']['status']);
		$advertisement['index_bottom_1']['status'] = intval($advertisement['index_bottom_1']['status']);
		m('common')->updateSysset(array('advertisement' => $advertisement));
		itoast("保存成功！","","success");
	}

}elseif($operation == 'deliver'){
	$method = empty($_GPC['method']) ? "dada" : trim($_GPC['method']);
	if ($method == 'dada') {
		if(checksubmit()){
			$postArr = array(
				'deliver_dada_status'		=>	intval($_GPC['deliver_dada_status']),
				'deliver_dada_app_key'		=>	trim($_GPC['deliver_dada_app_key']),
				'deliver_dada_app_secret'	=>	trim($_GPC['deliver_dada_app_secret']),
				'deliver_dada_shopid'		=>	trim($_GPC['deliver_dada_shopid']),
			);
			if(empty($settings)){
				pdo_insert("deamx_food_settings",$postArr);
			}else{
				pdo_update("deamx_food_settings",$postArr,array('uniacid'=>$uniacid));
			}
			itoast("保存成功！","","success");
		}
	}
}elseif($operation == 'clear_qrcode'){
    pdo_update("deamx_food_store",array('wxacode' => ''),array('uniacid'=>$uniacid));
    pdo_update("ims_deamx_food_desknumber",array('wxacode' => ''),array('uniacid'=>$uniacid));

    show_json(1);
}elseif ($operation == 'update_show_manage') {
    $status = $_GPC['status'] == 'true' ? 1 : 0;
    m('common')->updateSysset(array('wxapp' => array('show_manage' => $status)));
    show_json(1);
}
include $this->template('system/sysset/settings');
?>
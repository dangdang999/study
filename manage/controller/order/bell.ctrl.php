<?php
defined('IN_IA') or exit('Access Denied');
$operation = empty($operation) ? 'get_food' : $operation;
if($operation == 'get_food'){
	$orderId = intval($_GPC['orderid']);
	$orderInfo = pdo_get("deamx_food_order",array('uniacid'=>$_W['uniacid'],'id'=>$orderId));
	$settings['bell_settings'] = json_decode($settings['bell_settings'],true);
	if(empty($settings['bell_settings']['getfood_front'])){
		$settings['bell_settings']['getfood_front'] = "请";
	}
	if(empty($settings['bell_settings']['getfood_behind'])){
		$settings['bell_settings']['getfood_behind'] = "号到服务台取餐。";
	}
	require_once DM_ROOT.'/vendor/aipSpeech/AipSpeech.php';
	$APP_ID = $settings['bell_settings']['appid'];
	$API_KEY = $settings['bell_settings']['apikey'];
	$SECRET_KEY = $settings['bell_settings']['secretkey'];
	if(empty($APP_ID) || empty($API_KEY) || empty($SECRET_KEY)){
		show_json(0,"呼叫功能尚未配置，请到总后台进行配置！");
	}
	$client = new AipSpeech($APP_ID, $API_KEY, $SECRET_KEY);
	$text = $settings['bell_settings']['getfood_front'].$orderInfo['order_number'].$settings['bell_settings']['getfood_behind'];
	$result = $client->synthesis($text, 'zh', 1, array(
	    'vol' => 15,
	    'per' => 0,
	    'spd' => 3,
	));
	if(!is_array($result)){
		$audio_name = md5($text).".mp3";
		$audio_dir = MODULE_ROOT."/data/audios/foodBell/{$_W['uniacid']}";
		if (!file_exists($audio_dir)){
		    @mkdir($audio_dir,0777,true);
		}
		$audio_status = @file_put_contents($audio_dir."/".$audio_name, $result);
		if(!empty($audio_status)){
			show_json(1,"../data/audios/foodBell/{$_W['uniacid']}/{$audio_name}");
		}else{
			show_json(0,"文件保存失败，请检查目录".MODULE_ROOT."/data是否为可写(777)状态");
		}
	}else{
		show_json(0,$result);
	}
	
}
?>
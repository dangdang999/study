<?php
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$uniacid = $_W['uniacid'];
$store_id = intval($_GPC['store_id']);
$storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$uniacid,'id'=>$store_id));
empty($storeInfo) && itoast("门店不存在或已被删除！",$this->createWebUrl('store',array('version_id'=>intval($_GPC['version_id']))),'error');
$operation = empty($_GPC['op']) ? 'display' : trim($_GPC['op']);
$method = trim($_GPC['method']);
$settings = pdo_get("deamx_food_settings",array('uniacid'=>$uniacid));
if($operation == 'display'){
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' and store_id='.$store_id;
	$list = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_desknumber')." WHERE uniacid=:uniacid ".$condition." ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':uniacid'=>$_W['uniacid']));
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('deamx_food_desknumber') . " WHERE uniacid = '{$_W['uniacid']}' $condition");
	$pager = pagination($total, $pindex, $psize);
}elseif($operation == 'post'){
	load()->func('communication');
	load()->classs('wxapp.account');
	$accObj= WxappAccount::create($_W['account']['acid']);
	$access_token = $accObj->getAccessToken();
	if(empty($access_token)){
		itoast("请先配置小程序参数！",$this->createWebUrl('desk',array('store_id'=>$store_id, 'version_id'=>intval($_GPC['version_id']))),'error');
	}
	if (!is_writable(MODULE_ROOT."/data")) {
	    itoast("请先将目录".MODULE_ROOT."/data设置为可写(777)状态",$this->createWebUrl('desk',array('store_id'=>$store_id, 'version_id'=>intval($_GPC['version_id']))),'error');
	}
	$id = intval($_GPC['id']);
	if(!empty($id)){
		$desk = pdo_get("deamx_food_desknumber",array('uniacid'=>$_W['uniacid'],'store_id'=>$store_id,'id'=>$id));
		empty($desk) && itoast("桌号不存在或已被删除",$this->createWebUrl('desk',array('store_id'=>$store_id, 'version_id'=>intval($_GPC['version_id']))),'error');
	}
	if(checksubmit()){
		
		if(empty($id)){
			$addid = 0;
			foreach ($_GPC['name'] as $key => $name) {
				$name = trim($name);
				if(!empty($name)){
					$postArr = array(
						'uniacid'	=>	$_W['uniacid'],
						'store_id'	=>	$store_id,
						'name'		=>	$name
					);
					$result = pdo_insert("deamx_food_desknumber",$postArr);
					$desk_id = pdo_insertid();
					//start
					$wxacodeUrl = "https://api.weixin.qq.com/wxa/getwxacode?access_token=".$access_token;
					$path = 'deam_food/pages/store/detail?store_id='.$store_id.'&desk_id='.$desk_id;
					$postArr = array(
						'path'			=>	$path,
						'width'			=>	800,
						'auto_color'	=>	empty($settings['wxacode_color']) ? true : false
					);
					$postJson = json_encode_ex($postArr);
					$wxacodeResult = ihttp_post($wxacodeUrl,$postJson);
					$wxacode = $wxacodeResult['content'];
					$wxacodeInfo = json_decode($wxacode,true);
					if(empty($wxacodeInfo['errcode'])){
						$wxacode_name = 'imfox_desk_'.md5($path).".jpg";
						$wxacode_dir = MODULE_ROOT."/data/wxacode/{$_W['uniacid']}";
						if (!file_exists($wxacode_dir)){
						    @mkdir($wxacode_dir,0777,true);
						}
						$wxacode_status = @file_put_contents($wxacode_dir."/".$wxacode_name, $wxacode);
					}
					if(!empty($wxacode_status)){//成功写入小程序码
						pdo_update("deamx_food_desknumber",array('wxacode'=>"{$_W['uniacid']}/".$wxacode_name),array('uniacid'=>$_W['uniacid'],'id'=>$desk_id));
					}
					//end
					$addid++;
				}
			}
			if(empty($addid)){
				itoast("请至少添加一个桌号",$this->createWebUrl('desk',array('store_id'=>$store_id,'op'=>'post', 'version_id'=>intval($_GPC['version_id']))),'error');
			}
		}else{
			$postArr = array(
				'uniacid'	=>	$_W['uniacid'],
				'store_id'	=>	$store_id,
				'name'		=>	trim($_GPC['name'])
			);
			$result = pdo_update("deamx_food_desknumber",$postArr,array('uniacid'=>$_W['uniacid'],'store_id'=>$store_id,'id'=>$id));
			$desk_id = $id;
			if(empty($desk['wxacode'])){//未生成桌号小程序码
				//start
				$wxacodeUrl = "https://api.weixin.qq.com/wxa/getwxacode?access_token=".$access_token;
				$path = 'deam_food/pages/store/detail?store_id='.$store_id.'&desk_id='.$desk_id;
				$postArr = array(
					'path'			=>	$path,
					'width'			=>	800,
					'auto_color'	=>	empty($settings['wxacode_color']) ? true : false
				);
				$postJson = json_encode_ex($postArr);
				$wxacodeResult = ihttp_post($wxacodeUrl,$postJson);
				$wxacode = $wxacodeResult['content'];
				$wxacodeInfo = json_decode($wxacode,true);
				if(empty($wxacodeInfo['errcode'])){
					$wxacode_name = 'imfox_desk_'.md5($path).".jpg";
					$wxacode_dir = MODULE_ROOT."/data/wxacode/{$_W['uniacid']}";
					if (!file_exists($wxacode_dir)){
					    @mkdir($wxacode_dir,0777,true);
					}
					$wxacode_status = @file_put_contents($wxacode_dir."/".$wxacode_name, $wxacode);
				}
				if(!empty($wxacode_status)){//成功写入小程序码
					pdo_update("deamx_food_desknumber",array('wxacode'=>"{$_W['uniacid']}/".$wxacode_name),array('uniacid'=>$_W['uniacid'],'id'=>$desk_id));
				}
				//end
			}
		}
		
		itoast(empty($id) ? '添加成功':'更新成功',$this->createWebUrl('desk',array('store_id'=>$store_id, 'version_id'=>intval($_GPC['version_id']))),'success');
	}
}elseif($operation == 'delete'){
	$id = intval($_GPC['id']);
	pdo_delete("deamx_food_desknumber",array('uniacid'=>$uniacid,'id'=>$id,'store_id'=>$store_id));
	itoast('桌号删除成功！',$this->createWebUrl('desk',array('store_id'=>$store_id, 'version_id'=>intval($_GPC['version_id']))), 'success');
}
include $this->template('system/store/desk');
?>
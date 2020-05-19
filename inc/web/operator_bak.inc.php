<?php
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$uniacid = $_W['uniacid'];
$store_id = intval($_GPC['store_id']);
$storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$uniacid,'id'=>$store_id));
empty($storeInfo) && itoast("门店不存在或已被删除！",$this->createWebUrl('store',array('version_id'=>intval($_GPC['version_id']))),'error');
$operation = empty($_GPC['op']) ? 'display' : trim($_GPC['op']);
if($operation == 'display'){
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' and store_id='.$store_id;
	$list = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_store_clerk')." WHERE uniacid=:uniacid ".$condition." ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':uniacid'=>$_W['uniacid']));
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('deamx_food_store_clerk') . " WHERE uniacid = '{$_W['uniacid']}' $condition");
	$pager = pagination($total, $pindex, $psize);
}elseif($operation == 'post'){
	$id = intval($_GPC['id']);
	if(!empty($id)){
		$operator = pdo_get("deamx_food_store_clerk",array('uniacid'=>$_W['uniacid'],'store_id'=>$store_id,'id'=>$id));
		empty($operator) && itoast("店员不存在或已被删除",$this->createWebUrl('operator',array('store_id'=>$store_id, 'version_id'=>intval($_GPC['version_id']))),'error');
		$perms = explode("|", $operator['permission']);
	}
	if(checksubmit()){
		$password = trim($_GPC['password']);
		$postArr = array(
			'uniacid'		=>	$_W['uniacid'],
			'store_id'		=>	$store_id,
			'name'			=>	trim($_GPC['name']),
			'password'		=>	md5(DM_PWDPRE.$password),
			'realname'		=>	trim($_GPC['realname']),
			'telphone'		=>	trim($_GPC['telphone']),
			'status'		=>	intval($_GPC['status']),
			'remark'		=>	trim($_GPC['remark']),
			'permission'	=>	implode("|", $_GPC['perms'])
		);
		if(empty($id)){
			//检测店员用户名是否存在
			$isexist = pdo_get("deamx_food_store_clerk",array('name'=>$postArr['name']));
			if(!empty($isexist)){
				itoast("该用户名已存在！","",'error');
				exit;
			}
			$result = pdo_insert("deamx_food_store_clerk",$postArr);
		}else{
			if(empty($password)){
				unset($postArr['password']);
			}
			unset($postArr['name']);
			$result = pdo_update("deamx_food_store_clerk",$postArr,array('uniacid'=>$_W['uniacid'],'store_id'=>$store_id,'id'=>$id));
		}
		itoast(empty($id) ? '添加成功':'更新成功',$this->createWebUrl('operator',array('store_id'=>$store_id, 'version_id'=>intval($_GPC['version_id']))),'success');
	}
}elseif($operation == 'delete'){
	$id = intval($_GPC['id']);
	$store_id = intval($_GPC['store_id']);
	pdo_delete("deamx_food_store_clerk", array('id' => $id, 'store_id' => $store_id, 'uniacid' => $_W['uniacid']));
	itoast("成功删除店员","",'success');
}
include $this->template('system/store/operator');
?>
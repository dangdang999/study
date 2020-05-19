<?php
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$uniacid = $_W['uniacid'];
$storeArr = pdo_getall("deamx_food_store",array('uniacid'=>$uniacid),array('id', 'name'),"id");
$settings = pdo_get("deamx_food_settings",array('uniacid'=>$uniacid));
empty($storeArr) && itoast("请先创建门店！",$this->createWebUrl('store',array('version_id'=>intval($_GPC['version_id']))),'error');
$operation = empty($_GPC['op']) ? 'display' : trim($_GPC['op']);
if($operation == 'display'){
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;

	$list = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_store_clerk')." WHERE uniacid=:uniacid ".$condition." ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':uniacid'=>$_W['uniacid']));
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('deamx_food_store_clerk') . " WHERE uniacid = '{$_W['uniacid']}' $condition");
	$pager = pagination($total, $pindex, $psize);
}elseif($operation == 'post'){
	$id = intval($_GPC['id']);
	if(!empty($id)){
		$operator = pdo_get("deamx_food_store_clerk",array('uniacid'=>$_W['uniacid'],'id'=>$id));
		if(empty($operator)){
			itoast("店员不存在或已被删除",$this->createWebUrl('operator',array('version_id'=>intval($_GPC['version_id']))),'error');
		}elseif(empty($operator['system_uid'])){
			itoast("该账号需要修复！",$this->createWebUrl('operator',array('version_id'=>intval($_GPC['version_id']))),'error');
		}
		$perms = explode("|", $operator['permission']);
	}
	if(checksubmit('submit')){
		$password = trim($_GPC['password']);
		$postArr = array(
			'uniacid'		=>	$_W['uniacid'],
			'store_id'		=>	intval($_GPC['store_id']),
			'name'			=>	trim($_GPC['name']),
			'password'		=>	md5(DM_PWDPRE.$password),
			'realname'		=>	trim($_GPC['realname']),
			'telphone'		=>	trim($_GPC['telphone']),
			'status'		=>	intval($_GPC['status']),
			'remark'		=>	trim($_GPC['remark']),
			'permission'	=>	implode("|", $_GPC['perms'])
		);
		$uid = intval($_GPC['system_uid']);
		$user = user_single($uid);
		$insert_user = array(
			'username' => trim($_GPC['name']),
			'remark' => trim($_GPC['remark']),
			'password' => trim($_GPC['password']),
			'repassword' => trim($_GPC['password']),
			'type' => '3'
		);
		if(empty($operator)){
			if (empty($uid)) {
				if (user_check(array('username' => $insert_user['username']))) {
					itoast('非常抱歉，此用户名已经被注册，你需要更换注册名称！',"",'error');
				}
				unset($insert_user['repassword']);
				$uid = user_register($insert_user, "deam_food");
				if (!$uid) {
					itoast('注册账号失败，请重试！', '', 'error');
				}
			}else {
				$operator['password'] = $insert_user['password'];
				$operator['salt'] = $user['salt'];
				$operator['uid'] = $uid;
				$operator['username'] = $insert_user['username'];
				$operator['remark'] = $insert_user['remark'];
				$operator['type'] = $insert_user['type'];
				user_update($operator);
			}
		}elseif(!empty($password)){
			$user = pdo_get("users", array('username' => $insert_user['username']));
			
			$operator['password'] = $insert_user['password'];
			$operator['salt'] = $user['salt'];
			$operator['uid'] = $user['uid'];
			$operator['username'] = $insert_user['username'];
			$operator['remark'] = $insert_user['remark'];
			$operator['type'] = $insert_user['type'];
			$operator['status'] = '2';
			user_update($operator);
		}
		if(empty($id)){
			$postArr['system_uid'] = $uid;
			$result = pdo_insert("deamx_food_store_clerk",$postArr);
		}else{
			if(empty($password)){
				unset($postArr['password']);
			}
			unset($postArr['name']);
			$result = pdo_update("deamx_food_store_clerk",$postArr,array('uniacid'=>$_W['uniacid'],'id'=>$id));
		}
		itoast(empty($id) ? '添加成功':'更新成功',$this->createWebUrl('operator',array('version_id'=>intval($_GPC['version_id']))),'success');
	}
}elseif($operation == 'recover'){
	$id = intval($_GPC['id']);
	$operator = pdo_get("deamx_food_store_clerk",array('uniacid'=>$_W['uniacid'],'id'=>$id));
	if(empty($operator)){
		itoast("店员不存在或已被删除",$this->createWebUrl('operator',array('version_id'=>intval($_GPC['version_id']))),'error');
	}elseif (!empty($operator['system_uid'])) {
		itoast("该账号无需修复！",$this->createWebUrl('operator',array('version_id'=>intval($_GPC['version_id']))),'error');
	}
	$insert_user = array(
		'username' => trim($operator['name']),
		'remark' => $operator['remark'],
		'password' => 'a12345678',
		'repassword' => 'a12345678',
		'type' => '3'
	);
	if (user_check(array('username' => $insert_user['username']))) {
		itoast('修复失败，原因：系统中已存在该用户名！',"",'error');
	}
	unset($insert_user['repassword']);
	$uid = user_register($insert_user, "deam_food");
	if (!$uid) {
		itoast('修复失败，原因：注册账号失败，请重试！', '', 'error');
	}else{
		pdo_update("deamx_food_store_clerk", array('system_uid' => $uid), array('id' => $operator['id']));
		itoast('修复成功，密码重置为：a12345678', '', 'info');
	}
}elseif($operation == 'post_bak'){
	$id = intval($_GPC['id']);
	if(!empty($id)){
		$operator = pdo_get("deamx_food_store_clerk",array('uniacid'=>$_W['uniacid'],'id'=>$id));
		if(empty($operator)){
			itoast("店员不存在或已被删除",$this->createWebUrl('operator',array('version_id'=>intval($_GPC['version_id']))),'error');
		}
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
			$result = pdo_update("deamx_food_store_clerk",$postArr,array('uniacid'=>$_W['uniacid'],'id'=>$id));
		}
		itoast(empty($id) ? '添加成功':'更新成功',$this->createWebUrl('operator',array('version_id'=>intval($_GPC['version_id']))),'success');
	}
}elseif($operation == 'delete'){
	$id = intval($_GPC['id']);
	$operator = pdo_get("deamx_food_store_clerk",array('uniacid'=>$_W['uniacid'],'id'=>$id));
	pdo_delete("users", array('uid' => $operator['system_uid']));
	pdo_delete("deamx_food_store_clerk", array('id' => $id, 'uniacid' => $_W['uniacid']));
	itoast("店员删除成功！","",'success');
}
include $this->template('system/store/operator');
?>
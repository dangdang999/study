<?php
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$operation = empty($_GPC['op']) ? 'list' : trim($_GPC['op']);
$settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']));
if($operation == 'add'){
	$id = intval($_GPC['id']);
	if(!empty($id)){
		$advItem = pdo_fetch("SELECT * FROM ".tablename('deamx_food_adv')." WHERE uniacid=:uniacid AND id=:id ORDER BY id DESC",array(':uniacid'=>$_W['uniacid'],':id'=>$id));
		empty($advItem) && itoast('幻灯片不存在或已被删除！', $this->createWebUrl('adv'), 'error');
	}
	if(checksubmit('submit')){
		$insertArr = array(
			'uniacid'		=>	$_W['uniacid'],
			'adv_title'		=>	trim($_GPC['adv_title']),
			'adv_img'		=>	trim($_GPC['adv_img']),
			'adv_url'		=>	trim($_GPC['adv_url']),
			'adv_isshow'	=>	intval($_GPC['enabled']),
			'sortid'		=>	intval($_GPC['displayorder'])
		);
		
		if (empty($id)) {

			pdo_insert("deamx_food_adv",$insertArr);

			$id = pdo_insertid();

		}else{
			pdo_update("deamx_food_adv",$insertArr,array("id"=>$id));
		}
		itoast('更新成功！', $this->createWebUrl('adv', array('op' => 'list', 'version_id'=>intval($_GPC['version_id']))), 'success');
	}
}elseif($operation == 'list'){
	if(checksubmit('submit')){
		if (!empty($_GPC['sortid'])) {
	        foreach ($_GPC['sortid'] as $id => $displayorder) {
	            pdo_update('deamx_food_adv', array(
	                'sortid' => $displayorder
	            ), array(
	                'id' => $id
	            ));
	        }
	        itoast('排序更新成功！', $this->createWebUrl('adv', array('op' => 'list', 'version_id'=>intval($_GPC['version_id']))), 'success');
	    }
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = '';
	$list = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_adv')." WHERE uniacid=:uniacid ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':uniacid'=>$_W['uniacid']));
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('deamx_food_adv') . " WHERE uniacid = '{$_W['uniacid']}' $condition");
	$pager = pagination($total, $pindex, $psize);
	
}elseif($operation == 'delete'){
	$id = intval($_GPC['id']);
	$result = pdo_delete("deamx_food_adv",array('uniacid'=>$_W['uniacid'],'id'=>$id));
	$result && itoast('删除成功！', $this->createWebUrl('adv', array('op' => 'list', 'version_id'=>intval($_GPC['version_id']))), 'success');
}
include $this->template('system/sysset/adv');
?>
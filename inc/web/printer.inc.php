<?php
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$uniacid = $_W['uniacid'];
$store_id = intval($_GPC['store_id']);
$storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$uniacid,'id'=>$store_id));
empty($storeInfo) && itoast("门店不存在或已被删除！",$this->createWebUrl('store',array('version_id'=>intval($_GPC['version_id']))),'error');
$operation = empty($_GPC['op']) ? 'display' : trim($_GPC['op']);
$method = trim($_GPC['method']);

if($operation == 'display'){
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' and store_id='.$store_id;
	$list = pdo_fetchall("SELECT * FROM " . tablename('deamx_food_printer') . " WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('deamx_food_printer') . " WHERE uniacid = '{$_W['uniacid']}' {$condition}");

	$pager = pagination($total, $pindex, $psize);
}elseif ($operation == 'post') {
	$class = pdo_getall("deamx_food_class",array('uniacid'=>$uniacid,'store_id'=>$store_id),array('id','classname'),'id');
	$id = intval($_GPC['id']);
	if (!empty($id)) {//修改
		$printer = pdo_fetch("SELECT * FROM " . tablename('deamx_food_printer') . " WHERE id = :id AND uniacid = :uniacid and store_id=:store_id", array(':id' => $id,':uniacid'=>$uniacid,':store_id'=>$store_id));
		if (empty($printer)) {
			itoast('打印机不存在或已删除！', '', 'error');
		}
		$printer['print_data'] = json_decode($printer['print_data'],true);
		$print_class = json_decode($printer['print_class'],true);
		
	}
	if (checksubmit('submit')) {
		$dataArr = array(
			'uniacid'		=>	$uniacid,
			'store_id'		=>	$store_id,
			'title'			=>	trim($_GPC['title']),
			'type'			=>	intval($_GPC['type']),
			'print_type'	=>	intval($_GPC['print_type']),
			'createtime'	=>	TIMESTAMP,
			'print_data'	=>	json_encode(array('printer_yilianyun_new'=>$_GPC['printer_yilianyun_new'],'printer_feie'=>$_GPC['printer_feie'],'printer_365s1'=>$_GPC['printer_365s1'])),
			'print_class'	=>	json_encode($_GPC['print_class']),
			'status'		=>	intval($_GPC['status']),
            'order_qrcode'  =>  intval($_GPC['order_qrcode'])
		);
		if (empty($id)) {
			pdo_insert('deamx_food_printer', $dataArr);
			$id = pdo_insertid();
		}else{
			unset($dataArr['createtime']);
			pdo_update("deamx_food_printer", $dataArr,array("id"=>$id,'store_id'=>$store_id));
		}
		itoast('更新成功！', $this->createWebUrl('printer',array('store_id'=>$store_id, 'version_id'=>intval($_GPC['version_id']))), 'success');
	}
}elseif($operation == 'delete'){
	$id = intval($_GPC['id']);
	pdo_delete("deamx_food_printer",array('uniacid'=>$uniacid,'id'=>$id,'store_id'=>$store_id));
	itoast('删除成功！', $this->createWebUrl('printer',array('store_id'=>$store_id, 'version_id'=>intval($_GPC['version_id']))), 'success');
}
include $this->template('system/store/printer');
?>
<?php
defined('IN_IA') or exit('Access Denied');
$operation = empty($operation) ? 'display' : $operation;
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
		if(!in_array("store_printer_edit", $user_permissionArr)){
			itoast('您没有相应操作权限！',manage_url(array('r'=>'store','ac'=>'printer','op'=>'display')),'error');
			exit;
		}
		$printer = pdo_fetch("SELECT * FROM " . tablename('deamx_food_printer') . " WHERE id = :id AND uniacid = :uniacid and store_id=:store_id", array(':id' => $id,':uniacid'=>$uniacid,':store_id'=>$store_id));
		if (empty($printer)) {
			itoast('打印机不存在或已删除！', '', 'error');
			exit;
		}
		$printer['print_data'] = json_decode($printer['print_data'],true);
		$print_class = json_decode($printer['print_class'],true);
	}else{
		if(!in_array("store_printer_add", $user_permissionArr)){
			itoast('您没有相应操作权限！',manage_url(array('r'=>'store','ac'=>'printer','op'=>'display')),'error');
			exit;
		}
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
		itoast('更新成功！', manage_url(array('r'=>'store','ac'=>'printer','op'=>'display')), 'success');
	}
}elseif($operation == 'delete'){
	if(!in_array("store_printer_delete", $user_permissionArr)){
		itoast('您没有相应操作权限！',manage_url(array('r'=>'store','ac'=>'printer','op'=>'display')),'error');
		exit;
	}
	$id = intval($_GPC['id']);
	pdo_delete("deamx_food_printer",array('uniacid'=>$uniacid,'id'=>$id,'store_id'=>$store_id));
	itoast('删除成功！', manage_url(array('r'=>'store','ac'=>'printer','op'=>'display')), 'success');
}
include manage_template("store/printer");
?>
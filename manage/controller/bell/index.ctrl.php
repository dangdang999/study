<?php
defined('IN_IA') or exit('Access Denied');
$operation = empty($operation) ? 'display' : $operation;
if($operation == 'display'){
	$nobell = intval($_GPC['nobell']);
	
}elseif($operation == 'getlist'){
	$condition = ' and store_id='.$store_id;
	$list = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_notice')." WHERE uniacid=:uniacid ".$condition." AND status='0' ORDER BY id DESC",array(':uniacid'=>$_W['uniacid']));
	if(!empty($list)){
		foreach ($list as &$row) {
			$row['createtime'] = date("m-d H:i:s",$row['createtime']);
		}
	}
	$total = count($list);
	show_json(1,array('list'=>$list,'total'=>$total));
}elseif($operation == 'deal'){
	$id = intval($_GPC['id']);
	pdo_update("deamx_food_notice",array('status'=>'1'),array('uniacid'=>$_W['uniacid'],'id'=>$id,'store_id'=>$store_id));
	itoast('',manage_url(array('r'=>'bell','ac'=>'index')),'success');
}
include manage_template("store/bell");
?>
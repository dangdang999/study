<?php
defined('IN_IA') or exit('Access Denied');
if($operation == 'order_prompt'){
	$lastOrder = pdo_fetch("SELECT * FROM ".tablename('deamx_food_order')." WHERE uniacid=:uniacid and store_id=:store_id and status>='1' and is_prompt=:is_prompt ORDER BY id DESC LIMIT 0,1",array(':uniacid'=>$_W['uniacid'],':store_id'=>$store_id,':is_prompt'=>'0'));
	if(!empty($lastOrder)){
		pdo_update("deamx_food_order",array('is_prompt'=>'1'),array('uniacid'=>$_W['uniacid'],'status >='=>'1','store_id'=>$store_id));
		show_json(1);
	}else{
		show_json(0);
	}
}elseif($operation == 'check_order'){
	$check_order['count0'] = pdo_fetchcolumn('select count(*) from ' . tablename('deamx_food_order') . ' where uniacid=:uniacid and status=:status and store_id=:store_id and order_type=:order_type', array(':uniacid'=>$_W['uniacid'],':status'=>'1',':store_id'=>$store_id,':order_type'=>'2'));
	$check_order['count1'] = pdo_fetchcolumn('select count(*) from ' . tablename('deamx_food_order') . ' where uniacid=:uniacid and status=:status and store_id=:store_id and order_type=:order_type', array(':uniacid'=>$_W['uniacid'],':status'=>'2',':store_id'=>$store_id,':order_type'=>'2'));
	$check_order['count2'] = pdo_fetchcolumn('select count(*) from ' . tablename('deamx_food_order') . ' where uniacid=:uniacid and status=:status and store_id=:store_id and order_type=:order_type', array(':uniacid'=>$_W['uniacid'],':status'=>'1',':store_id'=>$store_id,':order_type'=>'1'));
	$check_order['count3'] = pdo_fetchcolumn('select count(*) from ' . tablename('deamx_food_notice') . ' where uniacid=:uniacid and status=:status and store_id=:store_id', array(':uniacid'=>$_W['uniacid'],':status'=>'0',':store_id'=>$store_id));
	show_json(1,$check_order);
}

?>
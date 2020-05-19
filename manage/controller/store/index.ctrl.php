<?php
defined('IN_IA') or exit('Access Denied');
$days = 30;
$i = $days;
$datas = array();
while (0 <= $i) {
	$time = date('Y-m-d', strtotime('-' . $i . ' day'));
	$condition = ' and uniacid=:uniacid and store_id=:store_id and paytime>=:starttime and paytime<=:endtime and status>:status';
	$params = array(':uniacid' => $_W['uniacid'], ':starttime' => strtotime($time . ' 00:00:00'), ':endtime' => strtotime($time . ' 23:59:59'), ':status' => '0',':store_id'=>$store_id);
	$datas[] = array('date' => $time, 'order_count' => pdo_fetchcolumn('select count(*) from ' . tablename('deamx_food_order') . ' where 1 ' . $condition, $params));
	--$i;
}
$todaytime = strtotime(date('Y-m-d'));
$order_stat['status1'] = pdo_fetchcolumn('select count(*) from ' . tablename('deamx_food_order') . ' where uniacid=:uniacid and status=:status and store_id=:store_id', array(':uniacid'=>$_W['uniacid'],':status'=>'1',':store_id'=>$store_id));
$order_stat['status2'] = pdo_fetchcolumn('select count(*) from ' . tablename('deamx_food_order') . ' where uniacid=:uniacid and status=:status and store_id=:store_id', array(':uniacid'=>$_W['uniacid'],':status'=>'2',':store_id'=>$store_id));
$order_stat['status3'] = pdo_fetchcolumn('select count(*) from ' . tablename('deamx_food_order') . ' where uniacid=:uniacid and status=:status and receivetime>=:todaytime and store_id=:store_id', array(':uniacid'=>$_W['uniacid'],':status'=>'3',':todaytime'=>$todaytime,':store_id'=>$store_id));
$order_stat['todayCount'] = pdo_fetchcolumn('select count(*) from ' . tablename('deamx_food_order') . ' where uniacid=:uniacid and status>:status and paytime>=:todaytime and store_id=:store_id', array(':uniacid'=>$_W['uniacid'],':status'=>'0',':todaytime'=>$todaytime,':store_id'=>$store_id));
$order_stat['todayPrice'] = pdo_fetchcolumn('select SUM(pay_price) from ' . tablename('deamx_food_order') . ' where uniacid=:uniacid and status>:status and paytime>=:todaytime and store_id=:store_id', array(':uniacid'=>$_W['uniacid'],':status'=>'0',':todaytime'=>$todaytime,':store_id'=>$store_id));

$order_stat['todayPrice'] = number_format($order_stat['todayPrice'],2);
if ($order_stat['todayCount'] == 0) {
	$order_stat['per_price'] = "0.00";
}else{
	$order_stat['per_price'] = number_format($order_stat['todayPrice'] / $order_stat['todayCount'],2);
}
if(!$isMobile){
	include manage_template('store/index');
}else{
	include manage_template('store/mobile/index');
}

?>
<?php
/**
 * 门店管理后台
 *
 * @author cmszs
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
class Statistics_DeamFoodManage extends DeamFoodManage
{
	public function index(){
		global $_W,$_GPC;
		$storeId = intval($_GPC['storeId']);
		$todaytime = strtotime(date('Y-m-d'));
		$order_stat['todayPrice'] = pdo_fetchcolumn('select SUM(pay_price) from ' . tablename('deamx_food_order') . ' where uniacid=:uniacid and status>:status and paytime>=:todaytime and store_id=:store_id', array(':uniacid'=>$_W['uniacid'],':status'=>'0',':todaytime'=>$todaytime,':store_id'=>$storeId));
		$order_stat['todayPrice'] = number_format($order_stat['todayPrice'],2);

		$order_stat['todayCount'] = pdo_fetchcolumn('select count(*) from ' . tablename('deamx_food_order') . ' where uniacid=:uniacid and status>:status and paytime>=:todaytime and store_id=:store_id', array(':uniacid'=>$_W['uniacid'],':status'=>'0',':todaytime'=>$todaytime,':store_id'=>$storeId));
		if ($order_stat['todayCount'] == 0) {
			$order_stat['perPrice'] = "0.00";
		}else{
			$order_stat['perPrice'] = number_format($order_stat['todayPrice'] / $order_stat['todayCount'],2);
		}
		show_json(1, array('data' => $order_stat));
	}
}
?>
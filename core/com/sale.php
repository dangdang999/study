<?php
function sort_enoughs($a, $b)
{
	$enough1 = floatval($a['enough']);
	$enough2 = floatval($b['enough']);

	if ($enough1 == $enough2) {
		return 0;
	}

	return $enough1 < $enough2 ? 1 : -1;
}
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Sale_DeamFoodComModel{

	public function getRechargeActivity()
	{
		global $_W;
		$set = m('common')->getPluginset('sale');
		$recharges = iunserializer($set['recharges']);

		if (is_array($recharges)) {
			usort($recharges, 'sort_enoughs');
			return $recharges;
		}

		return false;
	}

	public function setRechargeActivity($log)
	{
		global $_W;
		$set = m('common')->getPluginset('sale');
		$recharges = iunserializer($set['recharges']);
		$credit2 = 0;
		$enough = 0;
		$give = '';
		// load()->func('logging');
		// logging_run($recharges);
		if (is_array($recharges)) {
			usort($recharges, 'sort_enoughs');

			foreach ($recharges as $r) {
				if (empty($r['enough']) || empty($r['give'])) {
					continue;
				}

				if (floatval($r['enough']) <= $log['price']) {
					if (strexists($r['give'], '%')) {
						$credit2 = round((floatval(str_replace('%', '', $r['give'])) / 100) * $log['price'], 2);
					}
					else {
						$credit2 = round(floatval($r['give']), 2);
					}

					$enough = floatval($r['enough']);
					$give = $r['give'];
					if($log['price'] >= $enough){
					break;
				}	
				}
			}
		}
		
		//logging_run($credit2);
		if (0 < $credit2) {
			//m('member')->setCredit($log['openid'], 'credit2', $credit2, array('0', $_S['shop']['name'] . '充值满' . $enough . '赠送' . $give, '现金活动'));
			load()->model('mc');
			m('member')->credit_update($log['uid'], "credit2", $credit2, array($log['uid'], '充值满' . $enough . '赠送' . $give, '现金活动'));
			pdo_update('deamx_food_recharge_log', array('gives' => $credit2), array('id' => $log['id']));
		}

		//$this->getCredit1($log['openid'], $log['money'], 21, 2);
	}

	/**
     * 传入金额,生成满立减优惠
     * @param string $uid 用户uid
     * @param int $price 传入金额
     * @param int $paytype 支付类型 1 余额支付; 3 货到付款; 21 微信支付; 22 支付宝支付; 37 收银台付款;
     * @param int $type 购物送积分 1 充值送积分 2
     * @param int $refund 是否是退款
     * @param string $desc 是否是退款
     * @return float|int
     */
	public function getCredit1($uid, $price = 0, $paytype = 1, $type = 1, $refund = 0, $desc = '')
	{
		global $_W;
		$type = intval($type);
		if (empty($uid) || empty($price) || empty($type)) {
			return 0;
		}

		$data = m('common')->getPluginset('sale');
		$credit1 = iunserializer($data['credit1']);
		
		if ($type == '1') {
			$name = '积分活动购物送积分';
			$enoughs = (empty($credit1['enough1']) ? array() : $credit1['enough1']);
			if (empty($credit1['paytype'])) {
				return 0;
			}

			if (!empty($credit1['paytype']) && !in_array($paytype, array_keys($credit1['paytype']))) {
				return 0;
			}
		}
		else {
			if ($type = '2') {
				$name = '积分活动充值送积分';
				$enoughs = (empty($credit1['enough2']) ? array() : $credit1['enough2']);
			}
		}

		if (!empty($desc)) {
			$name = $desc;
		}

		$allenoughs = array();

		if (is_array($enoughs)) {
			foreach ($enoughs as $e) {
				if ((floatval($e['enough' . $type . '_1']) <= $price) && ($price <= floatval($e['enough' . $type . '_2']))) {
					if (0 < floatval($e['give' . $type])) {
						$allenoughs[] = floatval($e['give' . $type]);
					}
				}
			}
		}

		$money = 0;

		if (!empty($allenoughs)) {
			$money = (double) max($allenoughs);
		}

		if (0 < $money) {
			$money *= $price;
			$money = floor($money);
			load()->model('mc');
			if (empty($refund)) {
				m('member')->credit_update($uid, "credit1", $money, array($uid, $name . ': ' . $money . '积分'));
			}
			else {
				m('member')->credit_update($uid, "credit1", 0 - $money, array($uid, $name . '退款 : ' . (0 - $money) . '积分'));
			}
		}

		return $money;
	}
}

?>

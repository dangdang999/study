<?php
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$uniacid = $_W['uniacid'];
$operation = empty($_GPC['op']) ? 'sale' : trim($_GPC['op']);
$settings = pdo_get("deamx_food_settings",array('uniacid'=>$uniacid));
$needEcharts = true;
if($operation == 'sale'){
	$storeList = pdo_getall("deamx_food_store",array('uniacid'=>$_W['uniacid']),array('id','name'));
	$store_id = intval($_GPC['storeid']);
	$condition = "";
	if(!empty($store_id)){
		$condition .= " and store_id = ".$store_id;
	}
	$years = array();
	$current_year = date('Y');
	$year = empty($_GPC['year']) ? $current_year : trim($_GPC['year']);
	$i = $current_year - 5;
	while ($i <= $current_year){
		$years[] = array('data' => $i, 'selected' => $i == $year);
		++$i;
	}
	$months = array();
	$current_month = date('m');
	$month = $_GPC['month'];
	$i = 1;
	while ($i <= 12){
		$months[] = array('data' => $i, 'selected' => $i == $month);
		++$i;
	}
	$day = intval($_GPC['day']);
	$type = intval($_GPC['type']);
	$list = array();
	$totalcount = 0;
	$maxcount = 0;
	$maxcount_date = '';
	$maxdate = '';
	$countfield = empty($type) ? 'sum(price)' : 'count(*)';
	$typename = empty($type) ? '交易额' : '交易量';
	$dataname = empty($month) ? '月份' : '日期';
	if (!(empty($year)) && !(empty($month)) && !(empty($day))) {
		$hour = 0;
		while ($hour < 24){
			$nexthour = $hour + 1;
			$dr = array('data' => $hour . ':00 - ' . $nexthour . ':00', 'count' => pdo_fetchcolumn('SELECT ifnull(' . $countfield . ',0) as cnt FROM ' . tablename('deamx_food_order') . ' WHERE 1 '.$condition.' and uniacid=:uniacid and status>=1 and createtime >=:starttime and createtime <=:endtime', array(':uniacid' => $_W['uniacid'], ':starttime' => strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':00:00'), ':endtime' => strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':59:59'))));
			$totalcount += $dr['count'];
			if ($maxcount < $dr['count']){
				$maxcount = $dr['count'];
				$maxcount_date = $year . '年' . $month . '月' . $day . '日 ' . $hour . ':00 - ' . $nexthour . ':00';
			}
			$list[] = $dr;
			$hour++;
		}
	}else if (!(empty($year)) && !(empty($month))){
		$lastday = get_last_day($year, $month);
		$d = 1;
		while ($d <= $lastday){
			$dr = array('data' => $d, 'count' => pdo_fetchcolumn('SELECT ifnull(' . $countfield . ',0) as cnt FROM ' . tablename('deamx_food_order') . ' WHERE 1 '.$condition.' and uniacid=:uniacid and status>=1 and createtime >=:starttime and createtime <=:endtime', array(':uniacid' => $_W['uniacid'], ':starttime' => strtotime($year . '-' . $month . '-' . $d . ' 00:00:00'), ':endtime' => strtotime($year . '-' . $month . '-' . $d . ' 23:59:59'))));
			$totalcount += $dr['count'];
			if ($maxcount < $dr['count']){
				$maxcount = $dr['count'];
				$maxcount_date = $year . '年' . $month . '月' . $d . '日';
			}
			$list[] = $dr;
			$d++;
		}
	}else if (!(empty($year))) {
		foreach ($months as $k => $m){
			$lastday = get_last_day($year, $k + 1);
			$dr = array('data' => $m['data'], 'count' => pdo_fetchcolumn('SELECT ifnull(' . $countfield . ',0) as cnt FROM ' . tablename('deamx_food_order') . ' WHERE 1 '.$condition.' and uniacid=:uniacid and status>=1 and createtime >=:starttime and createtime <=:endtime', array(':uniacid' => $_W['uniacid'], ':starttime' => strtotime($year . '-' . $m['data'] . '-01 00:00:00'), ':endtime' => strtotime($year . '-' . $m['data'] . '-' . $lastday . ' 23:59:59'))));
			$totalcount += $dr['count'];
			if ($maxcount < $dr['count']){
				$maxcount = $dr['count'];
				$maxcount_date = $year . '年' . $m['data'] . '月';
			}
			$list[] = $dr;
		}
	}
	foreach ($list as $key => &$row ){
		$list[$key]['percent'] = number_format(($row['count'] / ((empty($totalcount) ? 1 : $totalcount))) * 100, 2);
	}
	unset($row);
}elseif($operation == 'get_day'){
	$year = intval($_GPC['year']);
	$month = intval($_GPC['month']);
	exit(get_last_day($year, $month));
}elseif ($operation == 'recharge'){
    $todayTimeDate = date("Y-m-d");
    $prev30Day = date('Y-m-d', strtotime('-30 days'));
    $dateRange = $prev30Day . " ~ " . $todayTimeDate;
}elseif($operation == 'get_recharge_data'){
    $todayTime = strtotime(date("Y-m-d"));
    $prev7Day = strtotime(date('Y-m-d', strtotime('-6 days')));
    $todayRechargeNum = pdo_getcolumn("deamx_food_credits_record", array('uniacid' => $_W['uniacid'], 'credittype' => 'credit2', 'createtime >=' => $todayTime, 'num >' => '0'), "SUM(num)");
    $prev7DayRechargeNum = pdo_getcolumn("deamx_food_credits_record", array('uniacid' => $_W['uniacid'], 'credittype' => 'credit2', 'createtime >=' => $prev7Day, 'num >' => '0'), "SUM(num)");
    $totalRechargeNum = pdo_getcolumn("deamx_food_credits_record", array('uniacid' => $_W['uniacid'], 'credittype' => 'credit2', 'num >' => '0'), "SUM(num)");
    $lastRechargeNum = pdo_getcolumn("deamx_food_members", array('uniacid' => $_W['uniacid']), "SUM(credit2)");


    $todayRechargeNum = number_format($todayRechargeNum, '2');
    $prev7DayRechargeNum = number_format($prev7DayRechargeNum, '2');
    $totalRechargeNum = number_format($totalRechargeNum, '2');
    show_json(1, array('todayRecharge' => $todayRechargeNum, 'prev7DayRechargeNum' => $prev7DayRechargeNum, 'totalRechargeNum' => $totalRechargeNum, 'lastRechargeNum' => $lastRechargeNum));
}elseif($operation == 'get_recharge_list'){
    $data_range = trim($_GPC['data_range']);
    $dataArr = explode(" ~ ", $data_range);
    $dataStartTime = strtotime(trim($dataArr[0]));
    $dataEndTime = strtotime(trim($dataArr[1]));
    if(($dataEndTime - $dataStartTime) / (3600 * 24) > 30){
        show_json(0, "时间范围不能超过30天");
    }
    $dateArr = array();
    $rechargeArr = array();
    $payArr = array();
    for ($x=$dataStartTime; $x<=$dataEndTime; $x += 3600 * 24) {
        $nextX = $x + 3600 * 24;
        $dateArr[] = date("Y-m-d", $x);
        $rechargeCount = pdo_getcolumn("deamx_food_credits_record", array('uniacid' => $_W['uniacid'], 'credittype' => 'credit2', 'createtime >=' => $x, 'createtime <=' => $nextX, 'num >' => '0'), "SUM(num)");
        $rechargeArr[] = number_format($rechargeCount, 2);
        $payCount = pdo_getcolumn("deamx_food_credits_record", array('uniacid' => $_W['uniacid'], 'credittype' => 'credit2', 'createtime >=' => $x, 'createtime <=' => $nextX, 'num <' => '0'), "SUM(num)");
        $payCount = abs(number_format($payCount, 2, '.', ''));
        $payArr[] = number_format($payCount, 2);
    }

    show_json(1, array('date' => $dateArr, 'recharge' => $rechargeArr, 'pay' => $payArr));
    exit();
}

include $this->template("system/statistics/index");
?>
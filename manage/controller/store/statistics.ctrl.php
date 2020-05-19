<?php
defined('IN_IA') or exit('Access Denied');
$operation = empty($operation) ? 'sale' : $operation;
if($operation == 'sale'){
    $condition = " and store_id=".$store_id;
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
}elseif($operation == 'goods'){
    $daterange = $_GPC['daterange'];
    if(empty($daterange['start'])){
        $daterange['start'] = date("Y-m-d",strtotime("-6 day"));//
        $daterange['end'] = date("Y-m-d",strtotime(date("Y-m-d",strtotime("+1 day")))-1);//
    }

    $starttime = strtotime($daterange['start']." 00:00:00");
    $endtime = strtotime($daterange['end']." 23:59:59");
    $daycount = ceil(($endtime-$starttime) / (3600*24));
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $method = trim($_GPC['method']);
    if($method == 'export'){
        $psize = 20000;
        $pindex = 1;
    }
    $condition = ' and goods.store_id='.$store_id;
    $condition .= " AND og.total > 0";
    $list = pdo_fetchall("SELECT goods.id,goods.name,SUM(og.total) as allcount FROM ".tablename('deamx_food_goods')." goods right JOIN ".tablename('deamx_food_order_goods')." og ON og.store_id=goods.store_id AND og.goodsid=goods.id AND og.createtime>=:starttime and og.createtime <=:endtime WHERE goods.uniacid=:uniacid ".$condition." group BY og.goodsid ORDER BY allcount DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':uniacid'=>$_W['uniacid'],':starttime'=>$starttime,':endtime'=>$endtime));
    $total = pdo_fetchall("SELECT goodsid FROM ".tablename('deamx_food_order_goods'). " WHERE uniacid=:uniacid AND store_id=:store_id AND createtime>=:starttime AND createtime <=:endtime AND total>0 group BY goodsid", array(':uniacid'=>$_W['uniacid'],':starttime'=>$starttime,':endtime'=>$endtime, ':store_id' => $store_id));


    $total = count($total);
    foreach ($list as &$row) {
        $row['allcount'] = intval($row['allcount']);
        $row['allcount'] = intval($row['allcount']);
        $row['perdaycount'] = ceil($row['allcount']/$daycount);
        $buyAgainArr = pdo_fetchall('SELECT COUNT(*) as percount FROM ' . tablename('deamx_food_order_goods') . " where uniacid=:uniacid and goodsid=:goodsid and createtime>=:starttime and createtime <=:endtime AND store_id=:store_id group by openid having percount>1",array(':uniacid'=>$_W['uniacid'],':goodsid'=>$row['id'],':starttime'=>$starttime,':endtime'=>$endtime,':store_id' => $store_id));
        $buyAgainTotal = 0;
        if(is_array($buyAgainArr)){
            foreach ($buyAgainArr as $value) {
                $buyAgainTotal = $buyAgainTotal + $value['percount'];
            }
        }
        $row['buyAgainCount'] = intval(count($buyAgainArr));
        if($row['buyAgainCount'] > 0){
            $row['perBuyAgainCount'] = ceil($buyAgainTotal/$row['buyAgainCount']);
        }else{
            $row['perBuyAgainCount'] = 0;
        }

    }
    if($method == 'export'){
        $html = "\xEF\xBB\xBF";
        $filter = array(
            'name' => '商品名称',
            'openid' => '总销量',
            'username' => '日均销量',
            'telphone' => '复购人次',
            'award_type' => '平均复购次数',
        );
        foreach ($filter as $title) {
            $html .= $title . "\t,";
        }
        $html .= "\n";
        foreach($list as $value){
            $html .= $value['name']. "\t, ";
            $html .= $value['allcount']. "\t, ";
            $html .= $value['perdaycount']. "\t, ";
            $html .= $value['buyAgainCount']. "\t, ";
            $html .= $value['perBuyAgainCount']. "\t, ";
            $html .= "\n";
        }
        header("Content-type:text/csv");
        header("Content-Disposition:attachment; filename=商品销售报表".$daterange['start']."至".$daterange['end'].".csv");
        echo $html;
        exit();
    }
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'detail'){
    $goodsid = intval($_GPC['id']);
    $goodsinfo = pdo_get("deamx_food_goods",array('uniacid'=>$_W['uniacid'],'id'=>$goodsid));
    $days = 30;
    $i = $days;
    $datas = array();
    while (0 <= $i) {
        $time = date('Y-m-d', strtotime('-' . $i . ' day'));
        $condition = ' and uniacid=:uniacid and store_id=:store_id and createtime>=:starttime and createtime<=:endtime and goodsid=:goodsid';
        $params = array(':uniacid' => $_W['uniacid'], ':starttime' => strtotime($time . ' 00:00:00'), ':endtime' => strtotime($time . ' 23:59:59'), ':store_id'=>$store_id,':goodsid'=>$goodsid);
        $order_count = pdo_fetchcolumn('select sum(total) from ' . tablename('deamx_food_order_goods') . ' where 1 ' . $condition, $params);
        $datas[] = array('date' => $time, 'order_count' => intval($order_count));

        --$i;
    }
}
include manage_template("store/statistics");
?>
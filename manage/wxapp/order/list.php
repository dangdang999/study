<?php
/**
 * 门店管理后台
 *
 * @author cmszs
 * @url
 */
defined('IN_IA') or exit('Access Denied');
class List_DeamFoodManage extends imfoxMember
{
    public function get_list(){
        global $_W,$_GPC;
        $store_id = intval($_GPC['store_id']);
        $status = $_GPC['status'];
        $timeType = intval($_GPC['timeType']);
        $chooseData = trim($_GPC['chooseData']);
        $orderType = trim($_GPC['orderType']);
        $pagesize = 10;
        $page = intval($_GPC['page']);
        $conditionStr = "";
        if($status == "all"){
            $conditionStr .= " AND status > '0'";
        }elseif($status == "wait"){
            $conditionStr .= " AND status = '1'";
        }elseif($status == "ing"){
            $conditionStr .= " AND (status = '2')";
        }elseif($status == "driving"){
            $conditionStr .= " AND (status = '4')";
        }elseif($status == "over"){
            $conditionStr .= " AND (status = '3')";
        }elseif($status == "cancel"){
            $conditionStr .= " AND (status = '-1')";
        }
        if(empty($timeType)){
            $conditionStr .= " AND paytime >= '" . strtotime(date("Y-m-d", strtotime("-2 days"))) . "'";
        }else{
            $conditionStr .= " AND paytime >= '" . strtotime($chooseData) . "' AND paytime < '" . (strtotime($chooseData) + 3600 * 24) . "'";
        }
        if($orderType == 'getself'){
            $conditionStr .= " AND order_type = '1'";
        }else{
            $conditionStr .= " AND order_type = '2'";
        }
        $list = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_order')." WHERE uniacid=:uniacid AND store_id=:store_id ".$conditionStr." ORDER BY id DESC LIMIT " . ($page - 1) * $pagesize . ',' . $pagesize,array(':uniacid'=>$_W['uniacid'], ':store_id' => $store_id));
        foreach ($list as &$row){
            $row['paytime'] = date("Y-m-d H:i", $row['paytime']);
            $type_count = 0;
            $row['goods_list'] = json_decode($row['goods_list'],true);
            $new_goods_list = array();
            foreach ($row['goods_list'] as &$goods_list) {
                $goods_list['img'] = pdo_getcolumn("deamx_food_goods",array('uniacid'=>$_W['uniacid'],'id'=>$goods_list['goodsid']), 'img',1);
                if(empty($goods_list['hasoption']) && $goods_list['count']>0){
                    $type_count ++;
                    $new_goods_list[] = array(
                        'name' => $goods_list['name'],
                        'count' => $goods_list['count'],
                        'price' => $goods_list['marketprice'],
                        'total_price' => $goods_list['totalprice']
                    );
                }elseif(!empty($goods_list['options']) && !empty($goods_list['hasoption'])){
                    foreach ($goods_list['options'] as $options) {
                        if($options['count']>0){
                            $new_goods_list[] = array(
                                'name' => $goods_list['name']."-".$options['name'],
                                'count' => $options['count'],
                                'price' => $options['marketprice'],
                                'total_price' => $options['price']
                            );
                            $type_count ++;
                        }
                    }
                }

            }
            $row['goods_list'] = $new_goods_list;
            $row['type_count'] = $type_count;
            if($row['order_type'] == '1'){
                if($row['status']=='1'){
                    $row['status_text'] = "等待接单";
                }elseif($row['status']=='2'){
                    $row['status_text'] = "制作中";
                }elseif($row['status']=='3'){
                    $row['status_text'] = "已完成";
                }elseif($row['status']=='5'){
                    $row['status_text'] = "订单已关闭";
                }elseif($row['status']=='-1'){
                    $row['status_text'] = "已取消";
                }
            }elseif($row['order_type'] == '2'){
                if($row['status']=='1'){
                    $row['status_text'] = "等待接单";
                }elseif($row['status']=='2'){
                    $row['status_text'] = "待配送";
                }elseif($row['status']=='3'){
                    $row['status_text'] = "已完成";
                }elseif($row['status']=='4'){
                    $row['status_text'] = "正在配送";
                }elseif($row['status']=='5'){
                    $row['status_text'] = "订单已关闭";
                }elseif($row['status']=='-1'){
                    $row['status_text'] = "已取消";
                }
                $row['address_info'] = @json_decode($row['address_info'],true);
                $row['address'] = $row['address_info']['realname'].' '.$row['address_info']['telphone'].' '.$row['address_info']['address'].' '.$row['address_info']['address_road'].' '.$row['address_info']['number'];
            }

        }
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('deamx_food_order') . " WHERE uniacid=:uniacid AND store_id=:store_id ".$conditionStr, array(':uniacid'=>$_W['uniacid'], ':store_id' => $store_id));
        $result['pagesize'] = $pagesize;
        $result['list'] = $list;
        $result['total'] = $total;
        show_json(1, $result);

    }
}
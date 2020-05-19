<?php
/**
 * Created by PhpStorm.
 * User: 南京狸先生信息技术有限公司
 * Date: 2020-01-10
 * Time: 14:36
 */
if( !defined("IN_IA") )
{
    exit( "Access Denied" );
}
class Detail extends ImFoxModule{
    public function getDetail() {
        global $_W, $_GPC;
        $order_id = intval($_GPC['order_id']);
        if (empty($_W['openid'])) {
            show_json(0,'请先登录');
        }
        $orderInfo = pdo_get("deamx_food_order",array('uniacid'=>$_W['uniacid'],'id'=>$order_id,'openid'=>$_W['openid']),array('id','goods_list','count','price','pay_price','status','remark','paytime','order_number','createtime','ordersn','store_id','order_type','pbox_fee','send_fee','use_coupon','coupon_price','need_send_coupon','is_send_coupon','enoughdeduct','getfood_time','pay_type','refund_fee'));
        if(empty($orderInfo)){
            show_json(0,'订单不存在或状态异常！');
        }
        $orderInfo['ticket_remark'] = "请留意服务员叫号";
        $orderInfo['paytime'] = date("Y-m-d H:i:s",$orderInfo['paytime']);
        $orderInfo['store'] = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$orderInfo['store_id']),array('id','name','telephone'));
        if ($orderInfo['getfood_time'] == '现在下单，稍后即取') {
            $orderInfo['getfood_time'] = "立即取餐";
        } else {
            $orderInfo['getfood_time'] = str_replace("取餐", "", $orderInfo['getfood_time']);
            $orderInfo['getfood_time'] = $orderInfo['getfood_time'] . "取餐";
        }
        $goodsList = json_decode($orderInfo['goods_list'], true);
        $orderInfo['new_order'] = 0;
        if (empty($goodsList)) {
            show_json(0,'订单异常！');
        }
        if (empty($goodsList[0]['goodsid'])) {
            //新版
            $orderInfo['new_order'] = 1;
        }
        //获取商品id
        $goodsIdArr = [];
        foreach ($goodsList as $key => $goods) {
            if ($orderInfo['new_order'] == 1) {
                //新版
                if(!in_array($goods['goodsId'], $goodsIdArr)) {
                    $goodsIdArr[] = $goods['goodsId'];
                }
            } else {
                //旧版
                if(!in_array($goods['goodsid'], $goodsIdArr)) {
                    $goodsIdArr[] = $goods['goodsid'];
                }
            }
        }
        $goodsArr = pdo_getall("deamx_food_goods", array('id' => $goodsIdArr, 'uniacid' => $_W['uniacid']), array('id', 'name', 'price', 'img'), 'id');
        $newGoodsList = array();
        foreach ($goodsList as $key => &$goods) {
            if ($orderInfo['new_order'] == 1) {
                //新版
                $goods['imgUrl'] = empty($goodsArr[$goods['goodsId']]['img']) ? '' : tomedia($goodsArr[$goods['goodsId']]['img']);
                $newGoodsList[$key] = $goods;
            } else {
                //旧版
                if ($goods['hasoption'] == '1' && $goods['count'] > 0) {
                    //有规格
                    foreach ($goods['options'] as $options) {
                        if ($options['count'] > 0) {
                            $newGoodsList[] = array(
                                'goodsId'       =>  $goods['goodsid'],
                                'name'          =>  $goods['name'],
                                'imgUrl'        =>  empty($goodsArr[$goods['goodsid']]['img']) ? '' : tomedia($goodsArr[$goods['goodsid']]['img']),
                                'count'         =>  $options['count'],
                                'optionName'    =>  $options['name'],
                                'optionsId'     =>  $options['id'],
                                'optionsRealId' =>  $options['id'],
                                'perPrice'      =>  $options['marketprice'],
                                'price'         =>  $options['marketprice'] * $options['count']
                            );
                        }
                    }
                } else {
                    if ($goods['count'] > 0) {
                        $newGoodsList[] = array(
                            'goodsId'       =>  $goods['goodsid'],
                            'name'          =>  $goods['name'],
                            'imgUrl'        =>  empty($goodsArr[$goods['goodsid']]['img']) ? '' : tomedia($goodsArr[$goods['goodsid']]['img']),
                            'count'         =>  $goods['count'],
                            'optionName'    =>  "",
                            'optionsId'     =>  0,
                            'optionsRealId' =>  0,
                            'perPrice'      =>  $goods['marketprice'],
                            'price'         =>  $goods['marketprice'] * $goods['count']
                        );
                    }
                }
            }
        }
        $orderInfo['goods_list'] = $newGoodsList;
        if($orderInfo['order_type'] == '2'){
            if($orderInfo['status'] == '0'){
                $orderInfo['status_text'] = STORE_STATUS0;
            }elseif($orderInfo['status'] == '1'){
                $orderInfo['status_text'] = STORE_STATUS1;
            }elseif($orderInfo['status'] == '2'){
                $orderInfo['status_text'] = STORE_STATUS2;
            }elseif($orderInfo['status'] == '3'){
                $orderInfo['status_text'] = STORE_STATUS3;
            }elseif($orderInfo['status'] == '4'){
                $orderInfo['status_text'] = STORE_STATUS4;
            }elseif($orderInfo['status'] == '5'){
                $orderInfo['status_text'] = STORE_STATUS5;
            }elseif($orderInfo['status'] == '-1'){
                $orderInfo['status_text'] = "商家取消";
            }

        }else{
            if($orderInfo['status'] == '0'){
                $orderInfo['status_text'] = "待付款";
            }elseif($orderInfo['status'] == '1'){
                $orderInfo['status_text'] = "等待接单";
            }elseif($orderInfo['status'] == '2'){
                $orderInfo['status_text'] = "制作中";
            }elseif($orderInfo['status'] == '3'){
                $orderInfo['status_text'] = "已完成";
            }elseif($orderInfo['status'] == '5'){
                $orderInfo['status_text'] = "已关闭";
            }elseif($orderInfo['status'] == '-1'){
                $orderInfo['status_text'] = "商家取消";
            }
        }
        if($orderInfo['pay_type'] == '1'){
            $orderInfo['pay_type'] = "微信支付";
        }else if($orderInfo['pay_type'] == '2'){
            $orderInfo['pay_type'] = "余额支付";
        }
        //更新卡券显示
        if($orderInfo['need_send_coupon'] > 0 && $orderInfo['is_send_coupon'] == 0){
            pdo_update("deamx_food_order",array('is_send_coupon'=>'1'),array('id'=>$order_id));
        }
        if (empty($orderInfo['store']['telephone'])){
            $orderInfo['store']['telephone'] = '';
        }
        show_json(1,$orderInfo);
    }
}
?>
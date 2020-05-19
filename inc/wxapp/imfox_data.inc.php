<?php
/**
 * 点餐小程序模块小程序接口定义
 *
 * @author imfox
 * @url
 */
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$operation = trim($_GPC['op']);
if($operation == 'cartInfo'){
    $cartJson = htmlspecialchars_decode($_GPC['cart']);
    $cartArr = json_decode($cartJson, true);
    $totalPrice = 0;
    $totalCount = 0;

    foreach ($cartArr as &$cart){

        if(empty($cart['hasoption'])){
            $cart['count'] = intval($cart['count']);
            if($cart['count'] > 0){
                $totalCount += $cart['count'];
                $goodsId = intval($cart['goodsid']);
                $goodsPrice = pdo_getcolumn("deamx_food_goods", array('uniacid' => $_W['uniacid'], 'id' => $goodsId), 'price');
                $cart['totalprice'] = number_format($cart['count'] * $goodsPrice, 2, '.', '');
                $totalPrice += $cart['totalprice'];
            }else{
                $cart['totalprice'] = 0;
            }

        }
        if(!empty($cart['options']) && !empty($cart['hasoption'])){
            $goodsId = intval($cart['goodsid']);
            $goodsInfo = pdo_get("deamx_food_goods", array('uniacid' => $_W['uniacid'], 'id' => $goodsId));
            foreach ($cart['options'] as &$options){
                $options['count'] = intval($options['count']);
                if($options['count'] > 0){
                    if($goodsInfo['old_option'] == '1'){
                        $totalCount += $options['count'];
                        $goodsOptionsId = intval($options['id']);
                        $goodsOptionPrice = pdo_getcolumn("deamx_food_goods_option", array('uniacid' => $_W['uniacid'], 'id' => $goodsOptionsId), 'marketprice');
                        $options['price'] = number_format($options['count'] * $goodsOptionPrice, 2, '.', '');
                        $totalPrice += $options['price'];
                    }else{
                        $totalCount += $options['count'];
                        $goodsOptionsId = intval($options['real_id']);
                        if(empty($goodsOptionsId)){
                            $goodsOptionPrice = $goodsInfo['price'];
                        }else{
                            $goodsOptionPrice = pdo_getcolumn("deamx_food_goods_option", array('uniacid' => $_W['uniacid'], 'id' => $goodsOptionsId), 'marketprice');
                        }
                        $options['price'] = number_format($options['count'] * $goodsOptionPrice, 2, '.', '');
                        $totalPrice += $options['price'];
                    }

                }else{
                    $options['price'] = 0;
                }

            }
        }

    }
    $cartJson = json_encode($cartArr);
    show_json(1, array('cart' => $cartArr, 'totalPrice' => number_format($totalPrice, 2, '.', ''), 'totalCount' => $totalCount));
}
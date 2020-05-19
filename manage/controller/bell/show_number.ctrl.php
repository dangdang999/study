<?php
defined('IN_IA') or exit('Access Denied');
$operation = empty($operation) ? 'display' : $operation;
if($operation == 'get_list'){

    $type0 = pdo_getall("deamx_food_order", array('order_number <>' => '', 'status' => '2', 'order_type' => '1', 'store_id' => $store_id, 'paytime >=' => TIMESTAMP - 3600 * 24 * 7), array('id', 'ordersn', 'order_number', 'is_get'), '', 'id desc', '0,20');
    $type1 = pdo_getall("deamx_food_order", array('order_number <>' => '', 'status' => '3', 'is_get' => '0', 'order_type' => '1', 'store_id' => $store_id), array('id', 'ordersn', 'order_number', 'is_get'), '', 'id desc', '0,20');
    show_json(1, array('type0' => $type0, 'type1' => $type1));
    //$list = pdo_getall("imfox_bbs_thread", $condition, array('id','title','content','is_vote','img_url_list','real_view','is_admin','is_score','is_top','createtime','nickname','board_id','good_count'),'',"is_top desc,top_createtime desc,id desc",($page - 1) * $pagesize . ',' . $pagesize);
    exit;
}
if($operation == 'deal'){
    $code = trim($_GPC['code']);
    $reg='#(\d+)#';
    preg_match($reg, $code,$m);
    $ordersn = "DM".$m[1];
    $orderInfo = pdo_get("deamx_food_order", array('ordersn' => $ordersn, 'store_id' => $store_id));
    if($orderInfo['order_type'] != '1'){
        exit();
    }
    if($orderInfo['status'] == '2'){
        pdo_update("deamx_food_order", array('status' => '3', 'is_get' => '0'), array('id' => $orderInfo['id']));
        if($orderInfo['desk_id'] == '0'){
            $settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']), array('template_status', 'template_id','takeout_template_id','get_food_template_id'));
            $storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$orderInfo['store_id']));
            if(!empty($settings['template_status']) && !empty($settings['get_food_template_id'])){
                load()->classs('wxapp.account');
                $accObj= WxappAccount::create($_W['uniacid']);
                $access_token = @$accObj->getAccessToken();
                $templateMessageArr = array(
                    'keyword1'=>array(
                        'value'	=>	$orderInfo['order_number'],
                        'color'	=>	'#000000',
                    ),
                    'keyword2'=>array(
                        'value'	=>	'自助点餐-即时单(自取)',
                        'color'	=>	'#000000',
                    ),
                    'keyword3'=>array(
                        'value'	=>	date("Y-m-d H:i:s", $orderInfo['paytime']),
                        'color'	=>	'#000000',
                    ),
                    'keyword4'=>array(
                        'value'	=>	$storeInfo['name'],
                        'color'	=>	'#000000',
                    ),
                    'keyword5'=>array(
                        'value'	=>	"您的餐点已准备好，请前往取餐区凭取餐号取餐",
                        'color'	=>	'#000000',
                    ),
                );
                $templateMessageUrl = "deam_food/pages/order/detail?id=".$orderInfo['id'];
                //获取有效的formId
                $formIdInfo = com('message')->getFormIdInfo($orderInfo['openid']);
                if(!empty($formIdInfo)){
                    $_template_id = $formIdInfo['form_id'];
                    $messageResult = wxappMessage($access_token,$orderInfo['openid'],$settings['get_food_template_id'],$templateMessageUrl,$_template_id,$templateMessageArr);
                    load()->func('logging');
                    logging_run($messageResult);
                    if($messageResult['errcode'] == '0'){
                        com('message')->deleteFormIdCount($formIdInfo['id']);
                    }
                }
            }
        }
        show_json(1);
    }elseif($orderInfo['status'] == '3' && $orderInfo['is_get'] == '0'){
        pdo_update("deamx_food_order", array('is_get' => '1'), array('id' => $orderInfo['id']));
        show_json(1);
    }

    exit;
}


include manage_template("bell/show_number");
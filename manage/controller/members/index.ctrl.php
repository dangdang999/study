<?php
defined('IN_IA') or exit('Access Denied');
$operation = empty($operation) ? 'display' : $operation;

if($operation == 'display'){
    $coupon_uniacid = $settings['coupon_uniacid'];
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = "";
    $keywords = trim($_GPC['keyword']);
    if(!empty($keywords)){
        $condition .= " AND (fans.nickname like '%".$keywords."%' OR members.realname like '%".$keywords."%' OR members.telephone like '%".$keywords."%')";
    }
    $list = pdo_fetchall("SELECT members.id as member_id,fans.openid,fans.nickname,members.credit1,members.credit2,members.realname,members.telephone,fans.tag FROM ".tablename("mc_mapping_fans")." fans RIGHT JOIN ".tablename('deamx_food_members')." members ON members.openid = fans.openid WHERE fans.uniacid=:uniacid ".$condition." ORDER BY fans.fanid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':uniacid'=>$_W['uniacid']));

    foreach ($list as &$row) {
        if(!empty($row['tag'])){
            $row['tag'] = iunserializer(base64_decode($row['tag']));
        }
    }
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename("mc_mapping_fans")." fans RIGHT JOIN ".tablename('deamx_food_members')." members ON members.openid = fans.openid WHERE fans.uniacid=:uniacid ".$condition." ORDER BY fans.fanid DESC",array(':uniacid'=>$_W['uniacid']));

    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'credit'){
    $type = trim($_GPC['type']);
    $num = floatval($_GPC['num']);
    $uid = intval($_GPC['uid']);
    if(empty($uid)){
        exit("会员不存在！");
    }
    $names = array('credit1' => "积分", 'credit2' => "余额");

    $credits = m('member')->credit_fetch($uid);

    if($num < 0 && abs($num) > $credits[$type]) {
        exit("会员账户{$names[$type]}不够");
    }
    if($type == 'credit1'){
        //积分
        $num = intval($num);
    }
    $remark = trim($_GPC['remark']);
    if(empty($remark)){
        $remark = "后台操作";
    }
    $status = m('member')->credit_update($uid, $type, $num, array($_W['user']['uid'], $remark, 'system', $_W['user']['clerk_id'], $_W['user']['store_id'], $_W['user']['clerk_type']));
    if(is_error($status)) {
        exit($status['message']);
    }

    exit('success');
}elseif($operation == 'edit_field'){
    $uid = intval($_GPC['uid']);
    $field = trim($_GPC['field']);
    $value = trim($_GPC['value']);
    $member = pdo_get("deamx_food_members", array('uniacid' => $_W['uniacid'], 'id' => $uid));
    if(empty($member)){
        show_json(0, "用户不存在！");
    }
    $fieldArr = array("realname","telephone");
    if(in_array($field, $fieldArr)){
        pdo_update("deamx_food_members", array($field => $value), array('id' => $member['id']));
        show_json(1, "修改成功！");
    }else{
        show_json(0, "未知字段！");
    }
    exit();
}elseif($operation == 'detail'){
    $method = empty($_GPC['method']) ? 'basic' : trim($_GPC['method']);
    $uid = intval($_GPC['id']);
    $userInfo = pdo_fetch("SELECT members.id as member_id,fans.uid,fans.openid,fans.nickname,members.credit1,members.credit2,members.realname,members.telephone FROM ".tablename("mc_mapping_fans")." fans RIGHT JOIN ".tablename('deamx_food_members')." members ON members.openid = fans.openid WHERE fans.uniacid=:uniacid AND members.id=:id ORDER BY fans.fanid DESC",array(':uniacid'=>$_W['uniacid'],':id'=>$uid));
    if($method == 'order'){
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $list = pdo_fetchall("SELECT o.id,o.ordersn,o.goods_list,o.count,o.price,o.pay_price,o.status,o.paytime,o.order_type,s.name as store_name,o.pay_type,o.refund_fee FROM ".tablename('deamx_food_order')." o INNER JOIN ".tablename('deamx_food_store')." s ON o.store_id=s.id WHERE o.uniacid=:uniacid and o.status > '0' and o.openid=:openid ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':uniacid'=>$_W['uniacid'],':openid'=>$userInfo['openid']));
        foreach ($list as &$row) {
            $row['paytime'] = date("Y-m-d H:i:s",$row['paytime']);
            //状态和样式
            if($row['order_type'] == '1'){
                if($row['status'] == '0'){
                    $row['status_text'] = "待付款";
                }elseif($row['status'] == '1'){
                    $row['status_text'] = "等待接单";
                }elseif($row['status'] == '2'){
                    $row['status_text'] = "制作中";
                }elseif($row['status'] == '3'){
                    $row['status_text'] = "已完成";
                }elseif($row['status'] == '5'){
                    $row['status_text'] = "已关闭";
                }
            }elseif($row['order_type'] == '2'){
                if($row['status'] == '0'){
                    $row['status_text'] = "待付款";
                }elseif($row['status'] == '1'){
                    $row['status_text'] = "等待接单";
                }elseif($row['status'] == '2'){
                    $row['status_text'] = "等待配送";
                }elseif($row['status'] == '3'){
                    $row['status_text'] = "已完成";
                }elseif($row['status'] == '4'){
                    $row['status_text'] = "正在配送";
                }elseif($row['status'] == '5'){
                    $row['status_text'] = "已关闭";
                }
            }
            if($row['refund_fee'] > 0){
                $row['status_text'] = $row['status_text']."(已退款)";
            }
        }
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('deamx_food_order')." o WHERE o.uniacid=:uniacid and o.status > '0' and o.openid=:openid ".$condition." ORDER BY id DESC",array(':uniacid'=>$_W['uniacid'],':openid'=>$userInfo['openid']));
        $pager = pagination($total, $pindex, $psize);

    }elseif($method == 'credit1' || $method == 'credit2'){
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $list = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_credits_record')." WHERE uniacid=:uniacid AND uid=:uid AND credittype=:credittype ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':uniacid'=>$_W['uniacid'],':uid'=>$userInfo['member_id'],':credittype'=>$method));
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('deamx_food_credits_record')." WHERE uniacid=:uniacid AND uid=:uid AND credittype=:credittype ORDER BY id DESC",array(':uniacid'=>$_W['uniacid'],':uid'=>$userInfo['member_id'],':credittype'=>$method));
        $pager = pagination($total, $pindex, $psize);
    }

}
include manage_template("members/list");
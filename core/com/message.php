<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

class Message_DeamFoodComModel{
    //模板消息通知
    public function saveFormId($formId, $openid, $count = '1'){
        global $_W;

        $insertArr = array(
            'uniacid'       =>  $_W['uniacid'],
            'form_id'       =>  $formId,
            'openid'        =>  $openid,
            'last_count'    =>  $count,
            'createtime'    =>  TIMESTAMP
        );
        $result = pdo_insert("deamx_food_notify_id", $insertArr);
        //删除过期||用完的
        pdo_delete('deamx_food_notify_id', array('last_count <=' => '0', 'createtime <' => (TIMESTAMP - 167 * 3600)), 'OR');
        return $result;
    }
    public function getFormIdInfo($openid){
        global $_W;
        $formIdInfo = pdo_fetch("SELECT * FROM " . tablename('deamx_food_notify_id') . " WHERE uniacid=:uniacid AND openid=:openid AND createtime>=:createtime AND last_count>:last_count ORDER BY id ASC", array(':uniacid' => $_W['uniacid'], ':openid' => $openid, ':createtime' => TIMESTAMP - 167 * 3600, ':last_count' => '0'));
        return $formIdInfo;
    }
    public function deleteFormIdCount($id){
        global $_W;
        $formIdInfo = pdo_get("deamx_food_notify_id", array('uniacid' => $_W['uniacid'], 'id' => $id));
        if(!empty($formIdInfo)){
            if($formIdInfo['last_count'] <= '1'){
                pdo_delete("deamx_food_notify_id", array('id' => $id));
            }else{
                pdo_update("deamx_food_notify_id", array('last_count -=' => '1'), array('id' => $id));
            }
            return true;
        }else{
            return "not fond";
        }
    }
}
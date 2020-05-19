<?php
/**
 * 门店管理后台
 *
 * @author cmszs
 * @url
 */
defined('IN_IA') or exit('Access Denied');
class Index_DeamFoodManage extends DeamFoodManage
{
    public function info(){
        global $_W,$_GPC;
        $storeId = intval($_GPC['storeId']);
        $storeInfo = pdo_get("deamx_food_store",array('uniacid' => $_W['uniacid'],'id' => $storeId), array('id','name','starttime','endtime','takeout_open_time','remark_text','auto_order'));
        if(!empty($storeInfo['takeout_open_time'])){
            $storeInfo['takeout_open_time'] = iunserializer($storeInfo['takeout_open_time']);
        }
        show_json(1, array('store_info' => $storeInfo));
    }
    public function post(){
        global $_W,$_GPC;
        $postInfo = htmlspecialchars_decode($_GPC['storeInfo']);
        $postInfoArr = json_decode($postInfo, true);
        $storeId = $postInfoArr['id'];
        $postInfoArr['takeout_open_time'] = iserializer($postInfoArr['takeout_open_time']);
        unset($postInfoArr['id']);
        pdo_update("deamx_food_store", $postInfoArr, array('id' => $storeId, 'uniacid' => $_W['uniacid']));
        show_json(1, "保存成功！");
    }
}
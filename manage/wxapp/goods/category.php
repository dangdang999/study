<?php
/**
 * 门店管理后台
 *
 * @author cmszs
 * @url
 */
defined('IN_IA') or exit('Access Denied');
class Category_DeamFoodManage extends imfoxMember
{
    public function post(){
        global $_W,$_GPC;
        $cateId = intval($_GPC['cate_id']);
        $store_id = intval($_GPC['store_id']);
        $cateName = trim($_GPC['name']);
        $postArr = array(
            'uniacid'		=>	$_W['uniacid'],
            'store_id'		=>	$store_id,
            'sortid'		=>	'0',
            'classname'		=>	$cateName,
            'enabled'		=>	intval($_GPC['enabled']),
            'createtime'	=>	TIMESTAMP
        );
        if(!empty($cateId)){
            unset($postArr['createtime']);
            $result = pdo_update("deamx_food_class",$postArr,array('uniacid' => $_W['uniacid'],'store_id' => $store_id,'id' => $cateId));
        }else{
            $postArr['enabled'] = '1';
            $result = pdo_insert("deamx_food_class",$postArr);
        }
        show_json(1);
    }
    public function delete(){
        global $_W,$_GPC;
        $cateId = intval($_GPC['cate_id']);
        $store_id = intval($_GPC['store_id']);
        pdo_delete("deamx_food_class", array('uniacid' => $_W['uniacid'],'store_id' => $store_id,'id' => $cateId));
        show_json(1);
    }
    public function get_list(){
        global $_W,$_GPC;
        $store_id = intval($_GPC['store_id']);
        $condition = ' and store_id='.$store_id;
        $list = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_class')." WHERE uniacid=:uniacid ".$condition." ORDER BY sortid DESC, id DESC",array(':uniacid'=>$_W['uniacid']));
        show_json(1, array('list' => $list, 'count' => count($list)));
    }
    public function move(){
        global $_W,$_GPC;
        $cateJson = htmlspecialchars_decode(trim($_GPC['cateArr']));
        $cateArr = json_decode($cateJson, true);
        $store_id = intval($_GPC['store_id']);
        foreach ($cateArr as $cate) {
            pdo_update('deamx_food_class', array(
                'sortid' => $cate['sortid']
            ), array(
                'id' => $cate['id'],
                'store_id'=>$store_id
            ));
        }
        show_json(1);
    }
    public function get_category(){
        global $_W,$_GPC;
        $store_id = intval($_GPC['store_id']);
        $cate_id = intval($_GPC['id']);
        $categoryInfo = pdo_get("deamx_food_class", array('id' => $cate_id, 'store_id' => $store_id, 'uniacid' => $_W['uniacid']));
        show_json(1, array('category_info' => $categoryInfo));
    }
}
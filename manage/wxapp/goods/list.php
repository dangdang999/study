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
        $pagesize = 10;
        $page = intval($_GPC['page']);
        $cate_id = intval($_GPC['cate_id']);
        $condition = array('uniacid'=>$_W['uniacid'], 'store_id' => $store_id);
        if($status != ""){
            $condition['status'] = $status;
        }
        if(!empty($cate_id)){
            $condition['class_id'] = $cate_id;
        }
        $list = pdo_getall("deamx_food_goods", $condition, '', '', "id desc", ($page - 1) * $pagesize . ',' . $pagesize);
        foreach ($list as &$row){
            $row['classname'] = pdo_getcolumn("deamx_food_class", array('id' => $row['class_id']), "classname");
            if(!empty($row['img'])){
                $row['img'] = tomedia($row['img']);
            }else{
                $row['img'] = MODULE_URL . "static/images/no-img.jpg";
            }
        }
        $total = pdo_getcolumn("deamx_food_goods", $condition, "COUNT(*)");
        $result['pagesize'] = $pagesize;
        $result['list'] = $list;
        $result['total'] = $total;
        show_json(1, $result);

    }
    public function event(){
        global $_W,$_GPC;
        $event = trim($_GPC['event']);
        $goodsId = intval($_GPC['goods_id']);
        if($event == 'changeStatus'){
            $goodsInfo = pdo_get("deamx_food_goods", array('id' => $goodsId, 'uniacid' => $_W['uniacid']));
            if(empty($goodsInfo)){
                show_json(0, "操作失败！");
            }
            $toStatus = $goodsInfo['status'] == '1' ? '0' : '1';
            pdo_update("deamx_food_goods", array('status' => $toStatus), array('id' => $goodsInfo['id']));
            show_json(1, "操作成功！");
        }
    }
    public function get_info(){
        global $_W,$_GPC;
        $goodsId = intval($_GPC['id']);
        $store_id = intval($_GPC['store_id']);
        $goods = pdo_get("deamx_food_goods",array('uniacid' => $_W['uniacid'], 'id'=>$goodsId, 'store_id' => $store_id));
        $condition = ' and store_id='.$store_id;
        $categoryList = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_class')." WHERE uniacid=:uniacid ".$condition." ORDER BY sortid DESC, id DESC",array(':uniacid'=>$_W['uniacid']));
        $chooseCategory = "0";
        foreach ($categoryList as $index => $row){
            if($goods['class_id'] == $row['id']){
                $chooseCategory = $index;
            }
        }
        if(!empty($goods)){
            if(!empty($goods['img'])){
                $goods['imgUrl'] = tomedia($goods['img']);
            }
            $goods['goods_attr'] = iunserializer($goods['goods_attr']);
            $specsArr = pdo_fetchall("SELECT id,title as name,marketprice as price,stock FROM ".tablename('deamx_food_goods_option')." WHERE uniacid=:uniacid AND is_new = '1' AND goodsid=:goodsid ORDER BY displayorder ASC, id ASC",array(':uniacid'=>$_W['uniacid'], ':goodsid' => $goodsId));
            if(empty($specsArr)){
                $goods['goods_specs'] = array();
            }else{
                $goods['goods_specs'][0] = array(
                    'title'     =>  $goods['goods_specs_title'],
                    'options'   =>  $specsArr
                );
            }

        }else{
            $goods = array(
                'name'          =>  '',
                'goods_specs'   =>  array(),
                'goods_attr'    =>  array(),
                'intro'         =>  '',
                'displayorder'  =>  '0'
            );
        }
        show_json(1, array('goodsInfo' => $goods, 'categoryList' => $categoryList, 'chooseCategory' => $chooseCategory));
    }
    public function post(){
        global $_W,$_GPC;
        $store_id = intval($_GPC['store_id']);
        $dataJson = htmlspecialchars_decode($_GPC['data']);
        $dataArr = json_decode($dataJson, true);
        unset($dataArr['imgUrl'], $dataArr['createtime'], $dataArr['realsales']);

        $dataArr['goods_attr'] = iserializer($dataArr['goods_attr']);
        $dataArr['old_option'] = '0';
        $goods_specs = $dataArr['goods_specs'];
        if(empty($goods_specs)){
            $goods_specs = array();
        }
        unset($dataArr['goods_specs']);
        $options = $goods_specs[0]['options'];
        if(empty($options)){
            $options = array();
        }
        $dataArr['goods_specs_title'] = $goods_specs[0]['title'];
        if(!empty($options) || !empty($dataArr['goods_attr'])){
            $dataArr['hasoption'] = '1';
        }else{
            $dataArr['hasoption'] = '0';
        }
        if(!empty($dataArr['id'])){
            //编辑
            $goodsId = $dataArr['id'];
            unset($dataArr['id']);

            pdo_update("deamx_food_goods", $dataArr, array('id' => $goodsId, 'uniacid' => $_W['uniacid'], 'store_id' => $store_id));

        }else{
            $dataArr['uniacid'] = $_W['uniacid'];
            $dataArr['store_id'] = $store_id;
            $dataArr['status'] = '1';
            pdo_insert("deamx_food_goods", $dataArr);
            $goodsId = pdo_insertid();
        }
        $alreadyOption = array();
        foreach ($options as $index => $option){
            $alreadyOption[] = $option['id'];
        }
        $specsArr = pdo_fetchall("SELECT id FROM ".tablename('deamx_food_goods_option')." WHERE uniacid=:uniacid AND is_new = '1' AND goodsid=:goodsid ORDER BY displayorder ASC, id ASC",array(':uniacid'=>$_W['uniacid'], ':goodsid' => $goodsId));
        foreach ($specsArr as $specs){
            if(!in_array($specs['id'], $alreadyOption)){
                pdo_delete("deamx_food_goods_option", array('uniacid' => $_W['uniacid'], 'goodsid' => $goodsId, 'store_id' => $store_id, 'id' => $specs['id']));
            }
        }
        foreach ($options as $index => $option){
            $insertArr = array(
                'uniacid'       =>  $_W['uniacid'],
                'store_id'      =>  $store_id,
                'goodsid'       =>  $goodsId,
                'title'         =>  trim($option['name']),
                'marketprice'   =>  $option['price'],
                'stock'         =>  intval($option['stock']),
                'displayorder'  =>  $index,
                'specs'         =>  '',
                'realsales'     =>  '0',
                'is_new'        =>  '1'
            );
            if(!empty($insertArr['title'])){
                if(empty($option['id'])){
                    pdo_insert("deamx_food_goods_option", $insertArr);
                }else{

                    pdo_update("deamx_food_goods_option", $insertArr, array('id' => $option['id']));
                }
            }

        }
        show_json(1, "保存成功！");
    }
}
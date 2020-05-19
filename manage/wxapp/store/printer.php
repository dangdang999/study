<?php
/**
 * 门店管理后台
 *
 * @author cmszs
 * @url
 */
defined('IN_IA') or exit('Access Denied');
class Printer_DeamFoodManage extends imfoxMember
{
    public function get_list(){
        global $_W, $_GPC;
        $store_id = intval($_GPC['store_id']);
        $condition = ' and store_id='.$store_id;
        $list = pdo_fetchall("SELECT * FROM " . tablename('deamx_food_printer') . " WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY id DESC");
        show_json(1, array('list' => $list, 'count' => count($list)));
    }
    public function get_info(){
        global $_W, $_GPC;
        $store_id = intval($_GPC['store_id']);
        $id = intval($_GPC['id']);
        $printerInfo = array();
        if(!empty($id)){
            $printerInfo = pdo_get("deamx_food_printer", array('uniacid' => $_W['uniacid'], 'store_id' => $store_id, 'id' => $id));
            $printerInfo['print_data'] = @json_decode($printerInfo['print_data']);
            $printerInfo['print_class'] = @json_decode($printerInfo['print_class'],true);
        }
        $class = pdo_getall("deamx_food_class", array('uniacid' => $_W['uniacid'], 'store_id' => $store_id),array('id', 'classname'));
        foreach ($class as &$row){
            $row['checked'] = (empty($printerInfo['print_class']) || in_array($row['id'], $printerInfo['print_class'])) ? true : false;
        }
        show_json(1, array('pinter_info' => $printerInfo, 'class_list' => $class));
    }
    public function post(){
        global $_W, $_GPC;
        $store_id = intval($_GPC['store_id']);
        $dataJson = htmlspecialchars_decode($_GPC['data']);
        $dataArr = json_decode($dataJson, true);
        $class_list = htmlspecialchars_decode($_GPC['class_list']);
        $class_list = json_decode($class_list, true);
        $choose_class = array();
        foreach ($class_list as $row){
            if ($row['checked'] == true){
                $choose_class[] = $row['id'];
            }
        }
        $dataArr['status'] == intval($dataArr['status']);
        $choose_printer = intval($_GPC['choose_printer']);
        if($choose_printer == '0'){
            $dataArr['type'] = '1';
        }elseif($choose_printer == '1'){
            $dataArr['type'] = '4';
        }elseif($choose_printer == '2'){
            $dataArr['type'] = '2';
        }
        $dataArr['print_type'] = intval($dataArr['print_type']);
        $dataArr['print_data'] = json_encode($dataArr['print_data']);
        $dataArr['print_class'] = json_encode($choose_class);
        $dataArr['uniacid'] = $_W['uniacid'];
        $dataArr['store_id'] = $store_id;
        if(empty($dataArr['id'])){
            $dataArr['createtime'] = TIMESTAMP;
            pdo_insert('deamx_food_printer', $dataArr);
            $id = pdo_insertid();
        }else{
            pdo_update("deamx_food_printer", $dataArr, array("id" => $dataArr['id'], 'store_id' => $store_id));
        }
        show_json(1);
    }

    public function delete(){
        global $_W, $_GPC;
        $store_id = intval($_GPC['store_id']);
        $id = intval($_GPC['id']);
        pdo_delete("deamx_food_printer",array('uniacid' => $_W['uniacid'], 'id' => $id, 'store_id' => $store_id));
        show_json(1);
    }
}
<?php
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$uniacid = $_W['uniacid'];
$operation = empty($_GPC['op']) ? 'display' : trim($_GPC['op']);
$settings = pdo_get("deamx_food_settings",array('uniacid'=>$uniacid));
$deam_frames = array(
	array(
		'title'	=>	'门店列表',
		'url'	=>	$this->createWebUrl('store',array('op'=>'display')),
		'active'=>	$operation == 'display' ? 'active' : '',
	),
	array(
		'title'	=>	'新增门店',
		'url'	=>	$this->createWebUrl('store',array('op'=>'post')),
		'active'=>	($operation == 'post' && empty($_GPC['id'])) ? 'active' : '',
	),
	array(
		'title'	=>	'编辑门店',
		'url'	=>	"javascript:;",
		'active'=>	($operation == 'post' && !empty($_GPC['id'])) ? 'active' : '',
		'is_hide'=>'1'
	),
);

if($operation == 'display'){
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = '';
	$list = pdo_fetchall("SELECT * FROM " . tablename('deamx_food_store') . " WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('deamx_food_store') . " WHERE uniacid = '{$_W['uniacid']}' $condition");
	$pager = pagination($total, $pindex, $psize);
}elseif($operation == 'post'){
	load()->func('communication');
	load()->classs('wxapp.account');
	$accObj= WxappAccount::create($_W['account']['acid']);
	$access_token = $accObj->getAccessToken();
	if(empty($access_token)){
		itoast("请先配置小程序参数！",$this->createWebUrl('store',array('version_id'=>intval($_GPC['version_id']))),'error');
	}
	if (!is_writable(MODULE_ROOT."/data")) {
	    itoast("请先将目录".MODULE_ROOT."/data设置为可写(777)状态",$this->createWebUrl('store',array('version_id'=>intval($_GPC['version_id']))),'error');
	}
	$id = intval($_GPC['id']);
    load()->model('wxapp');
    //$pay_setting = wxapp_payment_param();
    //print_r($pay_setting);
	if(!empty($id)){
		$store = pdo_get("deamx_food_store",array('uniacid'=>$uniacid,'id'=>$id));

		if(empty($store)){
            itoast("门店不存在或已被删除！",$this->createWebUrl('store',array('version_id'=>intval($_GPC['version_id']))),'error');
        }
        $store['takeout_open_time'] = iunserializer($store['takeout_open_time']);
		if(!empty($store['payment'])){
            $store['payment'] = iunserializer($store['payment']);
        }
        $storeInfo = $store;
	}else{
		//获取最多可创建多少个门店
		if(!empty($settings['store_count'])){
			//获取已创建门店数量
			$storeCount = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('deamx_food_store')." WHERE uniacid=:uniacid",array(':uniacid'=>$_W['uniacid']));
			if($storeCount >= $settings['store_count']){
				itoast("门店数量达到上限，可联系管理员增加数量！",$this->createWebUrl('store',array('version_id'=>intval($_GPC['version_id']))),'error');
			}
		}
        $store['takeout_open_time']['starttime'] = "";
        $store['takeout_open_time']['endtime'] = "";
	}
	if(checksubmit()){
		$store = $_GPC['store'];
		if($store['enoughmoney']>0 && $store['enoughdeduct']>0){
			if($store['enoughmoney']<=$store['enoughdeduct']){
				itoast("满减设置最低消费必须大于减免金额","",'error');
			}
		}

		$postArr = array(
			'uniacid'				=>	$uniacid,
			'name'					=>	trim($store['name']),
			'province'				=>	trim($store['province']),
			'city'					=>	trim($store['city']),
			'district'				=>	trim($store['district']),
			'district_edit_self'	=>	intval($store['district_edit_self']),
			'address'				=>	trim($store['address']),
			'longitude'				=>	trim($store['longitude']),
			'latitude'				=>	trim($store['latitude']),
			'status'				=>	intval($store['status']),
			'is_getself'			=>	intval($store['is_getself']),
			'is_takeout'			=>	intval($store['is_takeout']),
			'close_reason'			=>	trim($store['close_reason']),
			'starttime'				=>	trim($store['starttime']),
			'endtime'				=>	trim($store['endtime']),
			'bg_color'				=>	trim($store['bg_color']),
			'fg_color'				=>	trim($store['fg_color']),
			'imgs'					=>	iserializer($store['imgs']),
			'remark_text'			=>	trim($store['remark_text']),
			'auto_order'			=>	intval($store['auto_order']),
			'notice_tel'			=>	trim($store['notice_tel']),
			'start_price'			=>	@number_format($store['start_price'],2,".",""),
			'send_limit'			=>	@number_format($store['send_limit'],2,".",""),
			'send_fee'				=>	@number_format($store['send_fee'],2,".",""),
			'deliver_type'			=>	intval($store['deliver_type']),
			'deliver_dada_shopno'	=>	trim($store['deliver_dada_shopno']),
			'deliver_dada_citycode'	=>	trim($store['deliver_dada_citycode']),
			'enoughmoney'			=>	@number_format($store['enoughmoney'],2,".",""),
			'enoughdeduct'			=>	@number_format($store['enoughdeduct'],2,".",""),
			'createtime'			=>	TIMESTAMP,
            'telephone'             =>  trim($store['telephone']),
		);
        $postArr['takeout_open_time'] = iserializer($store['takeout_open_time']);

        //单独设置微信支付
        if($store['payment']['status'] == '1'){
            //load()->classs('uploadedfile');
//            $files = UploadedFile::createFromGlobal();
//            $cert = isset($files['cert']) ? $files['cert'] : null;
//            $private_key = isset($files['key']) ? $files['key'] : null;
//            $cert_content = $storeInfo['payment']['wechat_refund']['cert'];
//            $private_key_content = $storeInfo['payment']['wechat_refund']['key'];
//            if($cert && $cert->isOk()) {
//                $cert_content = $cert->getContent();
//                $cert_content = authcode($cert_content, 'ENCODE');
//            }
//
//            if($private_key && $private_key->isOk()) {
//                $key_content = $private_key->getContent();
//                $private_key_content = authcode($key_content, 'ENCODE');
//            }
            if(empty($store['payment']['wechat']['mchid'])) {
                itoast('请填写支付商户号', '', 'info');
            }
//            if(empty($store['payment']['wechat']['signkey'])) {
//                itoast('请填写支付密钥', '', 'info');
//            }
//            if(! $cert_content) {
//                itoast('请上传apiclient_cert.pem证书', '', 'info');
//            }
//            if(! $cert_content) {
//                itoast('请上传apiclient_cert.pem证书', '', 'info');
//            }
//
//            if(! $private_key_content) {
//                itoast ('请上传apiclient_key.pem证书', '', 'info');
//            }
            //$store['payment']['wechat_refund'] = array('cert'=>$cert_content,'key'=>$private_key_content, 'switch'=>1, 'version'=>1);
            $store['payment'] = iserializer($store['payment']);
            $postArr['payment'] = $store['payment'];
        }else{
            $store['payment'] = iserializer($store['payment']);
            $postArr['payment'] = $store['payment'];
        }



		if(empty($id)){
			$result = pdo_insert("deamx_food_store",$postArr);
			$store_id = pdo_insertid();
		}else{
			unset($postArr['createtime']);
			$result = pdo_update("deamx_food_store",$postArr,array('uniacid'=>$uniacid,'id'=>$id));
			$store_id = $id;
		}
		if(empty($store['wxacode'])){//未生成门店小程序码
			$wxacodeUrl = "https://api.weixin.qq.com/wxa/getwxacode?access_token=".$access_token;
			$path = 'deam_food/pages/store/detail?store_id='.$store_id;
			$postArr = array(
				'path'			=>	$path,
				'width'			=>	800,
				'auto_color'	=>	empty($settings['wxacode_color']) ? true : false
			);
			$postJson = json_encode_ex($postArr);
			$wxacodeResult = ihttp_post($wxacodeUrl,$postJson);
			$wxacode = $wxacodeResult['content'];
			$wxacodeInfo = json_decode($wxacode,true);
			if(empty($wxacodeInfo['errcode'])){
				$wxacode_name = 'imfox_desk_'.md5($path).".jpg";
				$wxacode_dir = MODULE_ROOT."/data/wxacode/{$_W['uniacid']}";
				if (!file_exists($wxacode_dir)){
				    @mkdir($wxacode_dir,0777,true);
				}
				$wxacode_status = @file_put_contents($wxacode_dir."/".$wxacode_name, $wxacode);
			}
			if(!empty($wxacode_status)){//成功写入小程序码
				pdo_update("deamx_food_store",array('wxacode'=>"{$_W['uniacid']}/".$wxacode_name),array('uniacid'=>$uniacid,'id'=>$store_id));
			}
		}
		itoast(empty($id)?'门店添加成功！':'门店更新成功！',$this->createWebUrl('store',array('version_id'=>intval($_GPC['version_id']))),'success');

	}
}elseif($operation == 'copy'){
	$id = intval($_GPC['id']);
	$store = pdo_get("deamx_food_store",array('uniacid'=>$uniacid,'id'=>$id));
	empty($store) && itoast("门店不存在或已被删除！",$this->createWebUrl('store',array('version_id'=>intval($_GPC['version_id']))),'error');
	//获取最多可创建多少个门店
	if(!empty($settings['store_count'])){
		//获取已创建门店数量
		$storeCount = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('deamx_food_store')." WHERE uniacid=:uniacid",array(':uniacid'=>$_W['uniacid']));
		if($storeCount >= $settings['store_count']){
			itoast("门店数量达到上限，可联系管理员增加数量！",$this->createWebUrl('store',array('version_id'=>intval($_GPC['version_id']))),'error');
		}
	}
	//复制门店数据表
	$sql = "INSERT INTO ".tablename('deamx_food_store')."(uniacid,name,province,city,district,address,longitude,latitude,status,is_getself,is_takeout,close_reason,starttime,endtime,bg_color,fg_color,imgs,operator,notice_tel,remark_text,auto_order,wxacode,start_price,send_limit,send_fee,createtime) SELECT uniacid,REPLACE(name,name,CONCAT(name,'-".random(5)."')),province,city,district,address,longitude,latitude,REPLACE(status,status,'2'),is_getself,is_takeout,close_reason,starttime,endtime,bg_color,fg_color,imgs,operator,notice_tel,remark_text,auto_order,REPLACE(wxacode,wxacode,''),start_price,send_limit,send_fee,REPLACE(createtime,createtime,'".TIMESTAMP."') FROM ".tablename('deamx_food_store')." WHERE id='".$id."';";
	$result = pdo_query($sql);
	$new_storeid = pdo_insertid();
	//复制商品分类
	$all_class = pdo_getall("deamx_food_class",array('uniacid'=>$uniacid,'store_id'=>$id));
	foreach ($all_class as $key => $classItem) {
		$class_sql = "INSERT INTO ".tablename('deamx_food_class')."(uniacid,store_id,sortid,classname,enabled,createtime) SELECT uniacid,REPLACE(store_id,store_id,'".$new_storeid."'),sortid,classname,enabled,REPLACE(createtime,createtime,'".TIMESTAMP."') FROM ".tablename('deamx_food_class')." WHERE id='".$classItem['id']."';";
		$class_result = pdo_query($class_sql);
		$new_classid = pdo_insertid();
		//获取该分类下老的商品列表
		$goods_list = pdo_getall("deamx_food_goods",array('uniacid'=>$uniacid,'store_id'=>$id,'class_id'=>$classItem['id']));
		foreach ($goods_list as $key2 => $goodsItem) {
			$goods_sql = "INSERT INTO ".tablename('deamx_food_goods')."(uniacid,store_id,displayorder,name,intro,class_id,price,is_pbox,pbox_price,unit,img,hasoption,status,createtime,realsales) SELECT uniacid,REPLACE(store_id,store_id,'".$new_storeid."'),displayorder,name,intro,REPLACE(class_id,class_id,'".$new_classid."'),price,is_pbox,pbox_price,unit,img,hasoption,status,REPLACE(createtime,createtime,'".TIMESTAMP."'),REPLACE(realsales,realsales,'0') FROM ".tablename('deamx_food_goods')." WHERE id='".$goodsItem['id']."';";
			$goods_result = pdo_query($goods_sql);
			$new_goodsid = pdo_insertid();
			//获取老的商品规格
			$spec_list = pdo_getall("deamx_food_goods_spec",array('uniacid'=>$uniacid,'store_id'=>$id,'goodsid'=>$goodsItem['id']));
			$spec_check = array();
			foreach ($spec_list as $key3 => $specItem) {
				$spec_sql = "INSERT INTO ".tablename('deamx_food_goods_spec')."(uniacid,store_id,goodsid,title,description,displaytype,content,displayorder,propId) SELECT uniacid,REPLACE(store_id,store_id,'".$new_storeid."'),REPLACE(goodsid,goodsid,'".$new_goodsid."'),title,description,displaytype,REPLACE(content,content,''),displayorder,propId FROM ".tablename('deamx_food_goods_spec')." WHERE id='".$specItem['id']."';";
				$spec_result = pdo_query($spec_sql);
				$new_specid = pdo_insertid();
				
				//获取spec_item
				$itemids = array();
				$item_list = pdo_getall("deamx_food_goods_spec_item",array('uniacid'=>$uniacid,'store_id'=>$id,'specid'=>$specItem['id']));
				foreach ($item_list as $key4 => $itemItem) {
					$item_sql = "INSERT INTO ".tablename('deamx_food_goods_spec_item')."(uniacid,store_id,specid,title,`show`,displayorder,valueId,virtual) SELECT uniacid,REPLACE(store_id,store_id,'".$new_storeid."'),REPLACE(specid,specid,'".$new_specid."'),title,`show`,displayorder,valueId,virtual FROM ".tablename('deamx_food_goods_spec_item')." WHERE id='".$itemItem['id']."';";
					$item_result = pdo_query($item_sql);
					$new_itemid = pdo_insertid();
					$spec_check[$itemItem['id']] = $new_itemid;
					$itemids[] = $new_itemid;
				}
				pdo_update('deamx_food_goods_spec', array('content' => serialize($itemids)), array('id' => $new_specid));
			}
			//获取option
			$option_list = pdo_getall("deamx_food_goods_option",array('uniacid'=>$uniacid,'store_id'=>$id,'goodsid'=>$goodsItem['id']));
			foreach ($option_list as $key5 => $optionItem) {
				$specs = $optionItem['specs'];
				$specsArr = explode("_", $specs);
				$new_specsArr = array();
				foreach ($specsArr as $specsItem) {
					$new_specsArr[] = $spec_check[$specsItem];
				}
				$new_specs = implode("_", $new_specsArr);
				$option_sql = "INSERT INTO ".tablename('deamx_food_goods_option')."(uniacid,store_id,goodsid,title,marketprice,stock,displayorder,specs,realsales) SELECT uniacid,REPLACE(store_id,store_id,'".$new_storeid."'),REPLACE(goodsid,goodsid,'".$new_goodsid."'),title,marketprice,stock,displayorder,REPLACE(specs,specs,'".$new_specs."'),REPLACE(realsales,realsales,'0') FROM ".tablename('deamx_food_goods_option')." WHERE id='".$optionItem['id']."';";
				$option_result = pdo_query($option_sql);
				$new_optionid = pdo_insertid();

			}
		}
	}
	itoast("门店复制成功，重新编辑保存后自动生成小程序码！",$this->createWebUrl('store',array('version_id'=>intval($_GPC['version_id']))),'info');
}elseif($operation == 'delete'){
	$id = intval($_GPC['id']);
	pdo_delete("deamx_food_store",array('uniacid'=>$uniacid,'id'=>$id));
	//删除桌号
	pdo_delete("deamx_food_desknumber",array('uniacid'=>$uniacid,'store_id'=>$id));
	//删除打印机
	pdo_delete("deamx_food_printer",array('uniacid'=>$uniacid,'store_id'=>$id));
	//删除商品分类
	pdo_delete("deamx_food_class",array('uniacid'=>$uniacid,'store_id'=>$id));
	//删除商品
	pdo_delete("deamx_food_goods",array('uniacid'=>$uniacid,'store_id'=>$id));
	//删除商品规格
	pdo_delete("deamx_food_goods_option",array('uniacid'=>$uniacid,'store_id'=>$id));
	pdo_delete("deamx_food_goods_spec",array('uniacid'=>$uniacid,'store_id'=>$id));
	pdo_delete("deamx_food_goods_spec_item",array('uniacid'=>$uniacid,'store_id'=>$id));
	//删除店员
	pdo_delete("deamx_food_store_clerk",array('uniacid'=>$uniacid,'store_id'=>$id));
	itoast("门店删除成功",$this->createWebUrl('store',array('version_id'=>intval($_GPC['version_id']))),'success');
} elseif($operation == 'getSquarePoint'){
    print_r(returnSquarePoint(116.397128, 39.916527, 1000));
    exit;

}
include $this->template('system/store/store');
?>
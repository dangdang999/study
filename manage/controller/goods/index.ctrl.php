<?php
defined('IN_IA') or exit('Access Denied');
$operation = empty($operation) ? 'display' : $operation;
$method = trim($_GPC['method']);
$class = pdo_getall("deamx_food_class",array('uniacid'=>$uniacid,'store_id'=>$store_id),array('id','classname'),'id');
if($operation == 'display'){
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' and store_id='.$store_id;
	$classid = intval($_GPC['classid']);
	if(!empty($classid)){
		$condition .= ' and class_id='.$classid;
	}
	$keyword = trim($_GPC['keyword']);
	if(!empty($keyword)){
		$condition .= " and name like '%{$keyword}%'";
	}
	$list = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_goods')." WHERE uniacid=:uniacid ".$condition." ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':uniacid'=>$_W['uniacid']));
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('deamx_food_goods') . " WHERE uniacid = '{$_W['uniacid']}' $condition");
	$pager = pagination($total, $pindex, $psize);
	if(checksubmit('status0')){
		if(empty($_GPC['items'])){
			itoast("请至少选择一条数据进行操作",manage_url(array('r'=>'goods','ac'=>'index','op'=>'display')),'error');
		}
		$goodsIdStr = implode(",", $_GPC['items']);
		$result = pdo_query("UPDATE ".tablename('deamx_food_goods')." SET status = :status WHERE id IN({$goodsIdStr})", array(':status' => '0'));
		if($result){
			itoast("操作成功",manage_url(array('r'=>'goods','ac'=>'index','op'=>'display')),'success');
		}
		exit();
	}
	if(checksubmit('status1')){
		if(empty($_GPC['items'])){
			itoast("请至少选择一条数据进行操作",manage_url(array('r'=>'goods','ac'=>'index','op'=>'display')),'error');
		}
		$goodsIdStr = implode(",", $_GPC['items']);
		$result = pdo_query("UPDATE ".tablename('deamx_food_goods')." SET status = :status WHERE id IN({$goodsIdStr})", array(':status' => '1'));
		if($result){
			itoast("操作成功",manage_url(array('r'=>'goods','ac'=>'index','op'=>'display')),'success');
		}
		exit();
	}
}elseif($operation == 'post'){
	empty($class) && itoast("请先添加分类！",manage_url(array('r'=>'goods','ac'=>'index','op'=>'class','method'=>'list')),'error');
	$id = intval($_GPC['id']);
	if(!empty($id)){//编辑
		if(!in_array("store_goods_edit", $user_permissionArr)){
			itoast('您没有相应操作权限！',manage_url(array('r'=>'goods','ac'=>'index','op'=>'display')),'error');
			exit;
		}
		$goods = pdo_get("deamx_food_goods",array('uniacid'=>$uniacid,'id'=>$id,'store_id'=>$store_id));
		empty($goods) && itoast("商品不存在或已被删除",manage_url(array('r'=>'goods','ac'=>'index','op'=>'display')),'error');

        $goods['goods_attr'] = iunserializer($goods['goods_attr']);
        $newSpecsArr = pdo_fetchall('select * from ' . tablename('deamx_food_goods_option') . ' where goodsid=:goodsid and uniacid=:uniacid AND is_new = "1" order by displayorder asc, id asc', array(':goodsid' => $id, ':uniacid' => $_W['uniacid']));

		$allspecs = pdo_fetchall('select * from ' . tablename('deamx_food_goods_spec') . ' where goodsid=:id order by displayorder asc', array(':id' => $id));
		foreach ($allspecs as &$row){
			$row['items'] = pdo_fetchall('select * from ' . tablename('deamx_food_goods_spec_item') . ' where specid=:specid order by displayorder asc', array(':specid' => $row['id']));
		}
		unset($row);
		$html = '';
		$options = pdo_fetchall('select * from ' . tablename('deamx_food_goods_option') . ' where goodsid=:id AND is_new = "0" order by id asc', array(':id' => $id));
		if (count($options) > 0){
			$specitemids = explode('_', $options[0]['specs']);
			foreach ($specitemids as $itemid ){
				foreach ($allspecs as $ss){
					$items = $ss['items'];
					foreach ($items as $it ){
						if ($it['id'] == $itemid){
							$specs[] = $ss;
							break;
						}
					}
				}
			}
			$html = '';
			$html .= '<table class="table table-bordered table-condensed">';
			$html .= '<thead>';
			$html .= '<tr class="active">';
			$discounts_html .= '<table class="table table-bordered table-condensed">';
			$discounts_html .= '<thead>';
			$discounts_html .= '<tr class="active">';
			$commission_html .= '<table class="table table-bordered table-condensed">';
			$commission_html .= '<thead>';
			$commission_html .= '<tr class="active">';
			$isdiscount_discounts_html .= '<table class="table table-bordered table-condensed">';
			$isdiscount_discounts_html .= '<thead>';
			$isdiscount_discounts_html .= '<tr class="active">';
			$len = count($specs);
			$newlen = 1;
			$h = array();
			$rowspans = array();
			$i = 0;
			while ($i < $len) 
			{
				$html .= '<th>' . $specs[$i]['title'] . '</th>';
				$discounts_html .= '<th>' . $specs[$i]['title'] . '</th>';
				$commission_html .= '<th>' . $specs[$i]['title'] . '</th>';
				$isdiscount_discounts_html .= '<th>' . $specs[$i]['title'] . '</th>';
				$itemlen = count($specs[$i]['items']);
				if ($itemlen <= 0) 
				{
					$itemlen = 1;
				}
				$newlen *= $itemlen;
				$h = array();
				$j = 0;
				while ($j < $newlen) 
				{
					$h[$i][$j] = array();
					++$j;
				}
				$l = count($specs[$i]['items']);
				$rowspans[$i] = 1;
				$j = $i + 1;
				while ($j < $len) 
				{
					$rowspans[$i] *= count($specs[$j]['items']);
					++$j;
				}
				++$i;
			}
			$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">价格</div><div class="input-group"><input type="text" class="form-control  input-sm option_marketprice_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_marketprice\');"></a></span></div></div></th>';
			$html .= '</tr></thead>';
			$m = 0;
			while ($m < $len){
				$k = 0;
				$kid = 0;
				$n = 0;
				$j = 0;
				while ($j < $newlen) 
				{
					$rowspan = $rowspans[$m];
					if (($j % $rowspan) == 0) 
					{
						$h[$m][$j] = array('html' => '<td class=\'full\' rowspan=\'' . $rowspan . '\'>' . $specs[$m]['items'][$kid]['title'] . '</td>', 'id' => $specs[$m]['items'][$kid]['id']);
					}
					else 
					{
						$h[$m][$j] = array('html' => '', 'id' => $specs[$m]['items'][$kid]['id']);
					}
					++$n;
					if ($n == $rowspan) 
					{
						++$kid;
						if ((count($specs[$m]['items']) - 1) < $kid) 
						{
							$kid = 0;
						}
						$n = 0;
					}
					++$j;
				}
				++$m;
			}
			$hh = '';
			$dd = '';
			$isdd = '';
			$cc = '';
			$i = 0;
			while ($i < $newlen) 
			{
				$hh .= '<tr>';
				$ids = array();
				$j = 0;
				while ($j < $len) 
				{
					$hh .= $h[$j][$i]['html'];
					$ids[] = $h[$j][$i]['id'];
					++$j;
				}
				$ids = implode('_', $ids);
				$val = array('id' => '', 'title' => '', 'stock' => '', 'presell' => '', 'costprice' => '', 'productprice' => '', 'marketprice' => '', 'weight' => '', 'virtual' => '');
				foreach ($options as $o ) 
				{
					if ($ids === $o['specs']) 
					{
						$val = array('id' => $o['id'], 'title' => $o['title'], 'stock' => $o['stock'],'marketprice' => $o['marketprice'], 'virtual' => $o['virtual']);
						$discount_val = array('id' => $o['id']);
						
						break;
					}
				}
				
				
				// $hh .= '<td>';
				// $hh .= '<input data-name="option_stock_' . $ids . '"  type="text" class="form-control option_stock option_stock_' . $ids . '" value="' . $val['stock'] . '"/>';
				// $hh .= '</td>';
				$hh .= '<input data-name="option_id_' . $ids . '"  type="hidden" class="form-control option_id option_id_' . $ids . '" value="' . $val['id'] . '"/>';
				$hh .= '<input data-name="option_ids"  type="hidden" class="form-control option_ids option_ids_' . $ids . '" value="' . $ids . '"/>';
				$hh .= '<input data-name="option_title_' . $ids . '"  type="hidden" class="form-control option_title option_title_' . $ids . '" value="' . $val['title'] . '"/>';
				
				$hh .= '<td><input data-name="option_marketprice_' . $ids . '" type="text" class="form-control option_marketprice option_marketprice_' . $ids . '" value="' . $val['marketprice'] . '"/></td>';
				
				$hh .= '</tr>';
				++$i;
			}
			$html .= $hh;
			$html .= '</table>';
		}
	}else{
		if(!in_array("store_goods_add", $user_permissionArr)){
			itoast('您没有相应操作权限！',manage_url(array('r'=>'goods','ac'=>'index','op'=>'display')),'error');
			exit;
		}
	}
	if(checksubmit('submit') || checksubmit('submitadd')){
        $attr_ids = $_GPC['attr_id'];
        if(empty($attr_ids)){
            $attr_ids = array();
        }
        $new_spec_item = $_GPC['new_spec_item'];
        if(empty($new_spec_item)){
            $new_spec_item = array();
        }
        $attrArr = array();
		$postArr = array(
			'uniacid'		    =>	$uniacid,
			'store_id'		    =>	$store_id,
			'displayorder'	    =>	intval($_GPC['displayorder']),
			'name'			    =>	trim($_GPC['name']),
			'intro'			    =>	trim($_GPC['intro']),
			'class_id'		    =>	intval($_GPC['class_id']),
			'price'			    =>	number_format($_GPC['price'],2,".",""),
			'is_pbox'		    =>	intval($_GPC['is_pbox']),
			'pbox_price'	    =>	@number_format($_GPC['pbox_price'],2,".",""),
			'unit'			    =>	trim($_GPC['unit']),
			'img'			    =>	trim($_GPC['img']),
			'hasoption'		    =>	intval($_GPC['hasoption']),
			'status'		    =>	intval($_GPC['status']),
            'old_option'        =>  intval($_GPC['old_option']),
            'goods_specs_title' =>  trim($_GPC['goods_specs_title']),
			'createtime'	    =>	TIMESTAMP
		);

        if($postArr['old_option'] == '0' && $postArr['hasoption'] == '1'){
            $attr_title = $_GPC['attr_title'];
            $attr_len = count($attr_ids);
            $k = 0;
            while ($k < $attr_len){
                $attr_id = '';
                $get_attr_id = $attr_ids[$k];
                $attrArr[$k] = array(
                    'title'     =>  $attr_title[$k]
                );
                $nameArr = $_GPC['attr_item_title_' . $get_attr_id];
                foreach ($nameArr as $nameKey => $name){
                    $attrArr[$k]['options'][] = array(
                        'name'      =>  $name,
                        'status'    =>  $_GPC['attr_item_show_' . $get_attr_id][$nameKey]
                    );
                }
                unset($name);
                ++$k;
            }
            $attrStr = iserializer($attrArr);
            $postArr['goods_attr'] = $attrStr;


        }

		if(empty($id)){
			pdo_insert("deamx_food_goods",$postArr);
			$id = pdo_insertid();
		}else{
			unset($postArr['createtime']);
			pdo_update("deamx_food_goods",$postArr,array('uniacid'=>$uniacid,'id'=>$id,'store_id'=>$store_id));
		}
        //新规格
        //print_r($new_spec_item);exit;
        $optionids = array();
        foreach ($new_spec_item['id'] as $key => $itemId){
            if(!empty($new_spec_item['title'][$key])){
                $a = array('uniacid' => $_W['uniacid'],'store_id' => $store_id, 'title' => $new_spec_item['title'][$key],'marketprice' => number_format($new_spec_item['marketprice'][$key], '2', '.', ''), 'stock' => $new_spec_item['option_stock'][$key],'goodsid' => $id, 'specs' => '', 'is_new' => '1', 'displayorder' => $key);
                if($a['marketprice'] <= '0'){
                    $a['marketprice'] = $postArr['price'];
                }
                if (empty($itemId)){
                    pdo_insert('deamx_food_goods_option', $a);
                    $option_id = pdo_insertid();
                }else{
                    pdo_update('deamx_food_goods_option', $a, array('id' => $itemId));
                    $option_id = $itemId;
                }
                $optionids[] = $option_id;
            }
        }
        if ((0 < count($optionids)) && ($postArr['hasoption'] !== 0))
        {
            pdo_query('delete from ' . tablename('deamx_food_goods_option') . ' where goodsid=' . $id . ' AND is_new = "1" and id not in ( ' . implode(',', $optionids) . ')');

        }
        else
        {
            pdo_query('delete from ' . tablename('deamx_food_goods_option') . ' where goodsid=' . $id . " AND is_new = '1'");

        }
        //新规格结束
		//规格
		$spec_ids = $_GPC['spec_id'];
		$spec_titles = $_GPC['spec_title'];
		$specids = array();
		$len = count($spec_ids);
		$specids = array();
		$spec_items = array();
		$k = 0;
		while ($k < $len){
			$spec_id = '';
			$get_spec_id = $spec_ids[$k];
			$a = array('uniacid' => $_W['uniacid'],'store_id' => $store_id, 'goodsid' => $id, 'displayorder' => $k, 'title' => $spec_titles[$get_spec_id]);
			if (is_numeric($get_spec_id)){
				pdo_update('deamx_food_goods_spec', $a, array('id' => $get_spec_id));
				$spec_id = $get_spec_id;
			}else{
				pdo_insert('deamx_food_goods_spec', $a);
				$spec_id = pdo_insertid();
			}
			$spec_item_ids = $_GPC['spec_item_id_' . $get_spec_id];
			$spec_item_titles = $_GPC['spec_item_title_' . $get_spec_id];
			$spec_item_shows = $_GPC['spec_item_show_' . $get_spec_id];
			$itemlen = count($spec_item_ids);
			$itemids = array();
			$n = 0;
			while ($n < $itemlen){
				$item_id = '';
				$get_item_id = $spec_item_ids[$n];
				$d = array('uniacid' => $_W['uniacid'],'store_id' => $store_id, 'specid' => $spec_id, 'displayorder' => $n, 'title' => $spec_item_titles[$n], 'show' => $spec_item_shows[$n]);
				if (is_numeric($get_item_id)) 
				{
					pdo_update('deamx_food_goods_spec_item', $d, array('id' => $get_item_id));
					$item_id = $get_item_id;
				}
				else 
				{
					pdo_insert('deamx_food_goods_spec_item', $d);
					$item_id = pdo_insertid();
				}
				$itemids[] = $item_id;
				$d['get_id'] = $get_item_id;
				$d['id'] = $item_id;
				$spec_items[] = $d;
				++$n;
			}
			if (0 < count($itemids)) {
				pdo_query('delete from ' . tablename('deamx_food_goods_spec_item') . ' where uniacid=' . $_W['uniacid'] . ' and specid=' . $spec_id . ' and id not in (' . implode(',', $itemids) . ')');
			}else{
				pdo_query('delete from ' . tablename('deamx_food_goods_spec_item') . ' where uniacid=' . $_W['uniacid'] . ' and specid=' . $spec_id);
			}
			pdo_update('deamx_food_goods_spec', array('content' => serialize($itemids)), array('id' => $spec_id));
			$specids[] = $spec_id;
			++$k;
		}
		if (count($specids)>0){
			pdo_query('delete from ' . tablename('deamx_food_goods_spec') . ' where uniacid=' . $_W['uniacid'] . ' and goodsid=' . $id . ' and id not in (' . implode(',', $specids) . ')');
		}else {
			pdo_query('delete from ' . tablename('deamx_food_goods_spec') . ' where uniacid=' . $_W['uniacid'] . ' and goodsid=' . $id);
		}
		$_GPC['optionArray'] = htmlspecialchars_decode($_GPC['optionArray']);
		$optionArray = json_decode($_GPC['optionArray'], true);
		$option_idss = $optionArray['option_ids'];
		$len = count($option_idss);
		$optionids = array();
		$k = 0;
		while ($k < $len){
			$option_id = '';
			$ids = $option_idss[$k];
			$get_option_id = $optionArray['option_id'][$k];
			$idsarr = explode('_', $ids);
			$newids = array();
			foreach ($idsarr as $key => $ida ) {
				foreach ($spec_items as $it ) 
				{
					if ($it['get_id'] == $ida) 
					{
						$newids[] = $it['id'];
						break;
					}
				}
			}
			$newids = implode('_', $newids);
			$a = array('uniacid' => $_W['uniacid'],'store_id' => $store_id, 'title' => $optionArray['option_title'][$k],'marketprice' => $optionArray['option_marketprice'][$k], 'stock' => $optionArray['option_stock'][$k],'goodsid' => $id, 'specs' => $newids, 'is_new' => '0');
			
			$totalstocks += $a['stock'];
			if (empty($get_option_id)){
				pdo_insert('deamx_food_goods_option', $a);
				$option_id = pdo_insertid();
			}else{
				pdo_update('deamx_food_goods_option', $a, array('id' => $get_option_id));
				$option_id = $get_option_id;
			}
			$optionids[] = $option_id;
			++$k;
		}
		if ((0 < count($optionids)) && ($postArr['hasoption'] !== 0)) 
		{
			pdo_query('delete from ' . tablename('deamx_food_goods_option') . ' where goodsid=' . $id . ' and is_new = "0" and id not in ( ' . implode(',', $optionids) . ')');
			
		}
		else 
		{
			pdo_query('delete from ' . tablename('deamx_food_goods_option') . ' where goodsid=' . $id . ' and is_new = "0"');
			
		}
		//规格 end
		
		if(checksubmit('submit')){
			itoast('商品更新成功！',manage_url(array('r'=>'goods','ac'=>'index','op'=>'display')), 'success');
		}elseif(checksubmit('submitadd')){
			itoast('商品更新成功！',manage_url(array('r'=>'goods','ac'=>'index','op'=>'post')), 'success');
		}
	}
}elseif($operation == 'delete'){
	if(!in_array("store_goods_delete", $user_permissionArr)){
		itoast('您没有相应操作权限！',manage_url(array('r'=>'goods','ac'=>'index','op'=>'display')),'error');
		exit;
	}
	$id = intval($_GPC['id']);
	pdo_delete("deamx_food_goods",array('uniacid'=>$uniacid,'id'=>$id,'store_id'=>$store_id));
	pdo_delete("deamx_food_goods_option",array('uniacid'=>$uniacid,'goodsid'=>$id));
	$specArr = pdo_getall("deamx_food_goods_spec",array('uniacid'=>$uniacid,'goodsid'=>$id),array('id'));
	foreach ($specArr as $specValue) {
		pdo_delete("deamx_food_goods_spec_item",array('uniacid'=>$uniacid,'specid'=>$specValue['id']));
	}
	pdo_delete("deamx_food_goods_spec",array('uniacid'=>$uniacid,'goodsid'=>$id));
	itoast('商品删除成功！',manage_url(array('r'=>'goods','ac'=>'index','op'=>'display')), 'success');
}elseif($operation == 'class'){
	if($method == 'list'){
		if(checksubmit('submit')){
			if (!empty($_GPC['sortid'])) {
		        foreach ($_GPC['sortid'] as $id => $displayorder) {
		            pdo_update('deamx_food_class', array(
		                'sortid' => $displayorder
		            ), array(
		                'id' => $id,
		                'store_id'=>$store_id
		            ));
		        }
		        itoast('排序更新成功！',manage_url(array('r'=>'goods','ac'=>'index','op'=>'class','method'=>'list')), 'success');
		    }
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = ' and store_id='.$store_id;
		$list = pdo_fetchall("SELECT * FROM ".tablename('deamx_food_class')." WHERE uniacid=:uniacid ".$condition." ORDER BY sortid DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':uniacid'=>$_W['uniacid']));
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('deamx_food_class') . " WHERE uniacid = '{$_W['uniacid']}' $condition");
		$pager = pagination($total, $pindex, $psize);
	}elseif($method == 'post'){
		$id = intval($_GPC['id']);
		if(!empty($id)){
			if(!in_array("store_class_edit", $user_permissionArr)){
				itoast('您没有相应操作权限！',manage_url(array('r'=>'goods','ac'=>'index','op'=>'class','method'=>'list')),'error');
				exit;
			}
			$class = pdo_get("deamx_food_class",array('uniacid'=>$uniacid,'id'=>$id,'store_id'=>$store_id));
		}else{
			if(!in_array("store_class_add", $user_permissionArr)){
				itoast('您没有相应操作权限！',manage_url(array('r'=>'goods','ac'=>'index','op'=>'class','method'=>'list')),'error');
				exit;
			}
		}
		if(checksubmit()){
			$postArr = array(
				'uniacid'		=>	$uniacid,
				'store_id'		=>	$store_id,
				'sortid'		=>	intval($_GPC['sortid']),
				'classname'		=>	trim($_GPC['classname']),
				'enabled'		=>	intval($_GPC['enabled']),
				'createtime'	=>	TIMESTAMP
			);
			if(!empty($id)){
				unset($postArr['createtime']);
				$result = pdo_update("deamx_food_class",$postArr,array('uniacid'=>$uniacid,'store_id'=>$store_id,'id'=>$id));
				itoast("分类更新成功！",manage_url(array('r'=>'goods','ac'=>'index','op'=>'class','method'=>'list')),"success");
			}else{
				$result = pdo_insert("deamx_food_class",$postArr);
				itoast("添加分类成功！",manage_url(array('r'=>'goods','ac'=>'index','op'=>'class','method'=>'list')),"success");
			}
		}
	}elseif($method == 'delete'){
		if(!in_array("store_class_delete", $user_permissionArr)){
			itoast('您没有相应操作权限！',manage_url(array('r'=>'goods','ac'=>'index','op'=>'class','method'=>'list')),'error');
			exit;
		}
		$id = intval($_GPC['id']);
		$goods = pdo_getall("deamx_food_goods",array('uniacid'=>$uniacid,'store_id'=>$store_id,'class_id'=>$id),array('id'));
		!empty($goods) && itoast("请将分类下的商品移除后再来删除分类！",manage_url(array('r'=>'goods','ac'=>'index','op'=>'class','method'=>'list')),'error');
		pdo_delete("deamx_food_class",array('uniacid'=>$uniacid,'store_id'=>$store_id,'id'=>$id));
		itoast("分类删除成功！",manage_url(array('r'=>'goods','ac'=>'index','op'=>'class','method'=>'list')),"success");
	}
}elseif($operation == 'tpl'){
	if($method == 'spec'){
		$spec = array('id' => random(32), 'title' => $_GPC['title']);
		include manage_template('store/tpl/spec');
		exit();
	}elseif($method == 'specitem'){
		$spec = array('id' => $_GPC['specid']);
		$specitem = array('id' => random(32), 'title' => $_GPC['title'], 'show' => 1);
		include manage_template('store/tpl/spec_item');
		exit();
	}elseif($method == 'attr'){
        $goods_attr = array('id' => random(32), 'title' => $_GPC['title']);
        include manage_template('store/tpl/attr');
        exit();
    }elseif($method == 'attr_item'){
        $goods_attr = array('id' => $_GPC['specid']);
        $options = array('id' => random(32), 'title' => $_GPC['title'], 'status' => 1);
        include manage_template('store/tpl/attr_item');
        exit();
    }elseif($method == 'new_specs'){
        include manage_template('store/tpl/new_specs');
        exit();
    }elseif($method == 'add_new_specs_item'){
        include manage_template('store/tpl/new_specs_item');
        exit();
    }

}

include manage_template("store/goods");
?>
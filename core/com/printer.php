<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Printer_DeamFoodComModel{
	/**
	 * 易联云生成签名sign
	 * @param  array $params 参数
	 * @param  string $apiKey API密钥
	 * @param  string $msign 打印机密钥
	 * @return   string sign
	 */
	public function yly_GenerateSign($params, $apiKey,$msign)
	{
	    //所有请求参数按照字母先后顺序排
	    ksort($params);
	    //定义字符串开始所包括的字符串
	    $stringToBeSigned = $apiKey;
	    //把所有参数名和参数值串在一起
	    foreach ($params as $k => $v)
	    {
	        $stringToBeSigned .= urldecode($k.$v);
	    }
	    unset($k, $v);
	    //定义字符串结尾所包括的字符串
	    $stringToBeSigned .= $msign;
	    //使用MD5进行加密，再转化成大写
	    return strtoupper(md5($stringToBeSigned));
	}
	/**
	 * 易联云打印接口
	 * @param  int $partner     用户ID
	 * @param  string $machine_code 打印机终端号
	 * @param  string $content      打印内容
	 * @param  string $apiKey       API密钥
	 * @param  string $msign       打印机密钥
	 */
	public function printerYilianyunNew($link_url,$partner,$machine_code,$content,$apiKey,$msign)
	{
		$param = array(
			"partner"=>$partner,
			'machine_code'=>$machine_code,
			'time'=>time(),
		);
		//获取签名
		$param['sign'] = $this->yly_GenerateSign($param,$apiKey,$msign);
		$param['content'] = $content;
		$response = ihttp_request($link_url,$param);
		$result = @json_decode($response['content'], true);
		return $result;
	}
	/**
	 * 飞鹅云打印接口
	 */
	public function printerFeie($link_url,$user,$ukey,$deviceNo,$content,$times)
	{
		$param = array(
			"user"=>$user,
			'stime'=>time(),
			'apiname'=>'Open_printMsg',
			'sn'=>$deviceNo,
			'times'=>$times
		);
		//获取签名
		$param['sig'] = sha1($param['user'].$ukey.$param['stime']);
		$param['content'] = $content;
		$response = ihttp_request($link_url,$param);
		$result = @json_decode($response['content'], true);
		return $result;
	}
	/**
	 * 365 s1 
	*/
	public function printer365s1($deviceNo,$key,$content,$times)
	{
		$link_url = "http://open.printcenter.cn:8080/addOrder";
		$param = array(
			'deviceNo'		=>	$deviceNo,
			'printContent'	=>	$content,
			'key'			=>	$key,
			'times'			=>	empty($times) ? 1 : $times
		);
		$response = ihttp_request($link_url,$param);
		$result = @json_decode($response['content'], true);
		return $result;
	}
	/**
	 * 易联云 auth2.0新接口获取token
	 * @param $client_id  易联云颁发给开发者的应用ID
	 * @param $client_secret 易联云颁发给开发者的应用密钥
	 * @return array
	 */
	protected function getYilianyunToken($client_id = '', $client_secret = ''){
		global $_W;
		if (empty($client_id)) {
			return array('error' => 1001, 'error_description' => '缺少应用ID');
		}
		if (empty($client_secret)) {
			return array('error' => 1002, 'error_description' => '缺少应用密钥');
		}
		$tokenFileDir = DM_ROOT."/data/yilianyun_token/{$_W['uniacid']}";
		if (!file_exists($tokenFileDir)){
		    @mkdir($tokenFileDir,0777,true);
		}
		$tokenName = 'token_' . $client_id;
		$tokenFile = $tokenFileDir . "/" . $tokenName;
		if (file_exists($tokenFile)) {
			$content = file_get_contents($tokenFile);
			$content = json_decode($content, true);

			if ((time() - filemtime($tokenFile)) < (3600 * 240)) {
				return $content;
			}
		}
		//获取accesstoken
		load()->func('communication');
		$postUrl = "https://open-api.10ss.net/oauth/oauth";
		$postArr = array(
			'client_id'		=>	$client_id,
			'timestamp'		=>	TIMESTAMP,
			'client_secret'	=>	$client_secret
		);
		$tempStr = "";
		foreach ($postArr as $value) {
			$tempStr .= $value;
		}
		$postArr['sign'] = md5($tempStr);
		unset($tempStr);
		unset($postArr['client_secret']);
		$postArr['grant_type'] = "client_credentials";
		$postArr['scope'] = "all";
		$postArr['id'] = $this->create_guid();
		$getJson = ihttp_post($postUrl,$postArr);
		$getArr = json_decode($getJson['content'],true);
		if($getArr['error'] == '0'){
			file_put_contents($tokenFile, $getJson['content']);
		}
		return $getArr;
	}
	/**
	* 绑定易联云打印机(AUTH2.0接口)
	* @param $client_id        易联云颁发给开发者的应用ID
	* @param $client_secret    易联云颁发给开发者的应用密钥
	* @param $machine_code     易联云打印机终端号
	* @param $msign            易联云终端密钥
	* @return [type] [description]
	*/
	public function bindYilianyunPrinter($client_id = '', $client_secret = '', $machine_code = '', $msign = ''){
		global $_W;
		if (empty($client_id)) {
			return array('error' => 100, 'error_description' => '缺少应用ID');
		}
		if (empty($client_secret)) {
			return array('error' => 101, 'error_description' => '缺少应用密钥');
		}
		if (empty($machine_code)) {
			return array('error' => 102, 'error_description' => '缺少打印机编号');
		}
		if (empty($msign)) {
			return array('error' => 103, 'error_description' => '缺少打印机密钥');
		}
		$token = $this->getYilianyunToken($client_id, $client_secret);
		if ($token['error']) {
			return array('error' => $token['error'], 'error_description' => $token['error_description']);
		}
		$timestamp = TIMESTAMP;
		$access_token = $token['body']['access_token'];
		$sign = md5($client_id . $timestamp . $client_secret);
		$guid = $this->create_guid();
		$post_data = array('client_id' => $client_id, 'machine_code' => $machine_code, 'msign' => $msign, 'access_token' => $access_token, 'sign' => $sign, 'timestamp' => $timestamp, 'id' => $guid);
		load()->func('communication');
		$curl = 'https://open-api.10ss.net/printer/addprinter';
		$content = ihttp_post($curl, $post_data);
		$content = json_decode($content['content'], true);
		return $content;
	}
	public function setYilianyunPrinterLogo($client_id = '', $client_secret = '', $machine_code = '', $img_url = ''){
		global $_W;
		if (empty($client_id)) {
			return array('error' => 100, 'error_description' => '缺少应用ID');
		}
		if (empty($client_secret)) {
			return array('error' => 101, 'error_description' => '缺少应用密钥');
		}
		if (empty($machine_code)) {
			return array('error' => 102, 'error_description' => '缺少打印机编号');
		}
		if (empty($img_url)) {
			return array('error' => 100, 'error_description' => '缺少logo路径');
		}
		$token = $this->getYilianyunToken($client_id, $client_secret);
		if ($token['error']) {
			return array('error' => $token['error'], 'error_description' => $token['error_description']);
		}
		$timestamp = TIMESTAMP;
		$access_token = $token['body']['access_token'];
		$sign = md5($client_id . $timestamp . $client_secret);
		$guid = $this->create_guid();
		$post_data = array('client_id' => $client_id, 'machine_code' => $machine_code, 'access_token' => $access_token, 'sign' => $sign, 'timestamp' => $timestamp, 'id' => $guid,'img_url'=> $img_url);
		load()->func('communication');
		$curl = 'https://open-api.10ss.net/printer/seticon';
		$content = ihttp_post($curl, $post_data);
		$content = json_decode($content['content'], true);
		return $content;
	}
	public function deleteYilianyunPrinterLogo($client_id = '', $client_secret = '', $machine_code = ''){
		global $_W;
		if (empty($client_id)) {
			return array('error' => 100, 'error_description' => '缺少应用ID');
		}
		if (empty($client_secret)) {
			return array('error' => 101, 'error_description' => '缺少应用密钥');
		}
		if (empty($machine_code)) {
			return array('error' => 102, 'error_description' => '缺少打印机编号');
		}
		$token = $this->getYilianyunToken($client_id, $client_secret);
		if ($token['error']) {
			return array('error' => $token['error'], 'error_description' => $token['error_description']);
		}
		$timestamp = TIMESTAMP;
		$access_token = $token['body']['access_token'];
		$sign = md5($client_id . $timestamp . $client_secret);
		$guid = $this->create_guid();
		$post_data = array('client_id' => $client_id, 'machine_code' => $machine_code, 'access_token' => $access_token, 'sign' => $sign, 'timestamp' => $timestamp, 'id' => $guid);
		load()->func('communication');
		$curl = 'https://open-api.10ss.net/printer/deleteicon';
		$content = ihttp_post($curl, $post_data);
		$content = json_decode($content['content'], true);
		return $content;
	}
	public function printerYilianyunK5($client_id = '', $client_secret = '', $machine_code = '', $content = ''){
		global $_W;
		load()->func('logging');
		$token = $this->getYilianyunToken($client_id, $client_secret);
		logging_run($token);
		if ($token['error']) {
			return array('error' => $token['error'], 'error_description' => $token['error_description']);
		}
		$timestamp = TIMESTAMP;
		$access_token = $token['body']['access_token'];
		$sign = md5($client_id . $timestamp . $client_secret);
		$guid = $this->create_guid();
		$post_data = array('client_id' => $client_id, 'machine_code' => $machine_code, 'access_token' => $access_token, 'sign' => $sign, 'timestamp' => $timestamp, 'id' => $guid, 'origin_id' => "DP".date("YmdHis").mt_rand(1000,9999), 'content' => $content);

		logging_run($post_data);
		$curl = 'https://open-api.10ss.net/print/index';
		$content = curl_post($curl, $post_data);
		$content = json_decode($content, true);
		logging_run("全部出现");
		return $content;
	}
	protected function create_guid($namespace = '') {   
	  static $guid = '';
	  $uid = uniqid("", true);
	  $data = $namespace;
	  $data .= $_SERVER['REQUEST_TIME'];
	  $data .= $_SERVER['HTTP_USER_AGENT'];
	  $data .= $_SERVER['LOCAL_ADDR'];
	  $data .= $_SERVER['LOCAL_PORT'];
	  $data .= $_SERVER['REMOTE_ADDR'];
	  $data .= $_SERVER['REMOTE_PORT'];
	  $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
	  $guid = '' .  
	      substr($hash, 0, 8) . 
	      '-' .
	      substr($hash, 8, 4) .
	      '-' .
	      substr($hash, 12, 4) .
	      '-' .
	      substr($hash, 16, 4) .
	      '-' .
	      substr($hash, 20, 12) .
	      '';
	  return $guid;
	}
	public function setLineType($arr, $A = 19, $B = 7, $C = 6){
		$content = "";
		foreach ($arr as $k5 => $v5) {
	        $name = $v5['title'];
	        $price = $v5['price'];
	        $num = $v5['num'];
	        $kw3 = '';
	        $kw1 = '';
	        $kw2 = '';
	        $kw4 = '';
	        $str = $name;
	        $blankNum = $A;//名称控制为14个字节
	        $lan = mb_strlen($str,'utf-8');
	        $m = 0;
	        $j=1;
	        $blankNum++;
	        $result = array();
	        for ($i=0;$i<$lan;$i++){
	          $new = mb_substr($str,$m,$j,'utf-8');
	          $j++;
	          if(mb_strwidth($new,'utf-8')<$blankNum) {
	            if($m+$j>$lan) {
	              $m = $m+$j;
	              $tail = $new;
	              $lenght = iconv("UTF-8", "GBK//IGNORE", $new);
	              $k = $A - strlen($lenght);
	              for($q=0;$q<$k;$q++){
	                $kw3 .= ' ';
	              }
	              $tail .= $kw3;
	              break;
	            }else{
	              $next_new = mb_substr($str,$m,$j,'utf-8');
	              if(mb_strwidth($next_new,'utf-8')<$blankNum) continue;
	              else{
	                $m = $i+1;
	                $result[] = $new.'<BR>';
	                $j=1;
	              }
	            }
	          }
	        }
	        $head = '';
	        foreach ($result as $value) {
	          $head .= $value;
	        }
	        if(strlen($num) < $B){
	              $k2 = $B - strlen($num);
	              for($q=0;$q<$k2;$q++){
	                    $kw2 .= ' ';
	              }
	              $num = $num.$kw2;
	        }
	        if(strlen($price) < $C){
	              $k1 = $C - strlen($price);
	              for($q=0;$q<$k1;$q++){
	                    $kw1 .= ' ';
	              }
	              $price = $price.$kw1;
	        }
	        $content .= $head.$tail.' '.$num.' '.$price.'<BR>';
    	}
    	return $content;
	}

	public function deamPrint($orderid, $extraText = ""){
		global $_W;
		$uniacid = $_W['uniacid'];
		$log = pdo_fetch('SELECT * FROM ' . tablename('deamx_food_order') . ' WHERE `uniacid`=:uniacid and `id`=:id limit 1', array(
			':uniacid' => $_W['uniacid'],
			':id' => $orderid
		));
        if ($log['getfood_time'] == '现在下单，稍后即取') {
            $log['getfood_time'] = "立即取餐";
        } else {
            $log['getfood_time'] = str_replace("取餐", "", $log['getfood_time']);
            $log['getfood_time'] = $log['getfood_time'] . "取餐";
        }
		$store_id = $log['store_id'];
		$storeInfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$store_id));
		$printerArr = pdo_getall("deamx_food_printer",array('uniacid'=>$_W['uniacid'],'status'=>'1','store_id'=>$store_id));
		$orderNumber = $log['order_number'];
		if(!empty($log['desk_id'])){
			$deskInfo = pdo_get("deamx_food_desknumber",array('uniacid'=>$_W['uniacid'],'id'=>$log['desk_id'],'store_id'=>$log['store_id']),array('id','name'));
		}
		if($log['order_type'] == '1'){
			$orderTypeStr = "堂食";
			if( empty($deskInfo) ){
				$orderTypeStr = "自取";
			}
		}else{
			$orderTypeStr = "外卖";
		}
		if($log['pay_type'] == '1'){
			$payTypeStr = "微信支付";
		}elseif($log['pay_type'] == '2'){
			$payTypeStr = "余额支付";
		}
		$payMoney = $log['pay_price'];
		if(!empty($printerArr)){
			//判断是否为扫桌号小程序订餐
			
			$print = $this;
			$goodsArr = json_decode($log['goods_list'],true);//商品列表
			foreach ($printerArr as $printer) {
				$goodsHtml = "";
				if($printer['type'] == '4'){//易联云新
					if(empty($printer['print_type'])){//判断打印类型
						$goodsHtml = '<tr><td>品名</td><td>数量</td><td>小计</td></tr>';
						foreach ($goodsArr as $index => $goodsInfo) {
						    if (empty($goodsInfo['goodsid'])) {
						        //新版
                                if($index >0){
                                    $goodsHtml .= "<tr><td></td></tr>";
                                }
                                $goodsHtml .= "<tr><td>".$goodsInfo['name']."</td><td>".$goodsInfo['count']."</td><td>".$goodsInfo['price']."</td></tr>";
                                if (!empty($goodsInfo['optionsId']) && !empty($goodsInfo['optionName'])) {
                                    $goodsHtml .= "<tr><td>".$goodsInfo['optionName']."</td></tr>";
                                }
                            } else {
						        //老版
                                if(empty($goodsInfo['hasoption']) && $goodsInfo['count']>0){
                                    if($index >0){
                                        $goodsHtml .= "<tr><td></td></tr>";
                                    }
                                    $goodsHtml .= "<tr><td>".$goodsInfo['name']."</td><td>".$goodsInfo['count']."</td><td>".$goodsInfo['totalprice']."</td></tr>";
                                }else{
                                    if(is_array($goodsInfo['options'])){
                                        foreach ($goodsInfo['options'] as $optionsIndex => $optionsInfo) {
                                            if($optionsInfo['count']>0){
                                                if($index > 0 || $optionsIndex > 0){
                                                    $goodsHtml .= "<tr><td></td></tr>";
                                                }
                                                $goodsHtml .= "<tr><td>".$goodsInfo['name']."</td><td>".$optionsInfo['count']."</td><td>".$optionsInfo['price']."</td></tr>";
                                                $goodsHtml .= "<tr><td>".$optionsInfo['name']."</td></tr>";
                                            }
                                        }
                                    }
                                }
                            }

						}
						$printerConf = json_decode($printer['print_data'],true);
						$times = (empty($printerConf['printer_yilianyun_new']['times'])||$printerConf['printer_yilianyun_new']['times']=='1') ? '':"<MN>".$printerConf['printer_yilianyun_new']['times']."</MN>";
						if($log['order_type'] != '2'){
							$printTemp = "\r\n\r\n取餐号 <FS2>".$orderNumber."</FS2>".(empty($deskInfo['name'])?'':"\r\n\r\n桌号 <FS2>".$deskInfo['name']."</FS2>");
						}else{
							$printTemp = "\r\n\r\n订单号".$log['ordersn'];
							$goodsHtml .= "<tr><td></td></tr>";
							$goodsHtml .= "<tr><td>餐盒费</td><td> </td><td>".$log['pbox_fee']."</td></tr>";
							$goodsHtml .= "<tr><td></td></tr>";
							$goodsHtml .= "<tr><td>配送费</td><td> </td><td>".$log['send_fee']."</td></tr>";
						}
						$printTemp .= "\r\n<FS2>".$log['getfood_time']."</FS2>";
						$content = $times."<FH><center>".$storeInfo['name']."</center></FH>".$printTemp."\r\n\r\n<table>".$goodsHtml ."</table><table><tr><td></td></tr><tr><td>合计</td><td>".$log['count']."</td><td>".$log['price']."</td></tr></table>\r\n\r\n支付时间：".date('Y-m-d H:i:s').($log['enoughdeduct'] > 0 ? "\r\n满减优惠：".$log['enoughdeduct']."元":'').($log['coupon_price'] > 0 ? "\r\n优惠券优惠：".$log['coupon_price']."元":'')."\r\n实际支付：".$payMoney."元\r\n支付方式：{$payTypeStr}\r\n<FB>备注：".$log['remark']."</FB>\r\n";
						if($log['order_type'] == '2'){
							$addressinfo = json_decode($log['address_info'],true);
							$content .= "\r\n<FS2>".$addressinfo['realname']." ".$addressinfo['telphone']."</FS2>\r\n<FS2>".$addressinfo['address']." ".$addressinfo['address_road']." ".$addressinfo['number']."</FS2>\r\n";
						}else{
						    if($printer['order_qrcode'] == '1'){
                                $content .= '\r\n<QR>' . $log['ordersn'] . '</QR>\r\n';
                            }
                        }

					}else{
						//分单打印
						foreach ($goodsArr as $index => $goodsInfo) {
							//获取商品分类
							$goodsClass = pdo_getcolumn("deamx_food_goods",array('uniacid'=>$uniacid,'id'=>$goodsInfo['goodsid']), 'class_id',1);
							$print_class = json_decode($printer['print_class'],true);
							if(@in_array($goodsClass, $print_class)){
								if(empty($goodsInfo['hasoption']) && $goodsInfo['count']>0){
									$x = 1;
									while($x <= $goodsInfo['count']){
										if($log['order_type'] != '2'){
											$goodsHtml .= "取餐号 <FS2>".$orderNumber."</FS2>".(empty($deskInfo['name'])?'':"\r\n\r\n桌号 <FS2>".$deskInfo['name']."</FS2>")."\r\n\r\n";
										}else{
											$goodsHtml .= "订单号 <FB>".$log['ordersn']."</FB>\r\n\r\n";
										}
										
										$goodsHtml .= "<FS2>".$goodsInfo['name']. (!empty($goodsInfo['optionsId']) && !empty($goodsInfo['optionName'])) ? '-'.$goodsInfo['optionName'] : '' ."  x1</FS2>\r\n\r\n<FB>备注：".$log['remark']."</FB>\r\n\r\n";
										$goodsHtml .= "--------------------------------\r\n";
										$x++;
									}
								}else{
									if(is_array($goodsInfo['options'])){
										foreach ($goodsInfo['options'] as $optionsIndex => $optionsInfo) {
											if($optionsInfo['count']>0){
												$x = 1;
												while($x <= $optionsInfo['count']){
													if($log['order_type'] != '2'){
														$goodsHtml .= "取餐号 <FS2>".$orderNumber."</FS2>".(empty($deskInfo['name'])?'':"\r\n\r\n桌号 <FS2>".$deskInfo['name']."</FS2>")."\r\n\r\n";
													}else{
														$goodsHtml .= "订单号 <FB>".$log['ordersn']."</FB>\r\n\r\n";
													}
													$goodsHtml .= "<FS2>".$goodsInfo['name']."-".$optionsInfo['name']." x1</FS2>\r\n\r\n<FB>备注：".$log['remark']."</FB>\r\n\r\n";
													$goodsHtml .= "--------------------------------\r\n";
													$x++;
												}
											}
										}
									}
								}
							}
							
						}
						$printerConf = json_decode($printer['print_data'],true);
						$content = ((empty($printerConf['printer_yilianyun_new']['times'])||$printerConf['printer_yilianyun_new']['times']=='1') ? '':"<MN>".$printerConf['printer_yilianyun_new']['times']."</MN>").$goodsHtml;
					}
					$apiKey = $printerConf['printer_yilianyun_new']['apikey'];
					$msign = $printerConf['printer_yilianyun_new']['msign'];
					$result = $print->printerYilianyunNew($printerConf['printer_yilianyun_new']['url'],$printerConf['printer_yilianyun_new']['partner'],$printerConf['printer_yilianyun_new']['machine_code'],$content,$apiKey,$msign);
				}elseif($printer['type'] == '1'){//飞鹅
					if(empty($printer['print_type'])){//判断打印类型
						$content = "<CB>".$storeInfo['name']."</CB><BR>";
						if($log['order_type'] != '2'){
							$content .= "取餐号 <B>".$orderNumber."</B><BR><BR>";
							if(!empty($deskInfo['name'])){
								$content .= "桌号 <B>".$deskInfo['name']."</B><BR><BR>";
							}
						}else{
							$content .= "订单号 ".$log['ordersn']."<BR>";
						}
						$content .= "<B>".$log['getfood_time']."</B><BR><BR>";
						$content .= '名称               数量    金额<BR>';
						$content .= '--------------------------------<BR>';
						$printConArr = array();
						foreach ($goodsArr as $index => $goodsInfo) {
                            if (empty($goodsInfo['goodsid'])) {
                                //新版
                                $printConArr[] = array(
                                    'title'	=>	$goodsInfo['name'] . (!empty($goodsInfo['optionsId']) && !empty($goodsInfo['optionName'])) ? '-'.$goodsInfo['optionName'] : '',
                                    'num'	=>	$goodsInfo['count'],
                                    'price'	=>	$goodsInfo['price']
                                );
                            } else {
                                //老版
                                if(empty($goodsInfo['hasoption']) && $goodsInfo['count']>0){
                                    $printConArr[] = array(
                                        'title'	=>	$goodsInfo['name'],
                                        'num'	=>	$goodsInfo['count'],
                                        'price'	=>	$goodsInfo['totalprice']
                                    );

                                }else{
                                    if(is_array($goodsInfo['options'])){
                                        foreach ($goodsInfo['options'] as $optionsIndex => $optionsInfo) {
                                            if($optionsInfo['count']>0){

                                                $printConArr[] = array(
                                                    'title'	=>	$goodsInfo['name']."-".$optionsInfo['name'],
                                                    'num'	=>	$optionsInfo['count'],
                                                    'price'	=>	$optionsInfo['price']
                                                );
                                            }
                                        }
                                    }
                                }
                            }

						}

						
						if($log['order_type'] == '2'){
							if($log['pbox_fee'] > 0){
								$printConArr[] = array(
									'title'	=>	"餐盒费",
									'num'	=>	"",
									'price'	=>	$log['pbox_fee']
								);
							}
							$printConArr[] = array(
								'title'	=>	"配送费",
								'num'	=>	"",
								'price'	=>	$log['send_fee']
							);
						}
						$content .= $this->setLineType($printConArr);
						$content .= '--------------------------------<BR>';
						$printerConf = json_decode($printer['print_data'],true);
						$content .= "支付时间：".date('Y-m-d H:i:s')."<BR>";
						if($log['enoughdeduct'] > 0){
							$content .= "满减优惠：".$log['enoughdeduct']."元<BR>";
						}
						if($log['coupon_price'] > 0){
							$content .= "优惠券优惠：".$log['coupon_price']."元<BR>";
						}
						$content .= "实际支付：".$payMoney."元<BR>";
						$content .= "支付方式：".$payTypeStr."<BR>";
						if(!empty($log['remark'])){
							$content .= "备注：<B>".$log['remark']."</B><BR>";
						}
						if($log['order_type'] == '2'){
							$addressinfo = json_decode($log['address_info'],true);
							$content .= "<BR><B>".$addressinfo['realname']." ".$addressinfo['telphone']."</B><B>".$addressinfo['address']." ".$addressinfo['address_road']." ".$addressinfo['number']."</B><BR>";
						}else{
                            if($printer['order_qrcode'] == '1'){
                                $content .= '<BR><QR>' . $log['ordersn'] . '</QR>';
                                $content .= '<BR>';
                            }

                        }

					}else{
						$printerConf = json_decode($printer['print_data'],true);
						//分单打印
						$content = '';
						foreach ($goodsArr as $index => $goodsInfo) {
							//获取商品分类
							$goodsClass = pdo_getcolumn("deamx_food_goods",array('uniacid'=>$uniacid,'id'=>$goodsInfo['goodsid']), 'class_id',1);
							$print_class = json_decode($printer['print_class'],true);
							if(@in_array($goodsClass, $print_class)){
								if(empty($goodsInfo['hasoption']) && $goodsInfo['count']>0){
									$x = 1;
									while($x <= $goodsInfo['count']){
										if($log['order_type'] != '2'){
											$content .= "取餐号 <B>".$orderNumber."</B>".(empty($deskInfo['name'])?'':"<BR><BR>桌号 <B>".$deskInfo['name']."</B>")."<BR><BR>";
										}else{
											$content .= "订单号 <B>".$log['ordersn']."</B><BR>";
										}
										$content .= "<B>".$goodsInfo['name']. (!empty($goodsInfo['optionsId']) && !empty($goodsInfo['optionName'])) ? '-'.$goodsInfo['optionName'] : '' ." x1</B><BR><BR><B>备注：".$log['remark']."</B><BR><BR>";
										$content .= '--------------------------------<BR>';
										$x++;
									}
								}else{
									if(is_array($goodsInfo['options'])){
										foreach ($goodsInfo['options'] as $optionsIndex => $optionsInfo) {
											if($optionsInfo['count']>0){
												$x = 1;
												while($x <= $optionsInfo['count']){
													if($log['order_type'] != '2'){
														$content .= "取餐号 <B>".$orderNumber."</B>".(empty($deskInfo['name'])?'':"<BR><BR>桌号 <B>".$deskInfo['name']."</B>")."<BR><BR>";
													}else{
														$content .= "订单号 <B>".$log['ordersn']."</B><BR>";
													}
													$content .= "<B>".$goodsInfo['name']."-".$optionsInfo['name']." x1</B><BR><BR><B>备注：".$log['remark']."</B><BR><BR>";
													$content .= '--------------------------------<BR>';
													$x++;
												}
											}
										}
									}
								}
							}
							
						}
					}
					$result = $print->printerFeie($printerConf['printer_feie']['url'],$printerConf['printer_feie']['user'],$printerConf['printer_feie']['ukey'],$printerConf['printer_feie']['deviceNo'],$content,$printerConf['printer_feie']['times']);
				
				}elseif($printer['type'] == '2'){//365 s1
					$printerConf = json_decode($printer['print_data'],true);
					if(empty($printer['print_type'])){//判断打印类型
						$content = empty($printerConf['printer_365s1']['times']) ? '^N1^F1' . "\n" : '^N' . $printerConf['printer_365s1']['times'] . '^F1' . "\n";
						$content .= $storeInfo['name']."\n\n";
						if($log['order_type'] != '2'){
							$content .= "^H2取餐号 ".$orderNumber."\n\n";
							if(!empty($deskInfo['name'])){
								$content .= "^H2桌号 ".$deskInfo['name']."\n\n";
							}
						}else{
							$content .= "订单号 ".$log['ordersn']."\n";
						}
						$content .= setSpacing("品名",22).setSpacing("数量",5).setSpacing("金额",5)."\n";
						$content .= "--------------------------------\n";
						foreach ($goodsArr as $index => $goodsInfo) {
                            if (empty($goodsInfo['goodsid'])) {
                                if($index >0){
                                    $content .= "\n";
                                }
                                $content .= setSpacing($goodsInfo['name'],22).setSpacing($goodsInfo['count'],5).setSpacing($goodsInfo['price'],5)."\n";
                                if (!empty($goodsInfo['optionsId']) && !empty($goodsInfo['optionName'])) {
                                    $content .= $goodsInfo['optionName']."\n";
                                }
                            } else {
                                //老版
                                if(empty($goodsInfo['hasoption']) && $goodsInfo['count']>0){
                                    if($index >0){
                                        $content .= "\n";
                                    }
                                    $content .= setSpacing($goodsInfo['name'],22).setSpacing($goodsInfo['count'],5).setSpacing($goodsInfo['totalprice'],5)."\n";
                                }else{
                                    if(is_array($goodsInfo['options'])){
                                        foreach ($goodsInfo['options'] as $optionsIndex => $optionsInfo) {
                                            if($optionsInfo['count']>0){
                                                if($index > 0 || $optionsIndex > 0){
                                                    $content .= "\n";
                                                }
                                                $content .= setSpacing($goodsInfo['name'],22).setSpacing($optionsInfo['count'],5).setSpacing($optionsInfo['price'],5)."\n";
                                                $content .= $optionsInfo['name']."\n";
                                            }
                                        }
                                    }
                                }
                            }

						}
						if($log['order_type'] == '2'){
							if($log['pbox_fee'] > 0){
								$content .= setSpacing("餐盒费",22).setSpacing("",5).setSpacing($log['pbox_fee'],5)."\n";
							}
							$content .= setSpacing("配送费",22).setSpacing("",5).setSpacing($log['send_fee'],5)."\n";
						}
						$content .= "--------------------------------\n";
						
						$content .= "支付时间：".date('Y-m-d H:i:s')."\n\n";
						if($log['enoughdeduct'] > 0){
							$content .= "满减优惠：".$log['enoughdeduct']."元\n\n";
						}
						if($log['coupon_price'] > 0){
							$content .= "优惠券优惠：".$log['coupon_price']."元\n\n";
						}
						$content .= "实际支付：".$payMoney."元\n\n";
						$content .= "支付方式：".$payTypeStr."\n\n";
						$content .= "^H2备注：".$log['remark']."\n";
						if($log['order_type'] == '2'){
							$addressinfo = json_decode($log['address_info'],true);
							$content .= "\n^H2".$addressinfo['realname']." ".$addressinfo['telphone']."\n^H2".$addressinfo['address']." ".$addressinfo['address_road']." ".$addressinfo['number']."\n";
						}
					}else{
						$printerConf = json_decode($printer['print_data'],true);
						//分单打印
						$content = '';
						foreach ($goodsArr as $index => $goodsInfo) {
							//获取商品分类
							$goodsClass = pdo_getcolumn("deamx_food_goods",array('uniacid'=>$uniacid,'id'=>$goodsInfo['goodsid']), 'class_id',1);
							$print_class = json_decode($printer['print_class'],true);
							if(@in_array($goodsClass, $print_class)){
								if(empty($goodsInfo['hasoption']) && $goodsInfo['count']>0){
									$x = 1;
									while($x <= $goodsInfo['count']){
										if($log['order_type'] != '2'){
											$content .= "^H2取餐号 ".$orderNumber."\n";
											if(!empty($deskInfo['name'])){
												$content .= "^H2桌号 ".$deskInfo['name']."\n";
											}
										}else{
											$content .= "订单号 ".$log['ordersn']."\n";
										}
										$content .= "^H2".$goodsInfo['name']. (!empty($goodsInfo['optionsId']) && !empty($goodsInfo['optionName'])) ? '-'.$goodsInfo['optionName'] : '' ." x1\n";
										$content .= "^H2备注：".$log['remark']."\n\n";
										$content .= "--------------------------------\n";
										$x++;
									}
								}else{
									if(is_array($goodsInfo['options'])){
										foreach ($goodsInfo['options'] as $optionsIndex => $optionsInfo) {
											if($optionsInfo['count']>0){
												$x = 1;
												while($x <= $optionsInfo['count']){
													if($log['order_type'] != '2'){
														$content .= "^H2取餐号 ".$orderNumber."\n";
														if(!empty($deskInfo['name'])){
															$content .= "^H2桌号 ".$deskInfo['name']."\n";
														}
													}else{
														$content .= "订单号 ".$log['ordersn']."\n";
													}
													$content .= "^H2".$goodsInfo['name']."-".$optionsInfo['name']." x1\n";
													$content .= "^H2备注：".$log['remark']."\n\n";
													$content .= "--------------------------------\n";
													$x++;
												}
											}
										}
									}
								}
							}
							
						}
					}
					$result = $print->printer365s1($printerConf['printer_365s1']['deviceNo'],$printerConf['printer_365s1']['key'],$content,$printerConf['printer_365s1']['times']);
				}
			}
		}
	}
}
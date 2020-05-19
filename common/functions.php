<?php
/**
 * 自定义函数
 *
 * @author 点沐
 * @url 
 */

function show_json($status = 1, $return = NULL){
	$ret = array('status' => $status, 'result' => ($status == 1 ? array('url' => referer()) : array()));
	if (!(is_array($return))) 
	{
		if ($return) 
		{
			$ret['result']['message'] = $return;
		}
		exit(json_encode($ret));
	}
	else 
	{
		$ret['result'] = $return;
	}
	if (isset($return['url'])) 
	{
		$ret['result']['url'] = $return['url'];
	}
	else if ($status == 1) 
	{
		$ret['result']['url'] = referer();
	}
	exit(json_encode($ret));
}
function dmx($name = ""){
	static $_deamManage = array( );
	if( isset($_deamManage[$name]) ) 
	{
		return $_deamManage[$name];
	}
	$names = explode("/", $name);
	$model = DM_ROOT_MANAGE . "wxapp/" . strtolower($name) . ".php";
	if( !is_file($model) )
	{
		exit( " Model " . $name . " Not Found!" );
	}
	require_once(DM_ROOT_MANAGE . "wxapp/manage.php");
	require_once($model);
	if(!empty($names[1])){
		$class_name = ucfirst($names[1]) . "_DeamFoodManage";
	}else{
		$class_name = ucfirst($name) . "_DeamFoodManage";
	}
	
	$_modules[$name] = new $class_name();
	return $_modules[$name];
}
function imx($name = ""){
    static $_deamManage = array( );
    if( isset($_deamManage[$name]) )
    {
        return $_deamManage[$name];
    }
    $names = explode("/", $name);
    $model = DM_ROOT . "/wxapp/" . strtolower($name) . ".php";
    if( !is_file($model) )
    {
        exit( " Model " . $name . " Not Found!" );
    }
    require_once(DM_ROOT . "/wxapp/index.php");
    require_once($model);
    if(!empty($names[1])){
        $class_name = ucfirst($names[1]);
    }else{
        $class_name = ucfirst($name);
    }

    $_modules[$name] = new $class_name();
    return $_modules[$name];
}
function m($name = "") {
	static $_modules = array( );
	if( isset($_modules[$name]) ) 
	{
		return $_modules[$name];
	}
	$model = DM_ROOT_CORE . "model/" . strtolower($name) . ".php";
	if( !is_file($model) )
	{
		exit( " Model " . $name . " Not Found!" );
	}
	require_once($model);
	$class_name = ucfirst($name) . "_DeamFoodModel";
	$_modules[$name] = new $class_name();
	return $_modules[$name];
}
function com($name = "") {
	static $_coms = array( );
	if( isset($_coms[$name]) ) 
	{
		return $_coms[$name];
	}
	
	$model = DM_ROOT_CORE . "com/" . strtolower($name) . ".php";
	if( !is_file($model) ) 
	{
		return false;
	}
	require_once($model);
	$class_name = ucfirst($name) . "_DeamFoodComModel";
	$_coms[$name] = new $class_name($name);
	return $_coms[$name];
}
function com_run($name = ""){
	$names = explode("::", $name);
	$com = com($names[0]);
	if(!$com){
		return false;
	}
	if(!method_exists($com, $names[1])){
		return false;
	}
	$func_args = func_get_args();
	$args = array_splice($func_args, 1);
	return call_user_func_array(array($com, $names[1]), $args);
}
function formatQueryParaMap($paraMap, $urlencode){
	$buff = "";
	ksort($paraMap);
	foreach ($paraMap as $k => $v){
		if (null != $v && "null" != $v && "sign" != $k) {
		    if($urlencode){
			   $v = urlencode($v);
			}
			$buff .= $k . "=" . $v . "&";
		}
	}
	$reqPar;
	if (strlen($buff) > 0) {
		$reqPar = substr($buff, 0, strlen($buff)-1);
	}
	return $reqPar;
}
function curl_post($url,$data){
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_POST, 1);//发送一个常规的Post请求
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);//Post提交的数据包
    $rv = curl_exec($curl);//输出内容
    curl_close($curl);
    return $rv;
}
function httpcurl($url){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$output = curl_exec($ch);//输出内容
	curl_close($ch);
	return $output;
}
function arrayToXml($arr){
    $xml = "<xml>";
    foreach ($arr as $key=>$val)
    {
    	 if (is_numeric($val))
    	 {
    	 	$xml.="<".$key.">".$val."</".$key.">"; 

    	 }
    	 else{
    	 	$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
    	 } 
    }
    $xml.="</xml>";
    return $xml;
}
function orderquery($res,$wechat){
	$orderQueryUrl = 'https://api.mch.weixin.qq.com/pay/orderquery';
	$input = array();
	$input['appid'] = $res['appid'];
	$input['mch_id'] = $res['mch_id'];
	$input['out_trade_no'] = $res['out_trade_no'];
	$input['nonce_str'] = random(32);
	if(!empty($res['sub_mch_id'])){
        $input['sub_mch_id'] = $res['sub_mch_id'];
    }
	$unSignParaStr = formatQueryParaMap($input,false);
	$signStr = $unSignParaStr."&key=".$wechat['apikey'];
	$input['sign'] = strtoupper(md5($signStr));
	
	$orderQueryInfo = simplexml_load_string(curl_post($orderQueryUrl,arrayToXml($input)),'SimpleXMLElement', LIBXML_NOCDATA);//红包预下单返回信息
	$orderQueryInfo = json_decode(json_encode($orderQueryInfo),true);	
	return $orderQueryInfo;
}
function json_encode_ex($value){
	if(version_compare(PHP_VERSION,'5.4.0','<')){
		$str = json_encode($value);
		$str = preg_replace_callback(
			"#\\\u([0-9a-f]{4})#i",
			function($matchs){
				return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
			},
			$str
		);
		return $str;
	}else{
		return json_encode($value, JSON_UNESCAPED_UNICODE);
	}
}
function getSignature($str, $key) {  
    $signature = "";  
    if (function_exists('hash_hmac')) {  
        $signature = base64_encode(hash_hmac("sha1", $str, $key, true));  
    } else {  
        $blocksize = 64;  
        $hashfunc = 'sha1';  
        if (strlen($key) > $blocksize) {  
            $key = pack('H*', $hashfunc($key));  
        }  
        $key = str_pad($key, $blocksize, chr(0x00));  
        $ipad = str_repeat(chr(0x36), $blocksize);  
        $opad = str_repeat(chr(0x5c), $blocksize);  
        $hmac = pack(  
                'H*', $hashfunc(  
                        ($key ^ $opad) . pack(  
                                'H*', $hashfunc(  
                                        ($key ^ $ipad) . $str  
                                )  
                        )  
                )  
        );  
        $signature = base64_encode($hmac);  
    }  
    return $signature;  
}
function is_utf8($str) 
{
	return preg_match('%^(?:' . "\r\n" . '            [\	\
\
\ -\~]              # ASCII' . "\r\n" . '            | [\?-\?][\?-\?]             # non-overlong 2-byte' . "\r\n" . '            | \?[\?-\?][\?-\?]         # excluding overlongs' . "\r\n" . '            | [\?-\?\?\?][\?-\?]{2}  # straight 3-byte' . "\r\n" . '            | \?[\?-\?][\?-\?]         # excluding surrogates' . "\r\n" . '            | \?[\?-\?][\?-\?]{2}      # planes 1-3' . "\r\n" . '            | [\?-\?][\?-\?]{3}          # planes 4-15' . "\r\n" . '            | \?[\?-\?][\?-\?]{2}      # plane 16' . "\r\n" . '            )*$%xs', $str);
}
function setSpacing($str, $length = 32) 
{
	$str_old = $str;
	$str = ((is_utf8($str) ? iconv('utf-8', 'gb2312', $str) : $str));
	$num = strlen($str);
	if ($length < $num) 
	{
		if ((32 < $num) && ($length == 32)) 
		{
			$temp = '';
			$count = ceil($num / $length);
			$i = 0;
			while ($i <= $count) 
			{
				$temp .= mb_substr($str_old, $i * $length, $length);
				++$i;
			}
			return $temp;
		}
		return mb_substr($str_old, 0, floor($length / 2), 'utf-8') . str_repeat(' ', $length % 2);
	}
	return $str_old . str_repeat(' ', $length - $num);
}
function wxappMessage($access_token,$touser,$template_id,$page,$form_id,$data,$emphasis_keyword = ""){
	if(empty($access_token)){
		return error(1, 'access_token为空');
	}
	if(empty($touser)){
		return error(1, 'openid为空');
	}
	$postArr = array(
		'touser'			=>	$touser,
		'template_id'		=>	$template_id,
		'page'				=>	$page,
		'form_id'			=>	$form_id,
		'data'				=>	$data,
		'emphasis_keyword'	=>	$emphasis_keyword
	);
	if(empty($emphasis_keyword)){
		unset($postArr['emphasis_keyword']);
	}
	$postJson = json_encode_ex($postArr);
	$url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$access_token;
	$response = ihttp_request($url, $postJson);
	$result = @json_decode($response['content'], true);
	return $result;
}
function messageSubscribe($access_token = '',$toUser = '',$template_id = '',$page = '',$data = ''){
    if(empty($access_token)){
        return error(1, 'access_token为空');
    }
    if(empty($toUser)){
        return error(1, 'openid为空');
    }
    $postArr = array(
        'touser'			=>	$toUser,
        'template_id'		=>	$template_id,
        'page'				=>	$page,
        'data'				=>	$data
    );
    $postJson = json_encode_ex($postArr);
    $url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=".$access_token;
    $response = ihttp_request($url, $postJson);
    $result = @json_decode($response['content'], true);
    return $result;
}
function manage_template($filename, $flag = TEMPLATE_DISPLAY) {
	global $_W;
	$name = 'deam_food';
	if (defined('IN_SYS')) {
		$template = 'default';
		$source = IA_ROOT . '/addons/' . $name . '/manage/template/' . $template . '/' . $filename . '.html';
		$compile = IA_ROOT . '/data/tpl/web/' . $name . '/' . $template . '/manage/' . $filename . '.tpl.php';
	}else{
		$template = 'default';
		$source = IA_ROOT . '/addons/' . $name . '/manage/template/' . $template . '/' . $filename . '.html';
		$compile = IA_ROOT . '/data/tpl/web/' . $name . '/' . $template . '/manage/' . $filename . '.tpl.php';
	}
	if(!is_file($source)) {
		echo "template source '{$filename}' is not exist!";
		return '';
	}
	if(DEVELOPMENT || !is_file($compile) || filemtime($source) > filemtime($compile)) {
		manage_template_compile($source, $compile);
	}
	return $compile;
}

function manage_url($params = array()){
	global $_W,$_GPC;
	if(empty($_W['uniacid'])){
		$_W['uniacid'] = intval($_GPC['i']);
	}
	$url = './index.php?';
	$url .= "i={$_W['uniacid']}&";
	if (!empty($params)) {
		$queryString = http_build_query($params, '', '&');
		$url .= $queryString;
	}
	return $url;
}
function get_last_day($year, $month) {
	return date('t', strtotime($year . '-' . $month . ' -1'));
}
function activity_get_coupon_colors() {
	$colors = array(
		'Color010' => '#55bd47',
		'Color020' => '#10ad61',
		'Color030' => '#35a4de',
		'Color040' => '#3d78da',
		'Color050' => '#9058cb',
		'Color060' => '#de9c33',
		'Color070' => '#ebac16',
		'Color080' => '#f9861f',
		'Color081' => '#f08500',
		'Color082' => '#a9d92d',
		'Color090' => '#e75735',
		'Color100' => '#d54036',
		'Color101' => '#cf3e36',
		'Color102' => '#5e6671',
	);
	return $colors;
}
function curl_post_ssl($url, $vars, $cert, $key,$second=30,$aHeader=array()){
	global $_W;
	$ch = curl_init();
	//超时时间
	curl_setopt($ch,CURLOPT_TIMEOUT,$second);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
	//这里设置代理，如果有的话
	//curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
	//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
	
	//以下两种方式需选择一种
	//第一种方法，cert 与 key 分别属于两个.pem文件
	curl_setopt($ch,CURLOPT_SSLCERT,$cert);
	curl_setopt($ch,CURLOPT_SSLKEY,$key);
 
	if( count($aHeader) >= 1 ){
		curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
	}
 
	curl_setopt($ch,CURLOPT_POST, 1);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
	$data = curl_exec($ch);
	if($data){
		curl_close($ch);
		return $data;
	}
	else { 
		$error = curl_errno($ch);
		echo "call faild, errorCode:$error\n"; 
		curl_close($ch);
		return false;
	}
}
function getOptionCompose($array = array()){
    $len = 1;
    $arrLen = count($array); //需要排列数组有多少个
    $recIndex = null; //记录当前该取的位置
    //foreach 计数
    $count_3 = 0;
    foreach ($array as $key => $value) {
        $lenRec[$count_3] = count($value);
        $len = $lenRec[$count_3]*$len;
        $recIndex[] = 0;//第一次全部取第0个
        $count_3++;
    }
    //算出% 的值
    $count = 1;
    foreach ($lenRec as $key => $value) {
        $moduloVal = 1;

        if($arrLen == $count){
            $modulo[] = count($array[$arrLen-1]); //等于最后一个的长度
        }else{
            $count_1 = 1;
            foreach ($lenRec as $index => $item) {
                $count_1 > $count && $moduloVal = $moduloVal*$item;
                $count_1 ++;
            }
            $modulo[] = $moduloVal;
        }
        $count ++;//为了防止$d key是有值的 不是自然序列 需要计数
    }
    $i = 1;
    $newArr = array();
    while ( $i <= $len) {
        $html = array();
        $count_2 = 0;// 取模
        $temp = '';
        foreach ($array as $value) {
            $html []= $value[$recIndex[$count_2]%$lenRec[$count_2]];
            $count_2 ++;
        }

        $newArr[] = $html;
        foreach ($modulo as $key => $value) {
            if($i%$value == 0 && $key < $arrLen - 1 ){
                $recIndex[$key] = $recIndex[$key] +1;
            }
            if($key == $arrLen - 1){
                if($i%$value == 0){
                    $recIndex[$key] = 0;
                }else{
                    $recIndex[$key] = $recIndex[$key] +1;
                }
            }
        }
        $i ++;
        //改变获取的位置
    }
    return $newArr;
}
function uploadImage($uploadfile)
{
    global $_W;
    global $_GPC;
    $result["status"] = "error";
    if( $uploadfile["error"] != 0 )
    {
        $result["message"] = "上传失败，请重试！";
        return $result;
    }
    load()->func("file");
    $path = "/images/imfox_moudle/" . $_W["uniacid"];
    if( !is_dir(ATTACHMENT_ROOT . $path) )
    {
        mkdirs(ATTACHMENT_ROOT . $path);
    }
    $_W["uploadsetting"] = array( );
    $_W["uploadsetting"]["image"]["folder"] = $path;
    $_W["uploadsetting"]["image"]["extentions"] = $_W["config"]["upload"]["image"]["extentions"];
    $_W["uploadsetting"]["image"]["limit"] = $_W["config"]["upload"]["image"]["limit"];
    $file = file_upload($uploadfile, "image");
    if( is_error($file) )
    {
        $result["message"] = $file["message"];
        return $result;
    }
    if( function_exists("file_remote_upload") )
    {
        $remote = file_remote_upload($file["path"]);
        if( is_error($remote) )
        {
            $result["message"] = $remote["message"];
            return $result;
        }
    }
    $result["status"] = "success";
    $result["url"] = $file["url"];
    $result["error"] = 0;
    $result["filename"] = $file["path"];
    $result["url"] = tomedia(trim($result["filename"]));
    return $result;
}
function returnSquarePoint($lng, $lat, $distance = 500)
{

    $dlng = 2 * asin(sin($distance / (2 * 6378137)) / cos(deg2rad($lat)));
    $dlng = rad2deg($dlng);

    $dlat = $distance / 6378137;
    $dlat = rad2deg($dlat);
    return array(
        'leftTop' => array(
            'lat' => $lat + $dlat,
            'lng' => $lng - $dlng
        ),
        'rightTop' => array(
            'lat' => $lat + $dlat,
            'lng' => $lng + $dlng
        ),
        'leftBottom' => array(
            'lat' => $lat - $dlat,
            'lng' => $lng - $dlng
        ),
        'rightBottom' => array(
            'lat' => $lat - $dlat,
            'lng' => $lng + $dlng
        )
    );
}
$isSupport = pdo_getcolumn("modules", array('name' => 'deam_food'), 'account_support');
if($isSupport != '2'){
    pdo_update("modules", array('account_support' => '2'), array('name' => 'deam_food'));
    cache_updatecache();
}
require DM_ROOT. '/common/template.func.php';
?>
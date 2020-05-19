<?php
defined('IN_IA') or exit('Access Denied');
load()->func('communication');
class Imdada_DeamFood{
	protected $requestUrl = "http://newopen.imdada.cn";
	public $settings = array();
	public function __construct($shop_id,$appkey,$app_secret) {
		$this->settings = array(
			'app_key' => $appkey,
			'app_secret' => $app_secret,
			'version' => '1.0',
			'format' => 'json',
			'source_id' => $shop_id
		);
	}
	public function buildSignature($data){
		ksort($data);
        $args = "";
        foreach ($data as $key => $value) {
            $args.=$key.$value;
        }
        $args = $this->settings['app_secret'].$args.$this->settings['app_secret'];
        $sign = strtoupper(md5($args));
        return $sign;
	}
	public function addOrder($data){
		$reqParams = $this->bulidRequestParams(json_encode($data));
		$resp = ihttp_request($this->requestUrl.'/api/order/addOrder', json_encode($reqParams), array('CURLOPT_HTTPHEADER' => array('Content-Type: application/json')));
		$resp = json_decode($resp['content'],true);
		return $resp;
		// if($resp['status'] == 'fail'){
		// 	return $this->error_code($resp['errorCode']);
		// }
		// return $resp['result'];//{"distance":53459.0,"fee":51.0}
	}
	public function getCityCode(){
		$reqParams = $this->bulidRequestParams("");
		$resp = ihttp_request($this->requestUrl.'/api/order/cancel/reasons', json_encode($reqParams), array('CURLOPT_HTTPHEADER' => array('Content-Type: application/json')));
		$resp = json_decode($resp['content'],true);
		return $resp;
	}
	public function getReasonList(){
        $reqParams = $this->bulidRequestParams("");
        $resp = ihttp_request($this->requestUrl.'/api/cityCode/list', json_encode($reqParams), array('CURLOPT_HTTPHEADER' => array('Content-Type: application/json')));
        $resp = json_decode($resp['content'],true);
        return $resp;
    }
    /**
     * 取消订单
     * data:业务参数，json字符串
     */
	public function cancelOrder($data){
        $reqParams = $this->bulidRequestParams(json_encode($data));
        $resp = ihttp_request($this->requestUrl.'/api/order/formalCancel', json_encode($reqParams), array('CURLOPT_HTTPHEADER' => array('Content-Type: application/json')));
        $resp = json_decode($resp['content'],true);
        return $resp;
    }
	/**
     * 构造请求数据
     * data:业务参数，json字符串
     */
    public function bulidRequestParams($body){
        $requestParams = array();
        $requestParams['app_key'] = $this->settings['app_key'];
        $requestParams['body'] = $body;
        $requestParams['format'] = $this->settings['format'];
        $requestParams['v'] = $this->settings['version'];
        $requestParams['source_id'] = $this->settings['source_id'];
        $requestParams['timestamp'] = time();
        $requestParams['signature'] = $this->buildSignature($requestParams);
        return $requestParams;
    }
	public function error_code($code, $errmsg = '未知错误') {
		$errors = array(
			'-1' => '系统异常',
			'0' => '请求成功',
			'999' => '系统维护中,暂时不能发单',
			'1994' => 'format值不正确,默认为json',
			'1995' => 'body值不能为null',
			'1996' => 'v的值不正确,默认为1.0',
			'1997' => '原始订单order_id不能为空',
			'1998' => '请求参数的个数不正确,请仔细核对',
			'1999' => '接口请求的headers为application/json',
			'2000' => '接口请求参数不能为空',
			'2001' => 'app_key无效',
			'2002' => 'app_key没有绑定上线',
			'2003' => '签名错误',
			'2004' => '无效的门店编号',
			'2005' => '订单不存在,请核查订单号',
			'2006' => '订单回调URL不存在',
			'2007' => '参数query需按要求传值',
			'2008' => 'token不能为空',
			'2009' => 'timestamp不能为空',
			'2010' => 'signature不能为空',
			'2011' => '达达订单不存在,数据异常',
			'2012' => '订单正在处理中,请稍后再试',
			'2013' => '请求接口参数异常,请查看开发文档参数设定',
			'2043' => '门店未审核',
			'2044' => '城市尚未开通',
			'2045' => '商家支付账号不存在',
			'2047' => '运费服务不可用',
			'2048' => '订单取消原因ID不能为空',
			'2049' => '订单取消原因ID对应其他,取消原因不能为空',
			'2050' => '订单配送中,无法取消',
			'2051' => '订单已完成配送,无法取消',
			'2052' => '订单已过期,无法取消',
			'2053' => '订单取消失败',
			'2054' => '小费不能为空或者字符,必须为数字',
			'2055' => '小费额度不能少于1元',
			'2056' => '城市区号不能为空',
			'2057' => '新增小费不能小于原来订单小费的值',
			'2058' => '添加小费失败',
			'2059' => '只有在待接单的情况下才能加小费',
			'2060' => '新的订单,不能重新发单,请走正常发单流程',
			'2061' => '只有已取消、已过期、投递异常的订单才能重发',
			'2062' => '订单价格信息已过期,请重新查询后发单',
			'2063' => '该订单已发布,请选择新的订单',
			'2064' => '该订单状态不是已取消、已过期、投递异常,请选择新的订单',
			'2065' => '该平台订单编号已处理,请勿重复请求',
			'2066' => '该平台订单编号已处理,请勿重复请求',
			'2067' => '该订单运费已查询,请稍后再试',
			'2068' => '接口仅测试环境可用',
			'2069' => '追加的订单与门店不匹配',
			'2070' => '追加的订单已被接单',
			'2071' => '追加的配送员不符合追加要求',
			'2072' => '订单没有追加记录',
			'2073' => '订单状态已经改变,取消失败',
			'2074' => '取消追加订单失败',
			'2075' => '投诉原因不能为空',
			'2076' => '订单已取消,无法重复取消',
			'2077' => '小费金额不能大于订单金额',
			'2078' => 'deliveryNo不能为空',
			'2079' => '收货人纬度lat异常,请检查是否有问题',
			'2080' => '收货人经度lng异常,请检查是否有问题',
			'2104' => '原始订单origin_id不能为空',
			'2105' => '订单已下发,如要重发,请使用重发接口',
			'2106' => 'pay_for_supplier_fee不能为空',
			'2107' => 'fetch_from_receiver_fee不能为空',
			'2108' => 'deliver_fee不能为空',
			'2109' => 'is_prepay不能为空',
			'2110' => 'App密码设置不符合要求,必须包含数字和字母,长度在6~16内',
			'2111' => 'cargo_type不能为空',
			'2112' => 'cargo_weight不能为空',
			'2113' => 'cargo_price不能为空',
			'2114' => 'supplier_name不能为空',
			'2115' => 'supplier_address不能为空',
			'2116' => 'supplier_phone不能为空',
			'2117' => 'supplier_tel不能为空',
			'2118' => 'supplier_lat不能为空',
			'2119' => 'supplier_lng不能为空',
			'2120' => 'receiver_name不能为空',
			'2121' => 'receiver_address不能为空',
			'2122' => 'receiver_phone不能为空',
			'2123' => 'callback不能为空',
			'2124' => 'expected_fetch_time不能为空',
			'2125' => 'expected_finish_time不能为空',
			'2126' => 'city_code不能为空',
			'2127' => 'invoice_title不能为空',
			'2128' => 'receiver_lat或receiver_lng不能为空',
			'2129' => '无效的收货地址,解析地址坐标失败',
			'2130' => '该订单已发单,不能查询运费',
			'2131' => 'source_id值不正确,测试环境默认为73753',
			'2132' => '门店已下线,不能发单',
			'2133' => '投诉原因id不存在,请重新选择',
			'2134' => '投诉失败',
			'2135' => '订单状态为待接单,不能投诉',
			'2136' => '门店尚未绑定商品类型',
			'2137' => '订单异常,配送员信息不存在',
			'2138' => 'receiver_lat或receiver_lng值不能为null',
			'2139' => '数据异常,receiver_lat大于receiver_lng值',
			'2140' => '订单已重发,请稍后再试',
			'2141' => '门店已下线,请至开放平台激活门店',
			'2155' => '余额不足，请充值',
			'2170' => '期望完成时间不合法',
			'2200' => 'accountId不能为空',
			'2201' => 'password不能为空',
			'2300' => 'accountId不能为空',
			'2301' => 'merhcantId不能为空',
			'2400' => '该商家尚未审核上线',
			'2401' => '商家不存在',
			'2402' => '门店不存在',
			'2403' => '门店编号已存在',
			'2404' => '城市名称city_name不正确',
			'2405' => '区域名称area_name不正确',
			'2406' => '没有可以更新的参数,请重新核对',
			'2407' => '业务类型不存在,请重新选择',
			'2408' => '门店状态不存在,请重新选择',
			'2409' => '新的门店编号不能与现有的门店编号相同',
			'2410' => '参数类型不正确, Double类型不能传null与字符串',
			'2411' => '参数类型不正确, pay_for_supplier_fee为Double类型',
			'2412' => '参数类型不正确, fetch_from_receiver_fee为Double类型',
			'2413' => '参数类型不正确, deliver_fee为Double类型',
			'2414' => '参数类型不正确, cargo_type值不在展示的列表中',
			'2415' => '参数类型不正确, cargo_weight为Double',
			'2416' => '参数类型不正确, cargo_num为Integer',
			'2417' => '参数类型不正确, 可以不传,但是不能传null值',
			'2418' => 'expected_fetch_time为unix时间戳,精确到秒(10位)',
			'2419' => 'body参数json解析出错,请检查body内的参数格式是否正确',
			'2420' => 'QA环境禁止修改11047059门店编号',
			'2421' => 'order_mark_no格式不正确,仅包含数字(长度小于15)或为空',
			'2422' => 'order_mark格式不正确,仅包含字母(长度小于10)或为空',
			'2423' => '重复的用户名',
			'2424' => '门店编码不能重复',
			'2425' => 'C端用户的商家不能绑定',
			'2426' => '已与当前商家绑定',
			'2427' => '已与其他商家绑定',
			'2428' => '门店编码已存在',
			'2429' => '查询余额失败',
			'2430' => '账户余额为负,无法绑定',
			'2431' => '未找到该用户名的门店或用户名密码错误',
			'2432' => '城市名称不正确',
			'2433' => '区域名称不正确',
			'2434' => '没有有效的数据',
			'2454' => '保价费不能为空或者字符,必须为数字(1,3,5,15)',
			'2455' => '网络异常,查询账户余额失败,请重试',
			'2456' => '运费账户余额不足,无法使用保价',
			'2457' => '取消失败[cancel_reason_id不在取消原因列表中]',
			'2458' => '取消失败[订单状态为9,不能取消]',
			'2459' => '取消失败[订单状态为10,不能取消]',
			'2460' => '取消失败[订单状态不为待接单和待取货]',
			'2461' => '门店编号格式错误,支持数字字母以及下划线和-',
			'2462' => '充值金额格式不正确,可以精确到分',
			'2463' => '获取充值Url失败,请重试',
			'2464' => '集合单已被接收',
			'2465' => '请选择合适的场景(category)',
			'2466' => '选择生成微信支付链接,open_id不能为空',
		);
		$code = strval($code);
		if($errors[$code]) {
			return $errors[$code];
		} else {
			return $errmsg;
		}
	}
}
?>
<?php
/**
 * 本破解程序由VIP资源网提供
 * www.vip-zyw.com 精品源码VIP资源网免费下载
 *
 */
defined('IN_IA') or exit('Access Denied');
class Deam_foodModuleReceiver extends WeModuleReceiver {
	public function receive() {
		global $_W;
		load()->model('mc');
		$type = $this->message['type'];
		$c = '';
        foreach ($this->message as $key => $value) {
            $c .= "$key : $value \r\n";
        }
        $c .= "uniacid : ".$_W['uniacid']." \r\n";
        load()->func('logging');
        logging_run($c);
        if ($this->message['event'] == 'user_get_card') {
        	if (empty($this->message['isgivebyfriend'])) {
        		$outer_str = intval($this->message['outerstr']);
        		if(empty($outer_str)){
        			$coupon_record = pdo_get('deamx_food_coupon_record', array('card_id' => trim($this->message['cardid']), 'openid' => trim($this->message['fromusername']), 'status' => 1, 'code' => ''), array('id'));
        		}else{
        			$coupon_record = pdo_get('deamx_food_coupon_record', array('card_id' => trim($this->message['cardid']), 'id' => $outer_str, 'status' => 1, 'code' => ''), array('id'));
        		}
				
				if (!empty($coupon_record)) {
					pdo_update('deamx_food_coupon_record', array('code' => trim($this->message['usercardcode'])), array('id' => $coupon_record['id']));
				} else {
					$fans_info = mc_fansinfo($this->message['fromusername']);
					$coupon_info = pdo_get('deamx_food_coupon', array('card_id' => $this->message['cardid']));
					if($coupon_info['type'] == '2'){
						//社交立减券
						exit('0');
					}
					$coupon_info['date_info'] = iunserializer($coupon_info['date_info']);
					if ($coupon_info['date_info']['time_type'] == 1) {
						$starttime = strtotime(str_replace(".","-",$coupon_info['date_info']['time_limit_start']));
						$endtime = strtotime(str_replace(".","-",$coupon_info['date_info']['time_limit_end']));
					} elseif($coupon_info['date_info']['time_type'] == 2) {
						if($coupon_info['date_info']['deadline'] > '0'){
							$startday = intval($coupon_info['date_info']['deadline']);
						}else{
							$startday = 0;
						}
						$starttime = strtotime(date("Y-m-d"),time()) + $startday * 24 * 60 * 60;
						$endtime = strtotime(date("Y-m-d"),time()) + $startday * 24 * 60 * 60 + $coupon_info['date_info']['limit'] * 24 * 60 * 60-1;
					}
					//获取unionid
					load()->func('communication');
					load()->classs('wxapp.account');
					$accObj= WxappAccount::create($_W['uniacid']);
					$wxapp_access_token = $accObj->getAccessToken();
					$getUrl = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$wxapp_access_token."&openid=".$this->message['fromusername']."&lang=zh_CN";
					$getStatusResult = ihttp_get($getUrl);
					$memberInfo = json_decode($getStatusResult['content'],true);
					$pcount = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('deamx_food_coupon_record') . " WHERE `openid` = :openid AND `couponid` = :couponid", array(':couponid' => $coupon_info['id'], ':openid' => trim($this->message['fromusername'])));
					if ($pcount < $coupon_info['get_limit'] && $coupon_info['quantity'] > 0) {
						$insert_data = array(
							'uniacid' => $_W['uniacid'],
							'card_id' => $this->message['cardid'],
							'openid' => $this->message['fromusername'],
							'code' => $this->message['usercardcode'],
							'addtime' => TIMESTAMP,
							'status' => '1',
							'uid' => $fans_info['uid'],
							'grantmodule' => 'deam_food',
							'remark' => '用户通过投放扫码',
							'couponid' => $coupon_info['id'],
							'granttype' => 2,
							'unionid' => $memberInfo['unionid'],
							'starttime' => $starttime,
							'endtime' => $endtime
						);
						pdo_insert('deamx_food_coupon_record', $insert_data);
						pdo_update('deamx_food_coupon', array('quantity -=' => 1, 'dosage +=' => 1), array('coupon_uniacid' => $_W['uniacid'], 'id' => $coupon_info['id']));
					}
				}
			} else {
				//获取unionid
				load()->func('communication');
				load()->classs('wxapp.account');
				$accObj= WxappAccount::create($_W['uniacid']);
				$wxapp_access_token = $accObj->getAccessToken();
				$getUrl = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$wxapp_access_token."&openid=".$this->message['fromusername']."&lang=zh_CN";
				$getStatusResult = ihttp_get($getUrl);
				$memberInfo = json_decode($getStatusResult['content'],true);

				$old_record = pdo_get('deamx_food_coupon_record', array('openid' => trim($this->message['friendusername']), 'card_id' => trim($this->message['cardid']), 'code' => trim($this->message['oldusercardcode'])));
				pdo_update('deamx_food_coupon_record', array('unionid' => $memberInfo['unionid'],'addtime' => TIMESTAMP, 'givebyfriend' => intval($this->message['isgivebyfriend']), 'openid' => trim($this->message['fromusername']), 'code' => trim($this->message['usercardcode']),'friend_openid' => trim($this->message['friendusername']), 'status' => 1), array('id' => $old_record['id']));
			}
        } elseif ($this->message['event'] == 'user_del_card') {
			//用户删除卡券事件
			$card_id = trim($this->message['cardid']);
			$openid = trim($this->message['fromusername']);
			$code = trim($this->message['usercardcode']);
			pdo_update('deamx_food_coupon_record', array('status' => 4), array('card_id' => $card_id, 'openid' => $openid, 'code' => $code));
		} elseif ($this->message['event'] == 'user_consume_card') {
			//核销卡券事件
			$card_id = trim($this->message['cardid']);
			$openid = trim($this->message['fromusername']);
			$code = trim($this->message['usercardcode']);
			pdo_update('deamx_food_coupon_record', array('status' => 3,'usetime'=>TIMESTAMP), array('card_id' => $card_id, 'openid' => $openid, 'code' => $code));
		} elseif ($this->message['event'] == 'user_gifting_card') {
			$card_id = trim($this->message['cardid']);
			$openid = trim($this->message['fromusername']);
			$code = trim($this->message['usercardcode']);
			pdo_update('deamx_food_coupon_record', array('status' => '-1'), array('card_id' => $card_id, 'openid' => $openid, 'code' => $code));
		}
	}
}
?>
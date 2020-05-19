<?php
defined('IN_IA') or exit('Access Denied');
if (checksubmit() || $_W['isajax']) {
	_login(manage_url(array('r'=>'store','ac'=>'index')));
}
include manage_template('user/login');
function _login($forward = '') {
	global $_GPC,$_W;
	$username = trim($_GPC['username']);
	if (empty($username)) {
		itoast('请输入要登录的用户名', '', '');
	}
	if (empty($_GPC['password'])) {
		itoast('请输入密码', '', '');
	}
	$record = pdo_get("deamx_food_store_clerk",array('name'=>$username,'password'=>md5(DM_PWDPRE.$_GPC['password'])));
	if(!empty($record)){
		if(empty($record['status'])){
			itoast('该账号已被禁用，无法登陆', '', '');
			exit();
		}
		$cookie = array();
		$cookie['uid'] = $record['id'];
		$cookie['lastvisit'] = $record['lastvisit'];
		$cookie['lastip'] = $record['lastip'];
		$cookie['hash'] = md5($record['password'] . DM_PWDPRE);
		$session = authcode(json_encode($cookie), 'encode');
		isetcookie('__DM_food_session', $session, !empty($_GPC['rember']) ? 7 * 86400 : 0, true);
		pdo_update("deamx_food_store_clerk",array('lastvisit'=>TIMESTAMP,'lastip'=>CLIENT_IP),array('id'=>$record['id']));
		itoast("欢迎回来，{$record['name']}。", $forward , 'success');
	}else{
		itoast('登录失败，请检查您输入的用户名和密码！', '', '');
	}
}
?>
<?php
/**
 * 本破解程序由VIP资源网提供
 * VIP资源网 www.vip-zyw.com
 *
 */
define('IN_SYS', true);
require_once '../../framework/bootstrap.inc.php';
require IA_ROOT . '/web/common/bootstrap.sys.inc.php';
!(defined('DM_PATH')) && define('DM_PATH', str_replace('\\', '/', dirname(__FILE__)) . '/');

$url = $_W['siteroot'];
$url = explode('addons', $url);
$manageUrl = $url[0] . "addons/deam_food/manage/index.php?i=" . intval($_GPC['i']) . "&r=store&ac=index";
$loginUrl = $url[0] . "web/index.php?c=user&a=login&referer=" . urlencode($manageUrl);
if (!empty($_W['uid'])) {
	$user = pdo_get("deamx_food_store_clerk",array('system_uid'=>$_W['uid']));
	if (is_array($user)) {
		$uniacid = $_W['uniacid'] = $_W['weid'] = $user['uniacid'];
		$_W['deam_food']['manage']['uid'] = $user['id'];
		$_W['deam_food']['manage']['username'] = $user['name'];
		$user['currentip'] = $user['lastip'];
		$user['lastip'] = $session['lastip'];
		$_W['deam_food']['manage']['user'] = $user;
      	header("Location:" . $manageUrl);
	} else {
		//itoast('禁止访问，请使用店员账号进行登陆！', $loginUrl, 'error');
		header("Location:" . $loginUrl);
	}
	unset($user);
}else{
	header("Location:" . $loginUrl);
}
?>
<?php
defined('IN_IA') or exit('Access Denied');

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
	} else {
        include manage_template("login_error");
		exit("");
	}
	unset($user);
}else{
	header("Location:" . $loginUrl);
}
?>
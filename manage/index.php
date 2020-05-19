<?php
define('IN_SYS', true);
require_once '../../../framework/bootstrap.inc.php';
require_once IA_ROOT . '/web/common/bootstrap.sys.inc.php';
define('DM_PATH', str_replace('\\', '/', dirname(__FILE__)) . '/');
define('DM_TEMPLATE_PATH', DM_PATH . 'template/');
define('DM_CONTROLLER_PATH', DM_PATH . 'controller/');
require_once IA_ROOT . '/addons/deam_food/common/defines.php';
require_once DM_ROOT. '/common/functions.php';
!(defined('MODULE_ROOT')) && define('MODULE_ROOT', DM_ROOT);
global $_W,$_GPC;
$controller = empty($_GPC['r']) ? 'store' : trim($_GPC['r']);
$action = empty($_GPC['ac']) ? 'index' : trim($_GPC['ac']);
$operation = trim($_GPC['op']);
$eid = intval($_GPC['eid']);
$is_admin = intval($_GPC['is_admin']);
if(!empty($is_admin) && empty($_GPC['r']) && empty($_GPC['ac'])){
	checklogin();
	include $this->template("system/sysset/store_controller");
	exit;
}
$url = $_W['siteroot'];
$url = explode('addons', $url);
$dmSiteroot = $url[0];
$_W['siteroot'] = $dmSiteroot;
$_W['attachurl_local'] = str_replace("/addons/deam_food/manage", "", $_W['attachurl_local']);
unset($url);
$uniacid = $_W['uniacid'] = $_W['weid'] = $_GPC['i'];
$_W['uniaccount'] = $_W['account'] = uni_fetch($_W['uniacid']);
$_W['acid'] = $_W['uniaccount']['acid'];
isetcookie ('__uniacid', $uniacid, -1000);

$settings = pdo_get("deamx_food_settings",array('uniacid'=>$uniacid));
require_once DM_PATH . "manage.sys.inc.php";
if ($controller == 'user' && $action == 'login') {
	exit("该控制器已废弃！");
}
// if (empty($_W['deam_food']['manage']['uid'])) {
// 	itoast('', manage_url(array('r'=>'user','ac'=>'login')), 'warning');
// 	exit();
// }else{
// 	if(empty($_W['deam_food']['manage']['user']['status'])){
// 		isetcookie('__DM_food_session', false, -100);
// 		itoast('该账号已被禁用，无法登陆', manage_url(array('r'=>'user','ac'=>'login')), 'error');
// 	}
// }
$store_id = $_W['deam_food']['manage']['user']['store_id'];
$storeinfo = pdo_get("deamx_food_store",array('uniacid'=>$uniacid,'id'=>$store_id), array('id','name', 'payment'));
if(!empty($storeinfo['payment'])){
    $storeinfo['payment'] = iunserializer($storeinfo['payment']);
}
//$uniacid = $_W['uniacid'] = $_W['deam_food']['manage']['user']['uniacid'];
$isMobile = in_array($_W['container'], array('wechat', 'android', 'ipad', 'iphone', 'ipod'));

$frams = array(
	array(
		'title'		=>	'概况',
		'icon'		=>	'fa fa-area-chart',
		'href'		=>	manage_url(array('r'=>'store','ac'=>'index')),
		'active'	=>	($controller == 'store' && $action == 'index') ? 'active' : ''
	),
);
$user_permission = $_W['deam_food']['manage']['user']['permission'];
$user_permissionArr = explode("|", $user_permission);
if(empty($user_permissionArr)){
    isetcookie('__session', '', -10000);
    isetcookie('__switch', '', -10000);
	itoast('该账号无任何操作权限！', manage_url(array('r'=>'user','ac'=>'login')), 'error');
}
if(in_array('store_goods', $user_permissionArr) || in_array('store_class', $user_permissionArr)){
	$frams[] = array(
		'title'		=>	'商品',
		'icon'		=>	'fa fa-tags',
		'href'		=>	manage_url(array('r'=>'goods','ac'=>'index')),
		'active'	=>	($controller == 'goods') ? 'active' : ''
	);
}
if(in_array('store_order', $user_permissionArr)){
	$frams[] = array(
		'title'		=>	'订单',
		'icon'		=>	'fa fa-list',
		'href'		=>	manage_url(array('r'=>'order','ac'=>'index')),
		'active'	=>	($controller == 'order') ? 'active' : ''
	);
}
if(in_array('store_desk', $user_permissionArr)){
	$frams[] = array(
		'title'		=>	'桌号',
		'icon'		=>	'fa fa-cubes',
		'href'		=>	manage_url(array('r'=>'desk','ac'=>'index')),
		'active'	=>	($controller == 'desk') ? 'active' : ''
	);
}
if (in_array('store_printer', $user_permissionArr)){
	$frams[] = array(
		'title'		=>	'打印机',
		'icon'		=>	'fa fa-print',
		'href'		=>	manage_url(array('r'=>'store','ac'=>'printer')),
		'active'	=>	($controller == 'store' && $action == 'printer') ? 'active' : ''
	);
}
if(in_array('store_members', $user_permissionArr)){
    $frams[] = array(
        'title'		=>	'会员',
        'icon'		=>	'icon icon-yonghu',
        'href'		=>	manage_url(array('r'=>'members','ac'=>'index')),
        'active'	=>	($controller == 'members') ? 'active' : ''
    );
}
if (in_array('store_statistics', $user_permissionArr)){
	$frams[] = array(
		'title'		=>	'报表',
		'icon'		=>	'icon icon-baobiao',
		'href'		=>	manage_url(array('r'=>'store','ac'=>'statistics')),
		'active'	=>	($controller == 'store' && $action == 'statistics') ? 'active' : ''
	);
}
//收银台
// $frams[] = array(
// 	'title'		=>	'收银台',
// 	'icon'		=>	'icon icon-desktop',
// 	'href'		=>	manage_url(array('r'=>'store','ac'=>'desktop')),
// 	'target'	=>	'_blank',
// 	'active'	=>	($controller == 'store' && $action == 'desktop') ? 'active' : ''
// );
if(in_array('store_store', $user_permissionArr)){
	$frams[] = array(
		'title'		=>	'设置',
		'icon'		=>	'fa fa-cog',
		'href'		=>	manage_url(array('r'=>'store','ac'=>'settings')),
		'active'	=>	($controller == 'store' && $action == 'settings') ? 'active' : ''
	);
}
$file = __forward($controller,$action);
if (!file_exists($file)) {
	exit("文件" . $file . "不存在");
}
require $file;
exit();

function __forward($c, $a) {
	$file = DM_CONTROLLER_PATH . $c . '/' . $a . '.ctrl.php';
	return $file;
}
?>
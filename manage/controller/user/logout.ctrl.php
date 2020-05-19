<?php
defined('IN_IA') or exit('Access Denied');

$url = $_W['siteroot'];
$url = explode('addons', $url);
$manageUrl = $url[0] . "addons/deam_food/manage/index.php?i=" . intval($_GPC['i']) . "&r=store&ac=index";
isetcookie('__session', '', -10000);
isetcookie('__switch', '', -10000);
header('Location:' . $manageUrl);
?>
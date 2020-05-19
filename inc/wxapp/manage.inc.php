<?php
/**
 * 点餐小程序模块小程序接口定义
 *
 * @author cmszs
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$router = trim($_GPC['r']);
dmx($router)->run();
?>
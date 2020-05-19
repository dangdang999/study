<?php
/**
 * 本破解程序由VIP资源网提供
 * VIP资源网 www.vip-zyw.com
 *
 */
defined('IN_IA') or exit('Access Denied');

class Deam_foodModule extends WeModule {
	public function welcomeDisplay(){
		header('Location:'.url('site/entry',array('m'=>'deam_food','do'=>'store')));
	}
}
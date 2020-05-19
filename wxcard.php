<?php

/**
 * 本破解程序由VIP资源网提供
 * VIP资源网 www.vip-zyw.com
 *
 */
	$path = urldecode($_GET['path']);
	if(!empty($path)){
		header("Location:". $path."&encrypt_code=".$_GET['encrypt_code']."&openid=".$_GET['openid']);
	}
?>
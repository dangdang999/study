<?php
/**
 * 门店管理后台
 *
 * @author cmszs
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
class Login_DeamFoodManage extends DeamFoodManage
{
	public function login(){
		global $_W,$_GPC;
		$username = trim($_GPC['username']);
		$password = trim($_GPC['password']);
		if(empty($username) || empty($password)){
			show_json(0, "用户名或者密码不能为空！");
		}
		load()->model('user');
		$user = array(
			'username'	=>	$username,
			'password'	=>	$password
		);
		$systemRecord = pdo_get("users", array('username' => $username), array('salt','password','uid'));
		if(empty($systemRecord)){
			show_json(0, '用户名不存在！');
		}
		if(user_hash($password, $systemRecord['salt']) != $systemRecord['password']){
			show_json(0, '登录失败，请检查您输入的用户名和密码！');
		}
		$record = pdo_get("deamx_food_store_clerk",array('system_uid'=>$systemRecord['uid']));
		if(!empty($record)){
			if(empty($record['status'])){
				show_json(0, '该账号已被禁用，无法登陆');
			}
			$cookie = array();
			$cookie['uid'] = $record['id'];
			$cookie['system_uid'] = $record['system_uid'];
			$cookie['lastvisit'] = $record['lastvisit'];
			$cookie['lastip'] = $record['lastip'];
			$cookie['hash'] = md5($systemRecord['password'] . $systemRecord['salt']);
			$session = authcode(json_encode($cookie), 'encode');
            $session = urlencode($session);
			pdo_update("deamx_food_store_clerk",array('lastvisit'=>TIMESTAMP,'lastip'=>CLIENT_IP),array('id'=>$record['id']));
			show_json(1, array("message" => "欢迎回来，{$record['name']}。", "session" => $session));
		}else{
			show_json(0, '登录失败，请使用店员账号进行登录！');
		}
		show_json(1);
	}
	public function checklogin(){
		global $_W,$_GPC;
        $session = urldecode($_GPC['session']);
		$session = json_decode(authcode($session), true);
		if (is_array($session)) {
			load()->model('user');
			$user = pdo_get("deamx_food_store_clerk",array('id'=>$session['uid'],'uniacid'=>$_W['uniacid']));
			if(empty($user['system_uid'])){
				show_json(0);
			}
			$systemRecord = pdo_get("users", array('uid' => $user['system_uid']), array('salt','password','uid'));
			
			if (is_array($user) && $session['hash'] == md5($systemRecord['password'] . $systemRecord['salt'])) {
				$_W['deam_food']['manage']['uid'] = $user['id'];
				$_W['deam_food']['manage']['username'] = $user['name'];
				$user['currentip'] = $user['lastip'];
				$user['lastip'] = $session['lastip'];
				$_W['deam_food']['manage']['user'] = $user;
				$store_id = $_W['deam_food']['manage']['user']['store_id'];
				$storeinfo = pdo_get("deamx_food_store",array('uniacid'=>$_W['uniacid'],'id'=>$store_id), array('id','name'));
				$showUserInfo = array(
					'uid'		=>	$user['id'],
					'username'	=>	$user['name'],
					'currentip'	=>	$user['lastip'],
					'lastip'	=>	$session['lastip'],
					'lastvisit'	=>	date("Y-m-d H:i", $user['lastvisit'])
				);
				show_json(1, array('userInfo' => $showUserInfo, 'storeInfo' => $storeinfo));
			} else {
				show_json(0);
			}
		}else{
			show_json(0);
		}
	}
}
?>
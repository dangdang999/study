<?php
/**
 * 门店管理后台
 *
 * @author cmszs
 * @url 
 */
if( !defined("IN_IA") ) 
{
	exit( "Access Denied" );
}
class DeamFoodManage
{


    public function run(){
		global $_W,$_GPC;
		$router = trim($_GPC['op']);
		$this->$router();
	}

}
class imfoxMember extends DeamFoodManage{
    function __construct()
    {
        global $_W,$_GPC;
        $session = urldecode($_GPC['session']);
        $session = json_decode(authcode($session), true);
        //print_r($_GPC['session']);
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

                return array(
                    'status' => '1',
                    array('userInfo' => $showUserInfo, 'storeInfo' => $storeinfo)
                );
            } else {
                show_json(0, "账户信息有误！");
            }
        }else{
            show_json(0, "账户信息有误！");
        }
    }
}
<?php
/**
 * 本破解程序由VIP资源网提供
 * VIP资源网 www.vip-zyw.com
 *
 */
defined('IN_IA') or exit('Access Denied');
require_once IA_ROOT . '/addons/deam_food/common/defines.php';
require DM_ROOT. '/common/functions.php';
class Deam_foodModuleSite extends WeModuleSite {

    public function deamMenu(){
		global $_W,$_GPC;
		$menuArr = array(
            array(
            	'title' => '幻灯片管理00',
                'is_display' => '1',
                'icon' => 'icon icon-swipe-copy',
                'url' => $this->createWebUrl('adv'),
                'active' => ($_GPC['do'] == 'adv') ? 'active' : ''
            ),
            array(
            	'title' => '门店管理',
                'is_display' => '1',
                'icon' => 'icon icon-mendianguanli',
                'url' => $this->createWebUrl('store'),
                'active' => ($_GPC['do'] == 'store' || $_GPC['do'] == 'desk' || $_GPC['do'] == 'goods' || $_GPC['do'] == 'printer') ? 'active' : ''
            ),
            array(
            	'title' => '营销',
                'is_display' => '1',
                'icon' => 'icon icon-yingxiao',
                'url' => $this->createWebUrl('coupon'),
                'active' => ($_GPC['do'] == 'coupon') ? 'active' : ''
            ),
            array(
            	'title' => '会员中心',
                'is_display' => '1',
                'icon' => 'icon icon-yonghu',
                'url' => $this->createWebUrl('members'),
                'active' => ($_GPC['do'] == 'members') ? 'active' : ''
            ),
            array(
            	'title' => '报表',
                'is_display' => '1',
                'icon' => 'icon icon-baobiao',
                'url' => $this->createWebUrl('statistics'),
                'active' => ($_GPC['do'] == 'statistics') ? 'active' : ''
            ),
            array(
            	'title' => '系统设定',
                'is_display' => '1',
                'icon' => 'icon icon-shezhi',
                'url' => $this->createWebUrl('settings'),
                'active' => ($_GPC['do'] == 'settings') ? 'active' : ''
            ),
            array(
            	'title' => '门店操作台',
                'is_display' => '1',
                'icon' => 'icon icon-diannao2',
                'url' => $this->createWebUrl('manage',array('is_admin' => '1')),
                'active' => ($_GPC['do'] == 'manage' || $_GPC['do'] == 'operator') ? 'active' : ''
            )
		);
		return $menuArr;
	}
	public function deamSelected_account(){
		global $_W,$_GPC;
		$wxappInfo = pdo_fetch("SELECT a.uniacid,a.name,v.version,v.id as version_id FROM ".tablename("uni_account")." a left join ".tablename('wxapp_versions')." v ON a.uniacid=v.uniacid WHERE a.uniacid=:uniacid",array(':uniacid'=>$_W['uniacid']));
		return $wxappInfo;
	}
	public function doWebManage(){
		global $_W,$_GPC;
		checklogin();
        $settings = pdo_get("deamx_food_settings",array('uniacid'=>$_W['uniacid']));
        $sysset = m('common')->getSysset();

		include $this->template("system/sysset/store_controller");
	}
	public function doWebPermission(){
		global $_W,$_GPC;
		$operation = empty($_GPC['op']) ? 'list' : trim($_GPC['op']);
		$module_name = trim($_GPC['m']);
		$modulelist = uni_modules(false);
		$module = $_W['current_module'] = $modulelist[$module_name];
		define('IN_MODULE', $module_name);
		if(empty($module)) {
			itoast('抱歉，你操作的模块不能被访问！');
		}
		if(!permission_check_account_user_module($module_name.'_permissions', $module_name)) {
			itoast('您没有权限进行该操作');
		}
		if($operation == 'list'){
			$user_permissions = module_clerk_info($module_name);
			$current_module_permission = module_permission_fetch($module_name);
			$permission_name = array();
			if (!empty($current_module_permission)) {
				foreach ($current_module_permission as $key => $permission) {
					$permission_name[$permission['permission']] = $permission['title'];
				}
			}
			if (!empty($user_permissions)) {
				foreach ($user_permissions as $key => &$permission) {
					if (!empty($permission['permission'])) {
						$permission['permission'] = explode('|', $permission['permission']);
						foreach ($permission['permission'] as $k => $val) {
							$permission['permission'][$val] = $permission_name[$val];
							unset($permission['permission'][$k]);
						}
					}
				}
				unset($permission);
			}
		}elseif($operation == 'post'){
			$uid = intval($_GPC['uid']);
			$user = user_single($uid);
			if (!empty($uid)) {
				$have_permission = permission_account_user_menu($uid, $_W['uniacid'], $module_name);
				if (is_error($have_permission)) {
					itoast($have_permission['message']);
				}
			}

			if (checksubmit()) {
				$insert_user = array(
						'username' => trim($_GPC['username']),
						'remark' => trim($_GPC['remark']),
						'password' => trim($_GPC['password']),
						'repassword' => trim($_GPC['repassword']),
						'type' => ACCOUNT_OPERATE_CLERK
					);
				if (empty($insert_user['username'])) {
					itoast('必须输入用户名，格式为 1-15 位字符，可以包括汉字、字母（不区分大小写）、数字、下划线和句点。');
				}

				$operator = array();
				if (empty($uid)) {
					if (user_check(array('username' => $insert_user['username']))) {
						itoast('非常抱歉，此用户名已经被注册，你需要更换注册名称！');
					}
					if (empty($insert_user['password']) || istrlen($insert_user['password']) < 8) {
						itoast('必须输入密码，且密码长度不得低于8位。');
					}
					if ($insert_user['repassword'] != $insert_user['password']) {
						itoast('两次输入密码不一致');
					}
					unset($insert_user['repassword']);
					$uid = user_register($insert_user);
					if (!$uid) {
						itoast('注册账号失败', '', '');
					}
				} else {
					if (!empty($insert_user['password'])) {
						if (istrlen($insert_user['password']) < 8) {
							itoast('必须输入密码，且密码长度不得低于8位。');
						}
						if ($insert_user['repassword'] != $insert_user['password']) {
							itoast('两次输入密码不一致');
						}
					}
					$operator['password'] = $insert_user['password'];
					$operator['salt'] = $user['salt'];
					$operator['uid'] = $uid;
					$operator['username'] = $insert_user['username'];
					$operator['remark'] = $insert_user['remark'];
					$operator['type'] = $insert_user['type'];
					user_update($operator);
				}
				$permission = $_GPC['module_permission'];
				if (!empty($permission) && is_array($permission)) {
					$permission = implode('|', array_unique($permission));
				} else {
					$permission = 'all';
				}
				if (empty($have_permission)) {
					pdo_insert('users_permission', array('uniacid' => $_W['uniacid'], 'uid' => $uid, 'type' => $module_name, 'permission' => $permission));
				} else {
					pdo_update('users_permission', array('permission' => $permission), array('uniacid' => $_W['uniacid'], 'uid' => $uid, 'type' => $module_name));
				}

				$role = table('users')->userOwnedAccountRole($uid, $_W['uniacid']);
				if (empty($role)) {
					pdo_insert('uni_account_users', array('uniacid' => $_W['uniacid'], 'uid' => $uid, 'role' => 'clerk'));
				} else {
					pdo_update('uni_account_users', array('role' => 'clerk'), array('uniacid' => $_W['uniacid'], 'uid' => $uid));
				}
				itoast('编辑店员资料成功', $this->createWebUrl('permission',array('version_id'=>intval($_GPC['version_id']))), 'success');
			}
			$current_module_permission = module_permission_fetch($module_name);
			if (!empty($uid) && !empty($current_module_permission)) {
				foreach ($current_module_permission as &$data) {
					$data['checked'] = 0;
					if (in_array($data['permission'], $have_permission) || in_array('all', $have_permission)) {
						$data['checked'] = 1;
					}
				}
			}
			unset($data);
		}
		if ($operation == 'delete') {
			$operator_id = intval($_GPC['uid']);
			if (empty($operator_id)) {
				itoast('参数错误', referer(), 'error');
			} else {
				$user = pdo_get('users', array('uid' => $operator_id), array('uid'));
				if (!empty($user)) {
					$delete_account_users = pdo_delete('uni_account_users', array('uid' => $operator_id, 'role' => 'clerk', 'uniacid' => $_W['uniacid']));
					$delete_user_permission = pdo_delete('users_permission', array('uid' => $operator_id, 'type' => $module_name, 'uniacid' => $_W['uniacid']));
					if (!empty($delete_account_users) && !empty($delete_user_permission)) {
						pdo_delete('users', array('uid' => $operator_id));
					}
				}
				itoast('删除成功', referer(), 'success');
			}
		}
		include $this->template('system/sysset/permission');
	}
}
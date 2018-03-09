<?php

defined('IN_IA') or exit('Access Denied');

$uid  = $_W['uid'];
$user = user($uid);
$do   = empty($do) ? 'display' : $do;
if ($do == 'display') {
    $role_uid = $_GPC['uid'];

    $role_user = user($role_uid);
    if (empty($role_user)) {
        message(0, '该用户不存在', null);
    } else {
        message(1, '获取成功', $role_user);
    }
}
if ($do == 'post') {
    $role_uid = $_GPC['uid'];
    $role     = $_GPC['role'];

    $role_user = user($role_uid);
    if (empty($role_user)) {
        message(0, '用户不存在', null);
    }
    if ($user['username'] == 'admin') {
        $update = array(
            'uid'  => $role_uid,
            'role' => iserializer($role),
        );
        $new_user = user_update($update);
        if (is_error($new_user)) {
            message(0, '更新用户权限失败', null);
        } else {
            message(1, '更新用户权限成功', $new_user);
        }
    }
}

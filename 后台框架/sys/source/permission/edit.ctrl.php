<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;
if ($do == 'display') {
    $edit_uid  = $_GPC['uid'];
    $edit_user = user($edit_uid);
    if (empty($edit_user)) {
        message(0, '该用户不存在', null);
    } else {
        message(1, '获取用户信息成功', $edit_user);
    }
}
if ($do == 'post') {
    $uid  = $_W['uid'];
    $user = user($uid);

    if ($user['username'] == 'admin') {
        $edit_uid  = $_GPC['uid'];
        $edit_user = user($edit_uid);
        $data      = array(
            'username' => $_GPC['username'],
            'mobile'   => $_GPC['mobile'],
            'realname' => $_GPC['realname'],
            'avatar'   => $_GPC['avatar'],
            'status'   => $_GPC['status'],
            'remark'   => $_GPC['remark'],
        );
        if (!empty($_GPC['password'])) {
            $data['password'] = $_GPC['password'];
        }
        if (empty($edit_user)) {
            if (is_error(user_register($data))) {
                message(0, $user['message'], null);
            } else {
                message(1, '用户添加成功', null);
            }
        } else {
            $data['uid'] = $edit_uid;
            if (is_error(user_update($data))) {
                message(0, $user['message'], null);
            } else {
                message(1, '用户编辑成功', null);
            }
        }
    } else {
        message(0, '您无此权限', null);
    }
}

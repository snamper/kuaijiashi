<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;

load()->model('mc');

if ($do == 'display') {
    $uid    = $_GPC['uid'];
    $detail = mc_member($uid);
    message(1, '获取用户详情成功', $member);
}

if ($do == 'delete') {
    if (!empty($_GPC['uid'])) {
        $uid = $_GPC['uid'];
        pdo_delete('mc_member', array('uid' => $uid));
        pdo_delete('mc_member_profile', array('uid' => $uid));
        message(1, '会员删除成功！', null);
    } else {
        message(0, '请选择需要删除的会员', null);
    }
}

if ($do == 'updatePassword') {
    $uid      = intval($_GPC['uid']);
    $sql      = 'SELECT `salt` FROM ' . tablename('mc_member') . ' WHERE `uid` = :uid';
    $salt     = pdo_fetchcolumn($sql, array(':uid' => $uid));
    $password = itrim($_GPC['password']);
    if (empty($password) || strlen($password) < 6 || strlen($password) > 18) {
        message(0, '密码不能为空或长度为6到18位', null);
    }
    $password = pwd_hash($password, $salt);
    pdo_update('mc_member', array('password' => $password), array('uid' => $uid));
    message(1, '更新密码成功', null);
}

if ($do == 'post') {
    $uid = intval($_GPC['uid']);

    if (!empty($_GPC)) {
        unset($_GPC['uid']);
        $email_effective = intval($_GPC['email_effective']);
        if (($email_effective == 1 && empty($_GPC['email']))) {
            unset($_GPC['email']);
        }
        $uid = mc_update($uid, $_GPC);
    }
    message(1, '更新资料成功！', null);

}

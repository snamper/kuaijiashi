<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;

if ($do == 'display') {
    $uid  = $_W['uid'];
    $user = user($uid);

    if (empty($user)) {
        message(0, '请先登录/注册', null);
    } else {
        $data = array(
            'userInfo' => $user,
            'token'    => create_sys_accesstoken($user['uid']),
        );
        message(0, '获取用户信息成功', $data);
    }
}

if ($do == 'sendCaptcha') {
    $mobile = $_GPC['mobile'];
    if (!preg_match(REGULAR_MOBILE, $mobile)) {
        message(0, '手机号错误', null);
    }
    $result = captcha_send($mobile, 0);
    if (is_error($result)) {
        message(0, $result['message'], null);
    } else {
        message(1, '验证码已发送', null);
    }

}

if ($do == 'changeMobile') {
    $captcha = $_GPC['captcha'];
    $mobile  = $_GPC['mobile'];
    $result  = captcha_check($mobile, $captcha);
    if (is_error($result)) {
        message(0, $result['message'], null);

    } else {
        $update_user = array(
            'uid'    => $_W['uid'],
            'mobile' => $mobile,
        );
        $update = user_update($update_user);
        if (is_error($update)) {
            message(0, $update['message'], null);

        } else {
            message(1, '更换绑定手机成功', $update);
        }
    }
}

if ($do == 'updatePassword') {
    $oldPass = $_GPC['oldPass'];
    $newPass = $_GPC['newPass'];
    $uid     = $_W['uid'];
    $user    = user($uid, 1);
    if ($user['password'] != pwd_hash($oldPass, $user['salt'])) {
        message(0, '原密码错误', null);
    } else {
        $update_user = array(
            'uid'      => $_W['uid'],
            'password' => $newPass,
        );
        $update = user_update($update_user);
        if (is_error($update)) {
            message(0, $update['message'], null);

        } else {
            message(1, '更改密码成功', $update);
        }
    }
}

if ($do == 'updateUserInfo') {

    $avatar   = $_GPC['avatar'];
    $realname = $_GPC['realname'];
    $mobile   = $_GPC['mobile'];

    if ($_W['setting']['remote']['type'] != '0') {
        $imgUrl = $_W['setting']['copyright']['ossUrl'];
    } else {
        $imgUrl = ATTACHMENT_ROOT;
    }
    $data['avatar']   = str_ireplace($imgUrl, '', $avatar);
    $data['realname'] = $realname;
    $data['mobile']   = $mobile;

    $updateUserInfo = array(
        'uid'      => $_W['uid'],
        'mobile'   => $mobile,
        'realname' => $realname,
        'avatar'   => $avatar,
    );
    $update = user_update($updateUserInfo);
    if (is_error($update)) {
        message(0, $update['message'], null);

    } else {
        message(1, '修改个人信息成功', $update);
    }

}

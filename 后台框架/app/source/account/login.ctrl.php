<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'login' : $do;

if ($do == 'login') {
    $mobile   = $_GPC['mobile'];
    $password = $_GPC['password'];

    $member = mc_login($mobile, $password);

    if (is_error($member)) {
        $return = array(
            'data'    => null,
            'message' => $member['message'],
            'status'  => 0,
        );
        exit(json_encode($return));
    }

    //头像地址补全
    $member['avatar']  = tomedia($member['avatar']);
    $member['viptime'] = date("Y-m-d H:i", $member['viptime']);

    isetcookie(COOKIE_UID, $member['uid'], 30 * 86400);
    isetcookie(COOKIE_APP_OPENID, $member['app_openid'], 30 * 86400);
    isetcookie(COOKIE_UNIONID, $member['unionid'], 30 * 86400);

    $data = array(
        'userInfo' => $member,
        'token'    => app_create_accesstoken($member['uid']),
    );

    $json_arr = array(
        "status"  => 1,
        "message" => '登录成功',
        "data"    => $data,
    );
    exit(json_encode($json_arr));
}

if ($do == 'checkLogin') {
    $mobile = $_GPC['mobile'];
    $member = mc_fetch_by_mobile($mobile);
    if (empty($member)) {
        $return = array(
            'data'    => null,
            'message' => '当前手机号尚未注册',
            'status'  => 0,
        );
        exit(json_encode($return));
    } else {
        $return = array(
            'data'    => null,
            'message' => '当前手机号已注册,请输入密码',
            'status'  => 1,
        );
        exit(json_encode($return));
    }
}

if ($do == 'register') {

    $mobile   = $_GPC['mobile'];
    $password = $_GPC['password'];

    //验证码
    $code   = $_GPC['code'];
    $result = verifycode_check($mobile, $code);
    if (is_error($result)) {
        $return = array(
            'data'    => null,
            'message' => $result['message'],
            'status'  => 0,
        );
        exit(json_encode($return));
    }

    $member = mc_register($mobile, $password);

    if (is_error($member)) {
        $return = array(
            'data'    => null,
            'message' => $member['message'],
            'status'  => 0,
        );
        exit(json_encode($return));
    } else {
        isetcookie(COOKIE_UID, $member['uid'], 30 * 86400);

        $member['avatar']  = tomedia($member['avatar']);
        $member['viptime'] = date("Y-m-d H:i", $member['viptime']);

        $json_arr = array(
            "status"  => 1,
            "message" => '注册成功',
            "data"    => array(
                'userInfo' => $member,
                'token'    => app_create_accesstoken($member['uid']),
            ),
        );
        exit(json_encode($json_arr));
    }
}

if ($do == 'sendCode') {
    $mobile = $_GPC['mobile'];
    $result = verifycode_send($mobile, '2');
    if (is_error($result)) {
        $return = array(
            'data'    => null,
            'message' => $result['message'],
            'status'  => 0,
        );
        exit(json_encode($return));
    } else {
        $return = array(
            'data'    => null,
            'message' => '验证码已成功发送到您的手机!',
            'status'  => 1,
        );
        exit(json_encode($return));
    }
}

if ($do == 'checkCode') {
    $mobile = $_GPC['mobile'];
    $code   = $_GPC['code'];
    $result = verifycode_check($mobile, $code);
    if (is_error($result)) {
        $return = array(
            'data'    => null,
            'message' => $result['message'],
            'status'  => 0,
        );
        exit(json_encode($return));
    } else {
        $return = array(
            'data'    => null,
            'message' => '验证码正确,请进行下一步',
            'status'  => 1,
        );
        exit(json_encode($return));
    }
}

if ($do == 'updatePassword') {
    $mobile   = $_GPC['mobile'];
    $password = $_GPC['password'];
    $result   = mc_change_password($mobile, $password);
    if (is_error($result)) {
        $return = array(
            'data'    => null,
            'message' => $result['message'],
            'status'  => 0,
        );
        exit(json_encode($return));
    } else {
        $return = array(
            'data'    => $result,
            'message' => '修改密码成功',
            'status'  => 1,
        );
        exit(json_encode($return));
    }
}

if ($do == 'wechatLogin') {
    $code = $_GPC['code'];
    load()->classs('wechat.account');
    $wechat                    = new WechatAccount();
    $member                    = $wechat->wechat_app_oauth($code);
    $wechat_login_success_json = array(
        "status"  => 1,
        "message" => '登录成功',
        "data"    => array(
            'userInfo' => $member,
            'token'    => app_create_accesstoken($member['uid']),
        ),
    );
    exit(json_encode($wechat_login_success_json));

}

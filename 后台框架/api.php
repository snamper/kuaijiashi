<?php

header('Access-Control-Allow-Origin:*');

require './framework/bootstrap.inc.php';

load()->classs('agent');
define('IS_WECHAT', Agent::isMicroMessage() == Agent::MICRO_MESSAGE_YES);

if (IS_WECHAT) {
    $_W['session_id'] = '';
    if ($_GPC['state'] && strexists($_GPC['state'], 'holdskill-')) {
        $_W['session_id'] = substr($_GPC['state'], 8);
    }
    if (empty($_W['session_id'])) {
        $_W['session_id'] = $_COOKIE[session_name()];
    }
    if (empty($_W['session_id'])) {
        $_W['session_id'] = md5(random(20));
        isetcookie(session_name(), $_W['session_id']);
    }
    session_id($_W['session_id']);
    load()->classs('wechat.account');
    $wechat = new WechatAccount();
    $code   = $_GET['code'];
    if ((($_GET['oauth'] == 'wechat') && empty($_W['ispost']) && empty($_W['isajax'])) || (!empty($_GET['deviceno']) && !empty($_GET['androidno']))) {
        $wechat_client_oauth_callback = $_GET['callback'];
        $deviceno                     = $_GET['deviceno'];
        $androidno                    = $_GET['androidno'];
        $wechat_client_oauth_callback = empty($wechat_client_oauth_callback) ? $_W['siteroot'] . '/wechat/' : $wechat_client_oauth_callback;
        $wechat->wechat_client_oauth('snsapi_userinfo', $code, $wechat_client_oauth_callback);
        exit();
    }
}

require './' . $entry . '/common/bootstrap.' . $entry . '.inc.php';

require './' . $entry . '/common/accesstoken.inc.php';

if ($entry == 'app') {
    // 读取用户信息
    if (!empty($_W['accesstoken']['uid'])) {
        $_W['uid']    = $_W['accesstoken']['uid'];
        $_W['member'] = mc_member($_W['accesstoken']['uid']);
    }
} elseif ($entry == 'sys') {
    // 读取管理员信息
    if (!empty($_W['sys_accesstoken']['uid'])) {
        $_W['uid']  = $_W['sys_accesstoken']['uid'];
        $_W['user'] = user($_W['sys_accesstoken']['uid']);
    }
}

$file = IA_ROOT . '/' . $entry . '/source/' . $controller . '/' . $action . '.ctrl.php';

if (!file_exists($file)) {
    message(0, '对不起，该页面控制器不存在', null);
}

require $file;

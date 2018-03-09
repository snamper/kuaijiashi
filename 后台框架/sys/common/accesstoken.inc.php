<?php

defined('IN_IA') or exit('Access Denied');

$_W['sys_accesstoken']                    = array();
$_W['sys_accesstoken']['sys_accesstoken'] = '';
$_W['sys_accesstoken']['expires']         = 0;
$_W['sys_accesstoken']['ip']              = '';
$_W['sys_accesstoken']['uid']         = 0;
$_W['sys_accesstoken']['lastvisit']       = 0;
$_W['sys_accesstoken_key_expire']         = 2592000;
/** ------------------------------------------------------------------- */

/**
 * Create access token
 * @return string client access token
 */
function create_sys_accesstoken($uid)
{
    global $_W;
    $old_sys_accesstoken = pdo_fetch_one('sys_accesstokens', array('uid' => $uid));
    if (!empty($old_sys_accesstoken) && ($old_sys_accesstoken['expires'] > TIMESTAMP)) {

        $sys_accesstoken        = $old_sys_accesstoken['sys_accesstoken'];
        $expires                = (float) $old_sys_accesstoken['expires'];
        $sign                   = create_sign(array($_W['config']['setting']['authkey'], $sys_accesstoken, $expires, 'sys_accesstoken'));
        $client_sys_accesstoken = $sys_accesstoken . ',' . $expires . ',' . $sign;
        return $client_sys_accesstoken;
    }
    $sys_accesstoken        = uniqid_random();
    $expires                = (float) (TIMESTAMP + $_W['sys_accesstoken_key_expire']);
    $sign                   = create_sign(array($_W['config']['setting']['authkey'], $sys_accesstoken, $expires, 'sys_accesstoken'));
    $client_sys_accesstoken = $sys_accesstoken . ',' . $expires . ',' . $sign;

    $ip = $_SERVER['REMOTE_ADDR'];

    $data = array(
        'sys_accesstoken' => $sys_accesstoken,
        'expires'         => $expires,
        'ip'              => $ip,
        'uid'         => $uid,
        'lastvisit'       => TIMESTAMP,
    );
    if (empty($old_sys_accesstoken)) {
        pdo_insert('sys_accesstokens', $data);
    } else {
        pdo_update('sys_accesstokens', $data, array('uid' => $uid));
    }

    return $client_sys_accesstoken;
}

/** ------------------------------------------------------------------- */

/**
 * 接收 token ,然后验证 sys_accesstoken
 */

$token = trim($_GPC['token']);

$token_arr = array();
if (preg_match('/^[a-zA-Z0-9]{19}\,[0-9]{10}\,[a-zA-Z0-9]{32}$/', $token) === 1) {
    $token_arr    = explode(',', $token);
    $token_arr[1] = (float) $token_arr[1]; //[*]
}

$token_key_verification = false;
if (!empty($token_arr)) {
    $sign = create_sign(array($_W['config']['setting']['authkey'], $token_arr[0], $token_arr[1], 'sys_accesstoken'));

    if (($sign === $token_arr[2]) && (($token_arr[1] - TIMESTAMP) < $_W['sys_accesstoken_key_expire'])) {
        $token_key_verification = true;
    }
}

/**
 * 如果 token 验证通过，就读取 sys_accesstoken 信息
 */

$db_token = array();
if ($token_key_verification === true) {
    // 读取 sys_accesstoken

    $db_token = pdo_fetch("SELECT * FROM " . tablename('sys_accesstokens') . " WHERE `sys_accesstoken`=:sys_accesstoken", array(':sys_accesstoken' => $token_arr[0]));

    if (!empty($db_token) && ($db_token['expires'] > TIMESTAMP)) {
        // 更新最近一次访问时间
        pdo_update('sys_accesstokens', array('lastvisit' => TIMESTAMP), array('sys_accesstoken' => $token_arr[0]));
        $_W['sys_accesstoken'] = $db_token;
    } else {
        // 删除过期的 sys_accesstoken
        pdo_query("DELETE FROM " . tablename('sys_accesstokens') . " WHERE `expires`<'" . TIMESTAMP . "'");
    }
}

/**
 * 删除用完的变量
 */

unset($token, $token_arr, $token_key_verification, $db_token);

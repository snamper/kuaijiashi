<?php

defined('IN_IA') or exit('Access Denied');

$_W['accesstoken']                = array();
$_W['accesstoken']['accesstoken'] = '';
$_W['accesstoken']['expires']     = 0;
$_W['accesstoken']['ip']          = '';
$_W['accesstoken']['uid']         = 0;
$_W['accesstoken']['lastvisit']   = 0;
$_W['accesstoken_key_expire']     = 2592000;
/** ------------------------------------------------------------------- */

/**
 * Create access token
 * @return string client access token
 */
function app_create_accesstoken($uid)
{
    global $_W;
    $old_accesstoken = pdo_fetch_one('accesstokens', array('uid' => $uid));
    if (!empty($old_accesstoken) && ($old_accesstoken['expires'] > TIMESTAMP)) {

        $accesstoken        = $old_accesstoken['accesstoken'];
        $expires            = (float) $old_accesstoken['expires'];
        $sign               = create_sign(array($_W['config']['setting']['authkey'], $accesstoken, $expires, 'accesstoken'));
        $client_accesstoken = $accesstoken . ',' . $expires . ',' . $sign;
        return $client_accesstoken;
    }
    $accesstoken        = uniqid_random();
    $expires            = (float) (TIMESTAMP + $_W['accesstoken_key_expire']);
    $sign               = create_sign(array($_W['config']['setting']['authkey'], $accesstoken, $expires, 'accesstoken'));
    $client_accesstoken = $accesstoken . ',' . $expires . ',' . $sign;

    $ip = $_SERVER['REMOTE_ADDR'];

    $data = array(
        'accesstoken' => $accesstoken,
        'expires'     => $expires,
        'ip'          => $ip,
        'uid'         => $uid,
        'lastvisit'   => TIMESTAMP,
    );
    if (empty($old_accesstoken)) {
        pdo_insert('accesstokens', $data);
    } else {
        pdo_update('accesstokens', $data, array('uid' => $uid));
    }

    return $client_accesstoken;
}

/** ------------------------------------------------------------------- */

/**
 * 接收 appkey ,然后验证 accesstoken
 */

$appkey = trim($_GPC['appkey']);

$appkey_arr = array();
if (preg_match('/^[a-zA-Z0-9]{19}\,[0-9]{10}\,[a-zA-Z0-9]{32}$/', $appkey) === 1) {
    $appkey_arr    = explode(',', $appkey);
    $appkey_arr[1] = (float) $appkey_arr[1]; //[*]
}

$appkey_key_verification = false;
if (!empty($appkey_arr)) {
    $sign = create_sign(array($_W['config']['setting']['authkey'], $appkey_arr[0], $appkey_arr[1], 'accesstoken'));

    if (($sign === $appkey_arr[2]) && (($appkey_arr[1] - TIMESTAMP) < $_W['accesstoken_key_expire'])) {
        $appkey_key_verification = true;
    }
}

/**
 * 如果 appkey 验证通过，就读取 accesstoken 信息
 */

$db_appkey = array();
if ($appkey_key_verification === true) {
    // 读取 accesstoken

    $db_appkey = pdo_fetch("SELECT * FROM " . tablename('accesstokens') . " WHERE `accesstoken`=:accesstoken", array(':accesstoken' => $appkey_arr[0]));

    if (!empty($db_appkey) && ($db_appkey['expires'] > TIMESTAMP)) {
        // 更新最近一次访问时间
        pdo_update('accesstokens', array('lastvisit' => TIMESTAMP), array('accesstoken' => $appkey_arr[0]));
        $_W['accesstoken'] = $db_appkey;
    } else {
        // 删除过期的 accesstoken
        pdo_query("DELETE FROM " . tablename('accesstokens') . " WHERE `expires`<'" . TIMESTAMP . "'");
    }
}

/**
 * 删除用完的变量
 */

unset($appkey, $appkey_arr, $appkey_key_verification, $db_appkey);

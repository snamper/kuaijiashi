<?php

defined('IN_IA') or exit('Access Denied');
$do = empty($do) ? 'display' : $do;

if ($do == 'display') {
    message(1, '获取微信登录设置成功', $_W['setting']);
}

if ($do == 'post') {

    $wechat_client = $_GPC['wechat_client'];
    foreach ($wechat_client as &$item1) {
        $item1 = trim($item1);
    }

    setting_save($wechat_client, 'wechat_client');

    $wechat_web = $_GPC['wechat_web'];
    foreach ($wechat_web as $item2) {
        $item2 = trim($item2);
    }

    setting_save($wechat_web, 'wechat_web');

    $wechat_app = $_GPC['wechat_app'];
    foreach ($wechat_app as $item3) {
        $item3 = trim($item3);
    }

    setting_save($wechat_app, 'wechat_app');

    $wechat_mina = $_GPC['wechat_mina'];
    foreach ($wechat_mina as $item) {
        $item4 = trim($item4);
    }

    setting_save($wechat_mina, 'wechat_mina');

    cache_write('access_token', array());

    message(1, '更新设置成功！', null);
}

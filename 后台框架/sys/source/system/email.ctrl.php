<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;
if ($do == 'display') {
    $setting = $_W['setting'];
    $email   = $setting['email'];
    message(1, '获取邮件设置参数成功', $eamil);
}
if ($do == 'save') {
    $notify = array(
        'username'  => $_GPC['username'],
        'password'  => $_GPC['password'],
        'smtp'      => $_GPC['smtp'],
        'sender'    => $_GPC['sender'],
        'signature' => $_GPC['signature'],
    );

    setting_save($notify, 'email');

    load()->func('communication');
    if (!empty($_GPC['receiver'])) {
        $result = ihttp_email($_GPC['receiver'], $_W['setting']['copyright']['sitename'] . '验证邮件' . date('Y-m-d H:i:s'), '如果您收到这封邮件则表示您系统的发送邮件配置成功！');
        if (is_error($result)) {
            message(0, $result['message'], null);
        }
    } else {
        $result = ihttp_email($notify['username'], $_W['setting']['copyright']['sitename'] . '验证邮件' . date('Y-m-d H:i:s'), '如果您收到这封邮件则表示您系统的发送邮件配置成功！');
        if (is_error($result)) {
            message(0, $result['message'], null);
        }
    }

    message(1, '更新设置成功！', null);
}

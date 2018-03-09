<?php

defined('IN_IA') or exit('Access Denied');
$do      = empty($do) ? 'display' : $do;
$setting = $_W['setting'];
$sms     = iunserializer($setting['sms']);
if ($do == 'display') {
    message(1, '获取短信设置参数成功', $sms);
}
if ($do == 'post') {

    $AccessKeyId     = trim($_GPC['AccessKeyId']);
    $AccessKeySecret = trim($_GPC['AccessKeySecret']);
    $signature       = trim($_GPC['signature']);

    if (is_null($AccessKeyId) && is_null($AccessKeySecret)) {
        message('短信参数设置修改失败！请填写AccessKeyId和AccessKeySecret', referer(), 'error');
    }
    $sms                    = array();
    $sms['AccessKeyId']     = $AccessKeyId;
    $sms['AccessKeySecret'] = $AccessKeySecret;
    $sms['signature']       = $signature;

    setting_save($sms, 'sms');
    message(1, '短信参数设置修改成功！', null);

}

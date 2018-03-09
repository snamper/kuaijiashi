<?php

defined('IN_IA') or exit('Access Denied');

if ($do == 'uploadMedia') {
    $media_id = $_GPC['media_id'];
    load()->classs('wechat.account');
    $wechat = new WechatAccount();
    $result = $wechat->downloadMedia($media_id);
    if (!is_error($result)) {
        message(1, '上传成功', tomedia($result));
    } else {
        message(0, '上传失败', null);
    }
}

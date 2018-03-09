<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;
if ($do == 'display') {
    $id                 = $_GPC['id'];
    $detail             = pdo_fetch_one('drive_article', array('id' => $id));
    $detail['thumb']    = tomedia($detail['thumb']);
    $detail['content'] = ihtml_entity_decode($detail['content']);

    message(1, '获取文章信息成功', $detail);
}

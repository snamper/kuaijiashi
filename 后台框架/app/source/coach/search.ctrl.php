<?php

defined('IN_IA') or exit('Access Denied');

$member = $_W['member'];
$do     = !empty($do) ? $do : 'display';

if ($do == 'display') {

    $keywords = pdo_fetchall("SELECT * FROM " . tablename('drive_keyword') . " WHERE `type`='1' ORDER BY searchnum DESC LIMIT 5");

    message(1, '获取热门关键词成功', $keywords);
}

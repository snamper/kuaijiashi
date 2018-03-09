<?php

defined('IN_IA') or exit('Access Denied');
$do = empty($do) ? 'display' : $do;

if ($do == 'display') {

    $credits            = array();
    $credits['credit1'] = array('enabled' => true, 'title' => '');
    $credits['credit2'] = array('enabled' => true, 'title' => '');
    $credits['credit3'] = array('enabled' => true, 'title' => '');
    $credits['credit4'] = array('enabled' => true, 'title' => '');
    $credits['credit5'] = array('enabled' => true, 'title' => '');

    $list = $_W['setting'];
    if (!empty($list['creditnames'])) {
        $list = iunserializer($list['creditnames']);
        if (is_array($list)) {
            foreach ($list as $k => $v) {
                $credits[$k] = $v;
            }
        }
    }
    message(1, '获取积分列表成功', $credits);
}

if ($do == 'save') {
    $creditNames = array();
    $credits     = array('credit1', 'credit2', 'credit3', 'credit4', 'credit5');

    foreach ($credits as $row) {
        if ($row == 'credit1' || $row == 'credit2') {
            $enabled_tmp = '1';
        } else {
            $enabled_tmp = $_GPC['enabled'][$row];
        }
        $creditNames[$row] = array('title' => $_GPC['title'][$row], 'enabled' => $enabled_tmp);
    }

    setting_save($creditNames, 'creditnames');
    message(1, '积分列表更新成功！', null);
}

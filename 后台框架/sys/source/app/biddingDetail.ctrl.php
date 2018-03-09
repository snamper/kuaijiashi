<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;

load()->model('mc');

if ($do == 'display') {

    $id = $_GPC['id'];

    $detail = pdo_fetch_one('drive_bidding', array('id' => $id));

    $detail['coaches'] = iunserializer($detail['coaches']);

    $lists = pdo_fetchall("SELECT * FROM " . tablename('drive_coach') . " WHERE `status`='1' ORDER BY `reviewtime` DESC");

    $coach = pdo_fetch_one('drive_coach', array('id' => $detail['cid']));

    $tuijian_coach = pdo_fetch_one('drive_coach', array('id' => $detail['tuijian_cid']));

    $return = array(
        'detail'           => $detail,
        'coachList'        => $lists,
        'realname'         => $coach['realname'],
        'tuijian_realname' => $tuijian_coach['realname'],
    );

    message(1, '获取需求详情成功', $return);

}

if ($do == 'post') {
    $id          = $_GPC['id'];
    $description = $_GPC['description'];
    $coaches     = $_GPC['coaches'];
    $status      = $_GPC['status'];

    $update = array(
        'description' => $description,
        'coaches'     => iserializer($coaches),
        'status'      => $status,
    );
    if (pdo_update('drive_bidding', $update, array('id' => $id))) {
        message(1, '更新成功', null);
    } else {
        message(0, '更新失败', null);
    }

}

if ($do == 'review') {
    $id     = $_GPC['id'];
    $update = array(
        'status'     => 1,
        'reviewtime' => TIMESTAMP,
    );
    if (pdo_update('drive_coach_order', $update, array('id' => $id))) {
        message(1, '审核订单成功', null);

    } else {
        message(0, '审核订单失败，请重试', null);
    }

}
if ($do == 'delete') {
    $id = $_GPC['id'];
    if (pdo_delete('drive_coach_order', array('id' => $id))) {
        message(1, '删除订单成功', null);

    } else {
        message(0, '删除订单失败，请重试', null);
    }

}

<?php

defined('IN_IA') or exit('Access Denied');

$do = !empty($do) ? $do : 'display';

if ($do == 'display') {

    $id = $_GPC['id'];

    $order = pdo_fetch_one('drive_coach_order', array('id' => $id));

    $order['createtime']  = date("Y-m-d H:i:s", (int) $order['createtime']);
    $order['appointtime'] = date("Y-m-d H:i", (int) $order['appointtime']);
    $order['paytime']     = date("Y-m-d H:i:s", (int) $order['paytime']);
    $order['accepttime']  = date("Y-m-d H:i:s", (int) $order['accepttime']);
    $order['finishtime']  = date("Y-m-d H:i:s", (int) $order['finishtime']);

    $coach                = pdo_fetch_one('drive_coach', array('id' => $order['cid']));
    $coach['avatar']      = tomedia($coach['avatar']);
    $coach['description'] = ihtml_entity_decode($coach['description']);

    $followArray = pdo_fetch("SELECT * FROM " . tablename('drive_follow') . " WHERE `uid`=:uid AND `touid` = :touid", array(':uid' => $_W['uid'], ':touid' => $coach['uid']));
    if (!empty($followArray)) {
        $coach['followed'] = 1;
    } else {
        $coach['followed'] = 0;
    }

    $buyer = mc_member($order['uid']);

    $evaluation = pdo_fetch_one('drive_evaluation', array('pid' => $id));

    $role = $_W['member']['role'];

    if ($role == 1) {
        //普通粉丝
        $order['avatar'] = tomedia($buyer['avatar']);
        $order['name']   = '购买方：' . $buyer['nickname'];
        $order['mobile'] = $buyer['mobile'];
    } else {
        //教练
        $order['avatar'] = tomedia($coach['avatar']);
        $order['name']   = '服务方：' . $coach['realname'];
        $order['mobile'] = $coach['mobile'];
    }

    $return = array(
        'detail'     => $order,
        'coach'      => $coach,
        'buyer'      => $buyer,
        'evaluation' => $evaluation,
    );
    message(1, '获取订单详情成功', $return);
}
if ($do == 'post') {

    $role = $_W['member']['role'];

    $id           = $_GPC['id'];
    $qualityLevel = $_GPC['qualityLevel'];
    $serviceLevel = $_GPC['serviceLevel'];
    $replyLevel   = $_GPC['replyLevel'];
    $content      = $_GPC['content'];
    $tags         = str_ireplace('&quot;', '"', $_GPC['tags']);
    $tags         = json_decode($tags, true);

    $order = pdo_fetch_one('drive_coach_order', array('id' => $id));

    $have_evaluation = pdo_fetch_one('drive_evaluation', array('pid' => $id));

    $evaluation = array(
        'pid'          => $id,
        'uid'          => $_W['uid'],
        'qualityLevel' => $qualityLevel,
        'serviceLevel' => $serviceLevel,
        'replyLevel'   => $replyLevel,
        'content'      => $content,
        'tags'         => iserializer($tags),
        'createtime'   => TIMESTAMP,
    );
    $seller_evaluation = array(
        'pid'               => $id,
        'seller_uid'        => $_W['uid'],
        'seller_content'    => $content,
        'seller_createtime' => TIMESTAMP,
    );
    if (empty($have_evaluation)) {
        if ($role == 1) {
            if (pdo_insert('drive_evaluation', $evaluation)) {
                pdo_update('drive_coach_order', array('status' => 4), array('id' => $id));
                message(1, '评价成功，谢谢支持', null);
            } else {
                message(0, '评价失败，请重试', null);
            }
        } elseif ($order['seller_uid'] == $_W['uid']) {
            if (pdo_insert('drive_evaluation', $seller_evaluation)) {
                message(1, '评价成功，谢谢支持', null);
            } else {
                message(0, '评价失败，请重试', null);
            }
        }
    } else {
        if ($role == 1) {
            if (pdo_update('drive_evaluation', $evaluation, array('pid' => $id))) {
                pdo_update('drive_coach_order', array('status' => 4), array('id' => $id));
                message(1, '评价成功，谢谢支持', null);
            } else {
                message(0, '评价失败，请重试', null);
            }
        } elseif ($order['seller_uid'] == $_W['uid']) {
            if (pdo_update('drive_evaluation', $seller_evaluation, array('pid' => $id))) {
                message(1, '评价成功，谢谢支持', null);
            } else {
                message(0, '评价失败，请重试', null);
            }
        }
    }
}

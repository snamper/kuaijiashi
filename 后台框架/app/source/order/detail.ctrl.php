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

    $coach       = pdo_fetch_one('drive_coach', array('id' => $order['cid']));
    $followArray = pdo_fetch("SELECT * FROM " . tablename('drive_follow') . " WHERE `uid`=:uid AND `touid` = :touid", array(':uid' => $_W['uid'], ':touid' => $coach['uid']));
    if (!empty($followArray)) {
        $coach['followed'] = 1;
    } else {
        $coach['followed'] = 0;
    }

    $buyer = mc_member($order['uid']);

    $role = $_W['member']['role'];

    if ($role == 1) {
        //教练
        $order['avatar'] = tomedia($coach['avatar']);
        $order['name']   = '服务方：' . $coach['realname'];
        $order['mobile'] = $coach['mobile'];

    } else {
        //普通粉丝
        $order['avatar'] = tomedia($buyer['avatar']);
        $order['name']   = '购买方：' . $buyer['nickname'];
        $order['mobile'] = $buyer['mobile'];
    }

    $return = array(
        'detail' => $order,
        'coach'  => $coach,
        'buyer'  => $buyer,
    );
    message(1, '获取订单详情成功', $return);

}

if ($do == 'getCoupon') {
    $coupon_id = $_GPC['coupon_id'];
    if (!empty($coupon_id)) {

        $detail = pdo_fetch("SELECT * FROM " . tablename('coupon') . " WHERE `uid`=:uid AND `id`=:id", array(':id' => $coupon_id, ':uid' => $_W['uid']));
        if ($detail['is_at_least'] == '2') {
            $detail['reason'] = '最低消费' . $detail['at_least'] . '元';
        } else {
            $detail['reason'] = '无限制';
        }
        $return = array(

            'data'    => $detail,

            'message' => '',

            'status'  => 1,

        );
        exit(json_encode($return));
    } else {
        $return = array(

            'data'    => null,

            'message' => '未选择优惠券',

            'status'  => 0,

        );
        exit(json_encode($return));
    }
}
if ($do == 'finish') {
    $id    = $_GPC['id'];
    $order = pdo_fetch_one('drive_coach_order', array('id' => $id));
    if ($order['uid'] == $_W['uid']) {
        $update = array(
            'status'     => 3,
            'finishtime' => TIMESTAMP,
        );
        if (pdo_update('drive_coach_order', $update, array('id' => $id))) {
            mc_credit_increase($order['seller_uid'], 'credit1', $order['total'], 0, '订单【' . $order['id'] . '】收入');
            message(1, '结束该订单成功', null);
        } else {
            message(0, '结束该订单失败', null);
        }
    } else {
        message(0, '您无权结束该订单', null);
    }

}

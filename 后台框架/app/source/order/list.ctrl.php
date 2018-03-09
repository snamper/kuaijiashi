<?php

defined('IN_IA') or exit('Access Denied');

$pindex = max(1, intval($_GPC['page']));

$psize = 10;

$condition = '';

$params = array();

if (!empty($_GPC['status'])) {

    $condition .= " AND `status` = :status";

    $params[':status'] = $_GPC['status'];

}
if ($_W['member']['role'] == 1) {
    $condition .= " AND `uid` = :uid";
} else {
    $condition .= " AND `seller_uid` = :uid";
}

$params[':uid'] = $_W['uid'];
$lists          = pdo_fetchall("SELECT * FROM " . tablename('drive_coach_order') . " WHERE 1 $condition ORDER BY `createtime` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);

if (!empty($lists)) {
    foreach ($lists as &$row) {

        $old_coupon_activity = pdo_fetch_one('order_activity', array('order_id' => $row['id'], 'activity' => 'coupon'));
        if (empty($old_coupon_activity)) {
            $row['coupon_id'] = 0;
        } else {
            $row['coupon_id'] = $old_coupon_activity['activity_id'];
        }

        $row['createtime'] = date("Y-m-d H:i:s", (int) $row['createtime']);
        $row['finishtime'] = date("Y-m-d H:i:s", (int) $row['finishtime']);

        if ($_W['member']['role'] == 1) {
            $userInfo      = pdo_fetch_one('drive_coach', array('id' => $row['cid']));
            $row['avatar'] = tomedia($userInfo['avatar']);
            $row['name']   = $userInfo['realname'];

        } else {
            $userInfo      = mc_member($row['uid']);
            $row['avatar'] = tomedia($userInfo['avatar']);
            $row['name']   = $userInfo['nickname'];
        }

        switch ($row['status']) {
            case '1':
                $row['statusText'] = '待付款';
                break;
            case '2':
                $row['statusText'] = '进行中'; //追加付款、取消订单
                break;
            case '3':
                $row['statusText'] = '已完成';
                break;
            case '4':
                $row['statusText'] = '已评价';
                break;
            default:
                $row['statusText'] = '';
                break;
        }

    }
    message(1, '获取订单数据成功', $lists);
} else {

    message(0, '没有更多数据了', null);
}

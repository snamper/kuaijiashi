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

$condition .= " AND `uid` = :uid";

$params[':uid'] = $_W['uid'];
$lists          = pdo_fetchall("SELECT * FROM " . tablename('drive_bidding') . " WHERE 1 $condition ORDER BY `createtime` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);

if (!empty($lists)) {
    foreach ($lists as &$row) {

        $row['createtime'] = date("Y-m-d H:i:s", (int) $row['createtime']);
        $row['selecttime'] = date("Y-m-d H:i:s", (int) $row['selecttime']);

        $coach           = pdo_fetch_one('drive_coach', array('id' => $row['cid']));
        $row['realname'] = $coach['realname'];
        $row['avatar']   = tomedia($coach['avatar']);

        $order            = pdo_fetch_one('drive_coach_order', array('id' => $row['oid']));
        $row['coupon_id'] = $order['coupon_id'];

        switch ($row['status']) {
            case '1':
                $row['statusText'] = '未付款';
                break;
            case '2':
                $row['statusText'] = '后台匹配中';
                break;
            case '3':
                $row['statusText'] = '待选教练';
                break;
            case '4':
                $row['statusText'] = '已选教练';
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

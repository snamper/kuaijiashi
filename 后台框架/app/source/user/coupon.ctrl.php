<?php

defined('IN_IA') or exit('Access Denied');

if ($do == 'list') {

    $pindex = max(1, intval($_GPC['page']));

    $psize = 10;
    if ($_GPC['all'] == '1') {
        $lists = pdo_fetchall("SELECT * FROM " . tablename('coupon_template') . " WHERE `enable` = :enable AND `end_time` > :this_time AND `quantity_issue` < `total` ORDER BY `id` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':enable' => ON, ':this_time' => TIMESTAMP));
        foreach ($lists as &$row) {
            if ($row['is_at_least'] == '2') {
                $row['reason'] = '剩余' . ($row['total'] - $row['quantity_issue']) . '张/最低消费' . $row['at_least'] . '元';
            } else {
                $row['reason'] = '剩余' . ($row['total'] - $row['quantity_issue']) . '张/无限制';
            }
            $row['start_time'] = date('Y-m-d H:i:s', $row['start_time']);
            $row['end_time']   = date('Y-m-d H:i:s', $row['end_time']);
        }
    } else {
        $lists = pdo_fetchall("SELECT * FROM " . tablename('coupon') . " WHERE `uid`=:uid AND `status` = '1' ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':uid' => $_W['uid']));
        foreach ($lists as &$row) {
            if ($row['is_at_least'] == '2') {
                $row['reason'] = '最低消费' . $row['at_least'] . '元';
            } else {
                $row['reason'] = '无限制';
            }
            $row['start_time'] = date('Y-m-d H:i:s', $row['start_time']);
            $row['end_time']   = date('Y-m-d H:i:s', $row['end_time']);
        }
    }

    if (!empty($lists)) {
        message(1, '获取数据成功', $lists);
    } else {
        message(0, '没有更多数据了', null);
    }

    exit(json_encode($return));
}
if ($do == 'get') {
    load()->model('coupon');
    $row_id = $_GPC['id'];
    $coupon = coupon_grant($_W['uid'], $row_id);
    if (is_error($coupon)) {
        message(0, $coupon['message'], null);
    } else {
        message(1, '领取优惠券成功', $coupon);
    }
}

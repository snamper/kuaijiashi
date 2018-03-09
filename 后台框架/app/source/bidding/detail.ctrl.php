<?php

defined('IN_IA') or exit('Access Denied');

$do = !empty($do) ? $do : 'display';

if ($do == 'display') {

    $id = $_GPC['id'];

    $bidding = pdo_fetch_one('drive_bidding', array('id' => $id));

    $bidding['createtime'] = date("Y-m-d H:i:s", (int) $bidding['createtime']);
    $bidding['selecttime'] = date("Y-m-d H:i:s", (int) $bidding['selecttime']);

    $coach               = pdo_fetch_one('drive_coach', array('id' => $bidding['cid']));
    $bidding['realname'] = $coach['realname'];
    $bidding['avatar']   = tomedia($coach['avatar']);
    $bidding['price']    = $coach['price'];
    $bidding['mobile']   = $coach['mobile'];

    $return = array(
        'detail' => $bidding,
    );
    message(1, '获取需求详情成功', $return);

}

if ($do == 'getList') {

    $id = $_GPC['id'];

    $pindex = max(1, intval($_GPC['page']));

    $psize = 10;

    $bidding = pdo_fetch_one('drive_bidding', array('id' => $id));

    $coach_ids = iunserializer($bidding['coaches']);

    if (empty($coach_ids)) {
        $coach_ids = [0];
    }

    $ids = implode(',', $coach_ids);

    $lists = pdo_fetchall("SELECT * FROM " . tablename('drive_coach') . " WHERE `id` IN (" . $ids . ") ORDER BY `createtime` DESC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    message(1, '获取教练列表成功', $lists);

}

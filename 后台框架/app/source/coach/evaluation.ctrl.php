<?php

defined('IN_IA') or exit('Access Denied');

$member = $_W['member'];

$id = $_GPC['id'];

$pindex = max(1, intval($_GPC['page']));

$psize = 10;

$orders = pdo_fetchall("SELECT * FROM " . tablename('drive_coach_order') . " WHERE `cid` = :cid AND `status` >= '3' ORDER BY `createtime` DESC", array(':cid' => $id));
$ids    = [0];
foreach ($orders as $row) {
    $ids[] = $row['id'];
}
$ids = implode(',', $ids);

$lists = pdo_fetchall("SELECT * FROM " . tablename('drive_evaluation') . " WHERE `pid` IN (" . $ids . ") ORDER BY `createtime` DESC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
if (!empty($lists)) {
    foreach ($lists as &$row) {
        $row['tags']       = iunserializer($row['tags']);
        $row['createtime'] = date("Y-m-d H:i:s", $row['createtime']);
        $userInfo          = mc_member($row['uid']);
        $row['nickname']   = $userInfo['nickname'];
    }

}
message(1, '获取评价列表成功', $lists);

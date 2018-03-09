<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;
if ($do == 'display') {

    $pindex = max(1, intval($_GPC['page']));

    $psize = 10;

    $lists = pdo_fetchall("SELECT A.*, B.id AS cid, B.avatar, B.realname, B.description FROM " . tablename('drive_follow') . " AS A LEFT JOIN " . tablename('drive_coach') . " AS B ON A.touid=B.uid WHERE A.uid=:uid ORDER BY A.createtime DESC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':uid' => $_W['uid']));

    foreach ($lists as &$row) {
        $row['avatar']      = tomedia($row['avatar']);
        $row['createtime']  = date("Y-m-d H:i", $row['createtime']);
        $row['pid']         = $row['cid'];
        $row['description'] = ihtml_entity_decode($row['description']);
    }
    message(1, '获取关注列表成功', $lists);

}

if ($do == 'delete') {
    $id = $_GPC['id'];
    if (pdo_delete('drive_follow', array('id' => $id))) {
        message(1, '删除成功', null);
    } else {
        message(0, '删除失败，请重试', null);
    }
}

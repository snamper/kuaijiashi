<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;

load()->model('mc');

if ($do == 'display') {

}
if ($do == 'getList') {

    $condition = ' WHERE 1';
    $params    = array();
    if (!empty($_GPC['keyword'])) {
        $condition .= ' AND (`mobile` LIKE  :keyword  OR `nickname` LIKE :keyword)';
        $params[':keyword'] = '%' . trim($_GPC['keyword']) . '%';
    }
    $size     = 10;
    $page     = $_GPC['page'];
    $sqlTotal = pdo_sql_select_count_from('drive_vip_order') . $condition;
    $sqlData  = pdo_sql_select_all_from('drive_vip_order') . $condition . ' ORDER BY `createtime` DESC ';
    $lists    = pdo_pagination($sqlTotal, $sqlData, $params, '', $total, $page, $size);
    if (!empty($lists)) {
        foreach ($lists as &$row) {
            load()->model('mc');
            $userInfo        = mc_member($row['uid']);
            $row['avatar']   = tomedia($userInfo['avatar']);
            $row['nickname'] = $userInfo['nickname'];
            $row['mobile']   = $userInfo['mobile'];

            $row['viptimeText'] = intval($row['viptime'] / 86400);

            $row['createtimeText'] = date("Y-m-d H:i", $row['createtime']);
        }
    }
    $return = array(
        'lists' => $lists,
        'total' => $total,
    );
    message(1, '获取VIP购买记录列表成功', $return);
}
if ($do == 'delete') {
    $id = $_GPC['id'];
    if (pdo_delete('drive_vip_order', array('id' => $id))) {
        message(1, '删除VIP购买记录成功', null);

    } else {
        message(0, '删除VIP购买记录失败，请重试', null);
    }

}

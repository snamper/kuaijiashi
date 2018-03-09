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
        $condition .= ' AND (`mobile` LIKE  :keyword  OR `realname` LIKE :keyword)';
        $params[':keyword'] = '%' . trim($_GPC['keyword']) . '%';
    }
    $size     = 10;
    $page     = $_GPC['page'];
    $sqlTotal = pdo_sql_select_count_from('drive_coach_order') . $condition;
    $sqlData  = pdo_sql_select_all_from('drive_coach_order') . $condition . ' ORDER BY `createtime` DESC ';
    $lists    = pdo_pagination($sqlTotal, $sqlData, $params, '', $total, $page, $size);
    if (!empty($lists)) {
        foreach ($lists as &$row) {
            $coach = pdo_fetch_one('drive_coach', array('id' => $row['cid']));
            load()->model('mc');
            $buyer           = mc_member($row['uid']);
            $row['avatar']   = tomedia($buyer['avatar']);
            $row['nickname'] = $buyer['nickname'];
            $row['mobile']   = $buyer['mobile'];

            $row['seller_avatar']   = tomedia($coach['avatar']);
            $row['seller_nickname'] = $coach['realname'];
            $row['seller_mobile']   = $coach['mobile'];

            $row['createtimeText'] = date("Y-m-d H:i", $row['createtime']);
            if (!empty($row['paytime'])) {
                $row['paytimeText'] = date("Y-m-d H:i", $row['paytime']);
            }
            if (!empty($row['finishtime'])) {
                $row['finishtimeText'] = date("Y-m-d H:i", $row['finishtime']);
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
    }
    $return = array(
        'lists' => $lists,
        'total' => $total,
    );
    message(1, '获取订单列表成功', $return);
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

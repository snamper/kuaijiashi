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
    $sqlTotal = pdo_sql_select_count_from('drive_withdraw') . $condition;
    $sqlData  = pdo_sql_select_all_from('drive_withdraw') . $condition . ' ORDER BY `createtime` DESC ';
    $lists    = pdo_pagination($sqlTotal, $sqlData, $params, '', $total, $page, $size);
    if (!empty($lists)) {
        foreach ($lists as &$row) {
            load()->model('mc');
            $userInfo           = mc_member($row['uid']);
            $row['avatar']   = tomedia($userInfo['avatar']);
            $row['nickname'] = $userInfo['nickname'];
            $row['mobile']   = $userInfo['mobile'];

            $row['createtimeText'] = date("Y-m-d H:i", $row['createtime']);
            if (!empty($row['review'])) {
                $row['reviewText'] = date("Y-m-d H:i", $row['review']);
            }

            switch ($row['status']) {
                case '0':
                    $row['statusText'] = '未审核';
                    break;
                case '1':
                    $row['statusText'] = '已审核'; //追加付款、取消提现
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
    message(1, '获取提现列表成功', $return);
}
if ($do == 'review') {
    $id     = $_GPC['id'];
    $update = array(
        'status'     => 1,
        'reviewtime' => TIMESTAMP,
    );
    if (pdo_update('drive_withdraw', $update, array('id' => $id))) {
        message(1, '审核提现成功', null);

    } else {
        message(0, '审核提现失败，请重试', null);
    }

}
if ($do == 'delete') {
    $id = $_GPC['id'];
    if (pdo_delete('drive_withdraw', array('id' => $id))) {
        message(1, '删除提现成功', null);

    } else {
        message(0, '删除提现失败，请重试', null);
    }

}

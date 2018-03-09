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
    $sqlTotal = pdo_sql_select_count_from('drive_coach') . $condition;
    $sqlData  = pdo_sql_select_all_from('drive_coach') . $condition . ' ORDER BY `createtime` DESC ';
    $coaches  = pdo_pagination($sqlTotal, $sqlData, $params, '', $total, $page, $size);
    if (!empty($coaches)) {
        foreach ($coaches as &$coach) {
            $coach['avatar'] = tomedia($coach['avatar']);
            if ($coach['reviewtime']) {
                $coach['reviewtimeText'] = date("Y-m-d H:i", $coach['reviewtime']);
            }

            $coach['createtimeText'] = date("Y-m-d H:i", $coach['createtime']);
            if ($coach['gender'] == 1) {
                $coach['genderText'] = '男';
            } elseif ($coach['gender'] == 2) {
                $coach['genderText'] = '女';
            } else {
                $coach['genderText'] = '保密';
            }
            if ($coach['status'] == 1) {
                $coach['statusText'] = '已认证';
            } elseif ($coach['status'] == 2) {
                $coach['statusText'] = '下架中';
            } else {
                $coach['statusText'] = '未认证';
            }
        }
    }
    $return = array(
        'lists' => $coaches,
        'total' => $total,
    );
    message(1, '获取教练列表成功', $return);
}
if ($do == 'review') {
    $id     = $_GPC['id'];
    $update = array(
        'status'     => 1,
        'reviewtime' => TIMESTAMP,
    );
    if (pdo_update('drive_coach', $update, array('id' => $id))) {
        message(1, '审核教练成功', null);

    } else {
        message(0, '审核教练失败，请重试', null);
    }

}
if ($do == 'delete') {
    $id = $_GPC['id'];
    if (pdo_delete('drive_coach', array('id' => $id))) {
        message(1, '删除教练成功', null);

    } else {
        message(0, '删除教练失败，请重试', null);
    }

}

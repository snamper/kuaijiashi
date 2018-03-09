<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;

load()->model('mc');

if ($do == 'display') {

    $creditNames = $_W['setting']['creditnames'];
    if (!empty($creditNames)) {
        foreach ($creditNames as $index => $creditName) {
            if (empty($creditName['enabled'])) {
                unset($creditNames[$index]);
            }
        }
    }
    message(1, '获取积分规则成功', $creditNames);
}
if ($do == 'getList') {

    $condition = ' WHERE 1';
    $params    = array();
    if (!empty($_GPC['keyword'])) {
        $condition .= ' AND (`mobile` LIKE  :keyword OR `email` LIKE :keyword OR `realname` LIKE :keyword OR `nickname` LIKE :keyword)';
        $params[':keyword'] = '%' . trim($_GPC['keyword']) . '%';
    }
    $size     = 10;
    $page     = $_GPC['page'];
    $sqlTotal = pdo_sql_select_count_from('mc_member') . $condition;
    $sqlData  = pdo_sql_select_all_from('mc_member') . $condition . ' ORDER BY `uid` DESC ';
    $members  = pdo_pagination($sqlTotal, $sqlData, $params, '', $total, $page, $size);
    if (!empty($members)) {
        foreach ($members as &$member) {
            $member['createtimeText'] = date("Y-m-d H:i", $member['createtime']);
            $member['profile']        = mc_profile($member['uid']);
            if ($member['profile']['gender'] == 1) {
                $member['profile']['genderText'] = '男';
            } elseif ($member['profile']['gender'] == 2) {
                $member['profile']['genderText'] = '女';
            } else {
                $member['profile']['genderText'] = '保密';
            }
        }
    }
    $return = array(
        'lists' => $members,
        'total' => $total,
    );
    message(1, '获取用户列表成功', $return);
}
if ($do == 'delete') {
    $uid = $_GPC['uid'];
    if (pdo_delete('mc_member', array('uid' => $uid))) {
        pdo_delete('mc_member_profile', array('uid' => $uid));
        message(1, '删除用户成功', null);

    } else {
        message(0, '删除用户失败', null);
    }

}

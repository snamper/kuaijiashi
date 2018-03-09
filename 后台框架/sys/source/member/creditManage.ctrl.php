<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'getList' : $do;

$creditNames = $_W['setting']['creditnames'];
if (!empty($creditNames)) {
    foreach ($creditNames as $index => $creditName) {
        if (empty($creditName['enabled'])) {
            unset($creditNames[$index]);
        }
    }
}

if ($do == 'getList') {
    $uid = intval($_GPC['uid']);
    load()->model('mc');
    $member = mc_fetch($uid);

    $size           = 20;
    $page           = max(1, intval($_GPC['page']));
    $where          = ' WHERE `uid` = :uid';
    $params[':uid'] = $uid;

    if (!empty($_GPC['type'])) {
        $where .= ' AND `credittype` = :credittype';
        $params[':credittype'] = $_GPC['type'];
    }

    if (!empty($_GPC['keyword'])) {
        $credittype = null;
        foreach ($creditNames as $key => $value) {
            if ($value['title'] == $_GPC['keyword']) {
                $credittype = $key;
            }
        }
        $operator = user_by_username($_GPC['keyword']);
        $where .= ' AND (`credittype`=:credittype OR `remark` LIKE :keyword OR `operator`=:operator)';
        $params[':keyword']    = '%' . trim($_GPC['keyword']) . '%';
        $params[':credittype'] = $credittype;
        $params[':operator']   = $operator['uid'];
    }

    $sqlTotal = pdo_sql_select_count_from('mc_credits_record') . $where;
    $sqlData  = pdo_sql_select_all_from('mc_credits_record') . $where . ' ORDER BY `id` DESC ';
    $lists    = pdo_pagination($sqlTotal, $sqlData, $params, '', $total, $page, $size);

    if (!empty($lists)) {
        foreach ($lists as &$row) {
            $row['credittypeText'] = $creditNames[$row['credittype']]['title'];
            $user_name             = user_fetch($row['operator'], array('username'));
            $row['username']       = $user_name['username'];
            $row['createtimeText'] = date("Y-m-d H:i", $row['createtime']);
        }
    }

    $return = array(
        'lists'       => $lists,
        'total'       => $total,
        'creditNames' => $creditNames,
        'userInfo'    => $member,
    );

    message(1, '获取数据成功', $return);
}
if ($do == 'manage') {
    load()->model('mc');
    $uid = intval($_GPC['uid']);
    if ($uid) {
        foreach ($_GPC['type'] as $k => $v) {
            if (($_GPC['type'][$k] == 1 || $_GPC['type'][$k] == 2) && $_GPC['money'][$k]) {
                if (empty($_GPC['money'][$k])) {
                    continue;
                }
                $value  = $_GPC['type'][$k] == 1 ? $_GPC['money'][$k] : -$_GPC['money'][$k];
                $remark = trim($_GPC['remark']);
                if (empty($remark)) {
                    $remark = '充值';
                }
                $result = mc_credit_increase($uid, $k, $value, $_W['uid'], $remark);
                if (is_error($result)) {
                    message(0, $result['message'], null);
                }
            }
        }
        $member = mc_credit_fetch($uid);
        message(1, '积分修改成功', $member);
    }
    message(0, '未找到指定用户', null);

}

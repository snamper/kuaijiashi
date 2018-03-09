<?php

defined('IN_IA') or exit('Access Denied');

$do = !empty($do) ? $do : 'list';
load()->model('coupon');
if ($do == 'list') {
    if (empty($op)) {
        $op = 'all';
    }
    $where   = ' WHERE 1';
    $params  = array();
    $keyword = trim($_GPC['keyword']);
    if (!empty($keyword)) {
        $where .= " AND name LIKE :name";
        $params[':name'] = "%{$keyword}%";
    }
    if ($op == 'future') {
        $where .= " AND start_time > :time AND enable = :enable";
        $params[':time']   = TIMESTAMP;
        $params[':enable'] = ON;
    } elseif ($op == 'on') {
        $where .= " AND start_time < :time AND end_time > :time AND enable = :enable";
        $params[':time']   = TIMESTAMP;
        $params[':enable'] = ON;
    } elseif ($op == 'end') {
        $where .= " AND enable = :enable";
        $params[':enable'] = OFF;
    }
    $size             = 10;
    $page             = $_GPC['page'];
    $sqlTotal         = pdo_sql_select_count_from('coupon_template') . $where;
    $sqlData          = pdo_sql_select_all_from('coupon_template') . $where . ' ORDER BY id DESC ';
    $coupon_templates = pdo_pagination($sqlTotal, $sqlData, $params, '', $total, $page, $size);
    $sql              = "SELECT `coupon_template_id`, COUNT(DISTINCT `uid`) as 'count_receive_person', COUNT('id') as 'count_receive_num' FROM " . tablename('coupon') . " GROUP BY `coupon_template_id`";
    $coupon_count     = pdo_fetchall($sql, array(), 'coupon_template_id');
    foreach ($coupon_templates as &$coupon_template) {
        if ($coupon_template['end_time'] < TIMESTAMP) {
            pdo_update('coupon_template', array('enable' => OFF), array('id' => $coupon_template['id']));
            cache_clean_coupon_template_all();
        }
        $coupon_template['stock']                = $coupon_template['total'] - $coupon_template['quantity_issue'];
        $coupon_template['quantity_used']        = pdo_select_count('order_activity', array('activity_id' => $coupon_template['id'], 'activity' => 'coupon'));
        $coupon_template['count_receive_num']    = $coupon_count[$coupon_template['id']]['count_receive_num'];
        $coupon_template['count_receive_person'] = $coupon_count[$coupon_template['id']]['count_receive_person'];
        $coupon_template['start_time']           = date('Y-m-d H:i:s', $coupon_template['start_time']);
        $coupon_template['end_time']             = date('Y-m-d H:i:s', $coupon_template['end_time']);
    }
    $return = array(
        'lists' => $coupon_templates,
        'total' => $total,
    );
    message(1, '获取优惠券列表成功', $return);
}

if ($do == 'display') {
    $id              = intval($_GPC['id']);
    $coupon_template = coupon_template($id);
    message(1, '获取优惠券信息成功', $coupon_template);
}

if ($do == 'post') {
    $id = intval($_GPC['id']);
    if (!empty($id) && $id != 'undefined') {
        $coupon_template = coupon_template($id);
        if (empty($coupon_template)) {
            message(0, '非法访问：访问记录不存在', null);
        }
        if ($coupon_template['enable'] == OFF) {
            message(0, '已失效不可编辑', null);
        }
    }

    $coupon_template_data = array(
        'name'        => trim($_GPC['coupon']['name']),
        'total'       => intval(trim($_GPC['coupon']['total'])),
        'value'       => currency_format($_GPC['coupon']['value']),
        'value_to'    => currency_format($_GPC['coupon']['value_to']),
        'is_random'   => $_GPC['coupon']['is_random'] ? $_GPC['coupon']['is_random'] : 1,
        'is_at_least' => $_GPC['coupon']['is_at_least'],
        'at_least'    => currency_format($_GPC['coupon']['at_least']),
        'user_level'  => $_GPC['coupon']['user_level'],
        'quota'       => intval($_GPC['coupon']['quota']),
        'start_time'  => strtotime($_GPC['coupon']['start_time']),
        'end_time'    => strtotime($_GPC['coupon']['end_time']),
        'range_type'  => $_GPC['coupon']['range_type'],
        'description' => trim($_GPC['coupon']['description']),
        'createtime'  => TIMESTAMP,
        'enable'      => ON,
        'uid'         => $_W['uid'],
    );
    if (!empty($id) && $id != 'undefined') {
        if (pdo_update('coupon_template', array('name' => trim($_GPC['coupon']['name']), 'total' => intval(trim($_GPC['coupon']['total'])), 'description' => trim($_GPC['coupon']['description'])), array('id' => $id))) {
            cache_clean_coupon_template_all();
            message(1, '更新优惠券成功', null);
        } else {
            message(0, '更新优惠券失败', null);
        }
    } else {
        if (pdo_insert('coupon_template', $coupon_template_data)) {
            cache_clean_coupon_template_all();
            message(1, '创建优惠券成功', null);
        } else {
            message(0, '创建优惠券失败', null);
        }
    }
}

if ($do == 'abate') {
    $coupon_template_id = $_GPC['id'];
    $coupon_template    = coupon_template($coupon_template_id);
    if (empty($coupon_template)) {
        message(0, '优惠券不存在或已删除', null);
    }
    if (pdo_update('coupon_template', array('enable' => OFF), array('id' => $coupon_template_id))) {
        cache_clean_coupon_template_all();
        message(1, '处理优惠券失效成功', null);
    } else {
        message(0, '处理优惠券失效失败', null);
    }
}

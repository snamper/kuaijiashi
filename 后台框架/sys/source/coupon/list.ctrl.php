<?php

defined('IN_IA') or exit('Access Denied');

$do = !empty($do) ? $do : 'display';

load()->model('mc');

if ($do == 'getList') {

    $where  = ' WHERE 1';
    $params = array();
    $id     = intval($_GPC['id']);
    if ($id) {
        $where .= " AND coupon_template_id = :coupon_template_id";
        $params[':coupon_template_id'] = $id;
    }
    $size     = 10;
    $page     = $_GPC['page'];
    $sqlTotal = pdo_sql_select_count_from('coupon') . $where;
    $sqlData  = pdo_sql_select_all_from('coupon') . $where . ' ORDER BY `createtime` DESC ';
    $coupons  = pdo_pagination($sqlTotal, $sqlData, $params, '', $total, $page, $size);
    foreach ($coupons as &$coupon) {
        $member             = mc_member($coupon['uid']);
        $coupon['avatar']   = tomedia($member['avatar']);
        $coupon['nickname'] = $member['nickname'];
        if ($coupon['status'] == 1) {
            $coupon['statusText'] = '未使用';
        } else {
            $coupon['statusText'] = '已使用';
        }
        if (!empty($coupon['usetime'])) {
            $coupon['usetimeText'] = date("Y-m-d H:i", $coupon['usetime']);
        }
        $coupon['createtimeText'] = date("Y-m-d H:i", $coupon['createtime']);
    }
    unset($coupon);
    $return = array(
        'lists' => $coupons,
        'total' => $total,
    );
    message(1, '获取优惠券列表成功', $return);
}

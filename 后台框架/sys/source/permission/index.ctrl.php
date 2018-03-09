<?php

defined('IN_IA') or exit('Access Denied');

$uid = $_W['uid'];

$condition = ' WHERE 1';
$params    = array();

if (!empty($_GPC['keyword'])) {

    $condition .= " AND ((`username` LIKE :keyword) OR (`realname` LIKE :keyword))";

    $params[':keyword'] = "%{$_GPC['keyword']}%";

}

$size     = 10;
$page     = $_GPC['page'];
$sqlTotal = pdo_sql_select_count_from('user') . $condition;
$sqlData  = pdo_sql_select_all_from('user') . $condition . ' ORDER BY `uid` ASC ';
$lists    = pdo_pagination($sqlTotal, $sqlData, $params, '', $total, $page, $size);
$return   = array(
    'lists' => $lists,
    'total' => $total,
);
message(1, '获取用户列表成功', $return);

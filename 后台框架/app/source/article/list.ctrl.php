<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;
if ($do == 'display') {

    $settings = $_W['setting']['drive'];

    $category = iunserializer($settings['category']);

    message(1, '获取文章分类成功', $category);
}

if ($do == 'getList') {

    $pindex = max(1, intval($_GPC['page']));

    $psize = 10;

    $category = $_GPC['category'];
    if (!empty($category)) {
        $condition           = ' AND `category`=:category';
        $params[':category'] = $category;
    }

    $lists = pdo_fetchall("SELECT * FROM " . tablename('drive_article') . " WHERE 1 $condition ORDER BY `createtime` DESC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);

    foreach ($lists as &$row) {
        $row['thumb']      = tomedia($row['thumb']);
        $row['createtime'] = date("Y-m-d H:i", $row['createtime']);
    }
    message(1, '获取文章列表成功', $lists);

}

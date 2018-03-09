<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;

if ($do == 'display') {

    $articles = pdo_fetchall("SELECT * FROM " . tablename('drive_article') . " ORDER BY `displayorder` ASC");
    foreach ($articles as &$row) {
        $row['thumb'] = tomedia($row['thumb']);
    }

    $return = array(
        'articles' => $articles,
    );
    message(1, '获取首页设置', $return);
}
if ($do == 'getList') {

    $condition = ' WHERE 1';
    $params    = array();
    if (!empty($_GPC['keyword'])) {
        $condition .= ' AND `title` LIKE :keyword';
        $params[':keyword'] = '%' . trim($_GPC['keyword']) . '%';
    }
    $size     = 10;
    $page     = $_GPC['page'];
    $sqlTotal = pdo_sql_select_count_from('drive_article') . $condition;
    $sqlData  = pdo_sql_select_all_from('drive_article') . $condition . ' ORDER BY `createtime` DESC ';
    $lists    = pdo_pagination($sqlTotal, $sqlData, $params, '', $total, $page, $size);
    if (!empty($lists)) {
        foreach ($lists as &$row) {
            $row['thumb'] = tomedia($row['thumb']);

            $row['createtimeText'] = date("Y-m-d H:i", $row['createtime']);
        }
    }
    $return = array(
        'lists' => $lists,
        'total' => $total,
    );
    message(1, '获取文章列表成功', $return);
}
if ($do == 'deleteArticle') {
    $id = $_GPC['id'];
    if (pdo_delete('drive_article', array('id' => $id))) {
        $articles = pdo_fetchall("SELECT * FROM " . tablename('drive_article') . " ORDER BY `displayorder` ASC");
        foreach ($articles as &$row) {
            $row['thumb'] = tomedia($row['thumb']);
        }
        message(1, '删除该文章成功', $articles);

    } else {
        message(0, '删除该文章失败', null);
    }

}

<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;

if ($do == 'display') {
    $id                = $_GPC['id'];
    $detail            = pdo_fetch_one('drive_article', array('id' => $id));
    $detail['thumb']   = tomedia($detail['thumb']);
    $detail['content'] = ihtml_entity_decode($detail['content']);

    $settings = $_W['setting']['drive'];

    $category = iunserializer($settings['category']);

    $return = array(
        'detail'   => $detail,
        'category' => $category,
    );

    message(1, '获取文章详情成功', $return);
}

if ($do == 'post') {
    $id = intval($_GPC['id']);
    if ($_W['setting']['remote']['type'] != '0') {
        $imgUrl = $_W['setting']['copyright']['ossUrl'];
    } else {
        $imgUrl = ATTACHMENT_ROOT;
    }

    $title        = $_GPC['article']['title'];
    $category     = $_GPC['article']['category'];
    $link         = $_GPC['article']['link'];
    $description  = $_GPC['article']['description'];
    $displayorder = $_GPC['article']['displayorder'];
    $content      = $_GPC['article']['content'];

    $thumb = str_ireplace($imgUrl, '', $_GPC['article']['thumb']);
    if (!empty($_GPC['article'])) {
        $data = array(
            'thumb'        => $thumb,
            'title'        => $title,
            'category'     => $category,
            'link'         => $link,
            'description'  => $description,
            'displayorder' => $displayorder,
            'content'      => $content,
            'createtime'   => TIMESTAMP,
        );
    }

    if (!empty($id) && $id != 'undefined') {
        if (pdo_update('drive_article', $data, array('id' => $id))) {
            message(1, '更新文章成功', null);
        } else {
            message(0, '更新文章失败，请重试', null);
        }
    } else {
        if (pdo_insert('drive_article', $data)) {
            message(1, '添加文章成功', null);
        } else {
            message(0, '添加文章失败，请重试', null);
        }
    }

}

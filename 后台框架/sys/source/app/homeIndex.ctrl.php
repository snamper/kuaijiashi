<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;

if ($do == 'display') {

    $banners = pdo_fetchall("SELECT * FROM " . tablename('drive_banner') . " ORDER BY `displayorder` ASC");
    foreach ($banners as &$row) {
        $row['thumb'] = tomedia($row['thumb']);
    }

    $navigations = pdo_fetchall("SELECT * FROM " . tablename('drive_navigation') . " ORDER BY `displayorder` ASC");
    foreach ($navigations as &$row) {
        $row['thumb'] = tomedia($row['thumb']);
    }

    $articles = pdo_fetchall("SELECT * FROM " . tablename('drive_article') . " ORDER BY RAND() LIMIT 5");
    foreach ($articles as &$row) {
        $row['thumb'] = tomedia($row['thumb']);
    }

    $settings = $_W['setting']['drive'];

    $settings['vips'] = iunserializer($settings['vips']);

    $settings['cityData'] = iunserializer($settings['cityData']);

    $settings['tags'] = iunserializer($settings['tags']);

    $settings['category'] = iunserializer($settings['category']);

    $return = array(
        'banners'     => $banners,
        'navigations' => $navigations,
        'articles'    => $articles,
        'settings'    => $settings,
    );
    message(1, '获取首页设置', $return);
}
if ($do == 'bannerPost') {
    $id           = $_GPC['id'];
    $title        = $_GPC['title'];
    $thumb        = $_GPC['thumb'];
    $link         = $_GPC['link'];
    $displayorder = $_GPC['displayorder'];
    $data         = array(
        'title'        => $title,
        'thumb'        => $thumb,
        'link'         => $link,
        'displayorder' => $displayorder,
        'createtime'   => TIMESTAMP,
    );
    if (!empty($id) && $id != 'undefined') {
        if (pdo_update('drive_banner', $data, array('id' => $id))) {
            $banners = pdo_fetchall("SELECT * FROM " . tablename('drive_banner') . " ORDER BY `displayorder` ASC");
            foreach ($banners as &$row) {
                $row['thumb'] = tomedia($row['thumb']);
            }
            message(1, '更新该轮播图成功', $banners);
        } else {
            message(0, '更新该轮播图失败', null);
        }
    } else {
        if (pdo_insert('drive_banner', $data)) {
            $banners = pdo_fetchall("SELECT * FROM " . tablename('drive_banner') . " ORDER BY `displayorder` ASC");
            foreach ($banners as &$row) {
                $row['thumb'] = tomedia($row['thumb']);
            }
            $id = pdo_insertid();
            message(1, '增加该轮播图成功', $banners);
        } else {
            message(0, '增加该轮播图失败', null);
        }
    }

}
if ($do == 'deleteBanner') {
    $id = $_GPC['id'];
    if (pdo_delete('drive_banner', array('id' => $id))) {
        $banners = pdo_fetchall("SELECT * FROM " . tablename('drive_banner') . " ORDER BY `displayorder` ASC");
        foreach ($banners as &$row) {
            $row['thumb'] = tomedia($row['thumb']);
        }
        message(1, '删除该轮播图成功', $banners);

    } else {
        message(0, '删除该轮播图失败', null);
    }

}

if ($do == 'navigationPost') {
    $id           = $_GPC['id'];
    $title        = $_GPC['title'];
    $thumb        = $_GPC['thumb'];
    $link         = $_GPC['link'];
    $displayorder = $_GPC['displayorder'];
    $data         = array(
        'title'        => $title,
        'thumb'        => $thumb,
        'link'         => $link,
        'displayorder' => $displayorder,
        'createtime'   => TIMESTAMP,
    );
    if (!empty($id) && $id != 'undefined') {
        if (pdo_update('drive_navigation', $data, array('id' => $id))) {
            $navigations = pdo_fetchall("SELECT * FROM " . tablename('drive_navigation') . " ORDER BY `displayorder` ASC");
            foreach ($navigations as &$row) {
                $row['thumb'] = tomedia($row['thumb']);
            }
            message(1, '更新该导航成功', $navigations);
        } else {
            message(0, '更新该导航失败', null);
        }
    } else {
        if (pdo_insert('drive_navigation', $data)) {
            $navigations = pdo_fetchall("SELECT * FROM " . tablename('drive_navigation') . " ORDER BY `displayorder` ASC");
            foreach ($navigations as &$row) {
                $row['thumb'] = tomedia($row['thumb']);
            }
            $id = pdo_insertid();
            message(1, '增加该导航成功', $navigations);
        } else {
            message(0, '增加该导航失败', null);
        }
    }

}
if ($do == 'deleteNavigation') {
    $id = $_GPC['id'];
    if (pdo_delete('drive_navigation', array('id' => $id))) {
        $navigations = pdo_fetchall("SELECT * FROM " . tablename('drive_navigation') . " ORDER BY `displayorder` ASC");
        foreach ($navigations as &$row) {
            $row['thumb'] = tomedia($row['thumb']);
        }
        message(1, '删除该导航成功', $navigations);

    } else {
        message(0, '删除该导航失败', null);
    }

}

if ($do == 'saveConfig') {

    $cityDataJson = str_ireplace('&quot;', '"', stripcslashes($_GPC['cityData']));
    $cityData     = json_decode($cityDataJson, true);

    $article   = $_GPC['article'];
    $ad1_thumb = $_GPC['ad1_thumb'];
    $ad1_link  = $_GPC['ad1_link'];
    $ad2_thumb = $_GPC['ad2_thumb'];
    $ad2_link  = $_GPC['ad2_link'];
    $ad3_thumb = $_GPC['ad3_thumb'];
    $ad3_link  = $_GPC['ad3_link'];
    $coaches1  = $_GPC['coaches1'];
    $coaches2  = $_GPC['coaches2'];
    $coaches3  = $_GPC['coaches3'];
    $bidprice  = $_GPC['bidprice'];
    $vips      = $_GPC['vips'];
    $tags      = $_GPC['tags'];
    $category  = $_GPC['category'];

    $data = array(
        'article'   => $article,
        'ad1_thumb' => $ad1_thumb,
        'ad1_link'  => $ad1_link,
        'ad2_thumb' => $ad2_thumb,
        'ad2_link'  => $ad2_link,
        'ad3_thumb' => $ad3_thumb,
        'ad3_link'  => $ad3_link,
        'coaches1'  => $coaches1,
        'coaches2'  => $coaches2,
        'coaches3'  => $coaches3,
        'bidprice'  => $bidprice,
        'cityData'  => iserializer($cityData),
        'vips'      => iserializer($vips),
        'tags'      => iserializer($tags),
        'category'  => iserializer($category),
    );

    setting_save($data, 'drive');
    message(1, '更新首页其他设置成功', $data);
}

<?php

defined('IN_IA') or exit('Access Denied');
$do = empty($do) ? 'display' : $do;
if ($do == 'display') {
    $banners = pdo_fetchall("SELECT * FROM " . tablename('drive_banner') . " ORDER BY `displayorder` ASC");
    foreach ($banners as &$row) {
        $row['thumb'] = tomedia($row['thumb']);
    }

    $navigations     = pdo_fetchall("SELECT * FROM " . tablename('drive_navigation') . " ORDER BY `displayorder` ASC");
    $thePage         = 0;
    $new_navigations = array();
    foreach ($navigations as $key => $row1) {
        if (($key + 1) > ($thePage + 1) * 8) {
            $thePage++;
        }
        $row1['thumb']                = tomedia($row1['thumb']);
        $new_navigations[$thePage][] = $row1;
    }
    unset($row1);

    $settings = $_W['setting']['drive'];

    if ($settings['article']) {
        $articles = pdo_fetchall("SELECT * FROM " . tablename('drive_article') . " WHERE `id` IN (" . $settings['article'] . ")");
        foreach ($articles as &$row) {
            $row['thumb'] = tomedia($row['thumb']);
        }
    }

    if ($settings['coaches1']) {
        $coaches1 = pdo_fetchall("SELECT * FROM " . tablename('drive_coach') . " WHERE `id` IN (" . $settings['coaches1'] . ")");
        foreach ($coaches1 as &$row) {
            $row['avatar'] = tomedia($row['avatar']);
        }
    }

    if ($settings['coaches2']) {
        $coaches2 = pdo_fetchall("SELECT * FROM " . tablename('drive_coach') . " WHERE `id` IN (" . $settings['coaches2'] . ")");
        foreach ($coaches2 as &$row) {
            $row['avatar'] = tomedia($row['avatar']);
        }
    }

    if ($settings['coaches3']) {
        $coaches3 = pdo_fetchall("SELECT * FROM " . tablename('drive_coach') . " WHERE `id` IN (" . $settings['coaches3'] . ")");
        foreach ($coaches3 as &$row) {
            $row['avatar'] = tomedia($row['avatar']);
        }
    }

    $return = array(
        'banners'     => $banners,
        'navigations' => $new_navigations,
        'articles'    => $articles,
        'settings'    => $settings,
        'coaches1'    => $coaches1,
        'coaches2'    => $coaches2,
        'coaches3'    => $coaches3,
    );
    message(1, '获取首页参数成功', $return);
}

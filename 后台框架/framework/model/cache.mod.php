<?php

defined('IN_IA') or exit('Access Denied');

function cache_clean_tuan_all()
{
    $cache_key = 'tuan_all';
    cache_delete($cache_key);
}

function cache_clean_coupon_template_all()
{
    $cache_key = 'coupon_template_all';
    cache_delete($cache_key);
}
function cache_clean_cashback_all()
{
    $cache_key = 'cashback_all';
    cache_delete($cache_key);
}

function cache_build_template()
{
    load()->func('file');
    rmdirs(IA_ROOT . '/data/tpl', true);
}

function cache_build_setting()
{
    $setting = pdo_fetch_many('core_setting', array(), array(), 'key');
    if (is_array($setting)) {
        foreach ($setting as $k => $v) {
            $setting[$v['key']] = iunserializer($v['value']);
        }
        cache_write('setting', $setting);
    }
}

function cache_clean_mc_member($uid)
{
    $cache_key = 'mc_member:' . $uid;
    cache_delete($cache_key);
}

function cache_delete_categories_enable()
{
    $cache_key = 'categories_enable';
    cache_delete($cache_key);
}

function cache_build_users_struct()
{
    $struct = array();
    $result = pdo_fetchall("SHOW COLUMNS FROM " . tablename('mc_member'));
    if (!empty($result)) {
        foreach ($result as $row) {
            $struct[] = $row['Field'];
        }
        cache_write('usersfields', $struct);
    }
    return $struct;
}

function cache_build_rolez_menus()
{

    $roles = pdo_fetch_many('user_role', array(), array());
    if (empty($roles)) {
        return;
    }

    $all_menu_dic = pdo_fetch_many('user_menu', array(), array(), 'id', 'ORDER BY level, displayorder, id ASC');
    foreach ($roles as $role) {

        $id = $role['id'];

        if ($role['id'] == 1) {
            $node_ids = pdo_fetch_many('user_node', array(), array('id'), 'id');
            $node_ids = array_keys($node_ids);
            sort($node_ids);
        } else {
            $node_ids = iunserializer($role['node_ids']);
            if (!is_array($node_ids)) {
                $node_ids = array(0);
            }
        }

        $role_update = array();

        $nodes = pdo_fetch_many('user_node', array('level' => 3, 'id' => $node_ids));

        $urls = array();
        foreach ($nodes as &$node) {
            $urls[] = $node['url'];
            parse_str($node['url'], $node['query']);
        }
        unset($node);

        $role_update['nodes'] = iserializer($nodes);
        $menu_dic             = pdo_fetch_many('user_menu', array(), array(), 'id', 'ORDER BY level, displayorder, id ASC');
        $new_menu_dic         = array();
        foreach ($menu_dic as $pk => $item) {
            if ($item['level'] == 3) {
                if (in_array($item['url'], $urls)) {
                    $new_menu_dic[$pk] = $item;
                }
            } else {
                $new_menu_dic[$pk] = $item;
            }
        }
        $menu_dic = $new_menu_dic;

        foreach ($menu_dic as $pk => &$item) {
            if ($item['level'] == 3) {
                if ($item['url']) {
                    $item['gid'] = $menu_dic[$item['pid']]['pid'];
                    parse_str($item['url'], $item['query']);
                }

                $item['active_queries'] = array();
                if ($item['active_urls']) {
                    $urlArr = explode("\n", $item['active_urls']);
                    foreach ($urlArr as $url) {
                        parse_str($url, $query);
                        $item['active_queries'][] = $query;
                    }
                }
            }
        }
        unset($item);

        $menus = menu_merge($menu_dic);

        foreach ($menus as &$top) {

            if ($top['children']) {
                foreach ($top['children'] as &$group) {

                    if ($group['children']) {
                        foreach ($group['children'] as &$menu) {
                            if (empty($menu['url'])) {
                                continue;
                            }
                            $menu['output']  = 1;
                            $group['output'] = 1;
                            $top['output']   = 1;

                            if (empty($top['url'])) {
                                $top['url']   = $menu['url'];
                                $top['query'] = $menu['query'];
                                if (empty($role_update['default_url'])) {
                                    $role_update['default_url'] = $top['url'];
                                }
                            }
                        }
                    }
                }
            }
        }
        unset($top, $menu, $key, $value, $flag);

        $role_update['menus'] = iserializer($menus);

        pdo_update('user_role', $role_update, array('id' => $id));
    }
}

<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;
if ($do == 'display') {
    $settings = $_W['setting']['drive'];

    $settings['vips'] = iunserializer($settings['vips']);

    $settings['cityData'] = iunserializer($settings['cityData']);

    $settings['tags'] = iunserializer($settings['tags']);

    message(1, '获取标签成功', $settings['tags']);
}

if ($do == 'getList') {
    $pindex = max(1, intval($_GPC['page']));

    $psize = 10;

    $lists = [];

    $condition = '';

    $params = array();

    if (!empty($_GPC['keyword'])) {

        $exist = pdo_fetch("SELECT * FROM " . tablename('drive_keyword') . " WHERE  `keyword`=:keyword", array(':keyword' => $_GPC['keyword']));
        if (empty($exist)) {
            pdo_insert('drive_keyword', array('type' => 1, 'keyword' => $_GPC['keyword'], 'searchnum' => 1));
        } else {
            pdo_update('drive_keyword', array('searchnum' => $exist['searchnum'] + 1), array('keyword' => $_GPC['keyword']));
        }

        $condition .= " AND `realname` LIKE :keyword";

        $params[':keyword'] = "%{$_GPC['keyword']}%";

    }

    if (!empty($_GPC['orderby'])) {
        $orderby_array = explode(',', $_GPC['orderby']);
        if ($orderby_array[0] == 'hot') {
            $orderbyType = ' ORDER BY `reviewtime`';
        } else {
            $orderbyType = ' ORDER BY `price`';
        }
        if ($orderby_array[1] == 'up') {
            $sc = 'DESC';
        } else {
            $sc = 'ASC';
        }
        $orderby = $orderbyType . ' ' . $sc;
    }

    if (!empty($_GPC['city'])) {
        $condition .= " AND (`province` LIKE :city OR `city` LIKE :city)";

        $params[':city'] = "%{$_GPC['city']}%";
    }

    if (!empty($_GPC['type'])) {
        $condition .= " AND `type`=:type";

        $params[':type'] = $_GPC['type'];
    }

    if (!empty($_GPC['sex'])) {
        $condition .= " AND `gender`=:sex";

        $params[':sex'] = $_GPC['sex'] == '男' ? 1 : 2;
    }

    if (!empty($_GPC['tag'])) {
        $condition .= " AND (`tags` LIKE :tag)";

        $params[':tag'] = "%{$_GPC['tag']}%";
    }

    $lists = pdo_fetchall("SELECT * FROM " . tablename('drive_coach') . " WHERE `status`='1' $condition $orderby LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);

    foreach ($lists as &$row) {
        $row['description'] = ihtml_entity_decode($row['description']);

        $row['realname']    = str_replace($_GPC['keyword'], '<i style="color:red;">' . $_GPC['keyword'] . '</i>', $row['realname']);
        $row['description'] = str_replace($_GPC['keyword'], '<i style="color:red;">' . $_GPC['keyword'] . '</i>', $row['description']);
        $row['avatar']      = tomedia($row['avatar']);
        $row['orderCount']  = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('drive_coach_order') . " WHERE `cid`=:cid AND `status`='4'", array(':cid' => $row['id']));

        $orders = pdo_fetchall("SELECT * FROM " . tablename('drive_coach_order') . " WHERE `cid` = :cid AND `status`='4' ORDER BY `createtime` DESC", array(':cid' => $row['id']));
        $ids    = [0];
        foreach ($orders as $value) {
            $ids[] = $value['id'];
        }
        $ids = implode(',', $ids);

        $row['evaluationCount'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('drive_evaluation') . " WHERE `pid` IN (" . $ids . ") ORDER BY `createtime` DESC");

        $row['fansCount'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('drive_follow') . " WHERE `touid` = :touid", array(':touid' => $row['uid']));

        $row['qualityLevel'] = pdo_fetchcolumn("SELECT FLOOR(SUM(qualityLevel)/COUNT(qualityLevel)) FROM " . tablename('drive_evaluation') . " WHERE `pid` IN (" . $ids . ") ORDER BY `createtime` ASC");
        $row['serviceLevel'] = pdo_fetchcolumn("SELECT FLOOR(SUM(serviceLevel)/COUNT(serviceLevel)) FROM " . tablename('drive_evaluation') . " WHERE `pid` IN (" . $ids . ") ORDER BY `createtime` ASC");
        $row['replyLevel']   = pdo_fetchcolumn("SELECT FLOOR(SUM(replyLevel)/COUNT(replyLevel)) FROM " . tablename('drive_evaluation') . " WHERE `pid` IN (" . $ids . ") ORDER BY `createtime` ASC");

        $row['level'] = floor(($row['qualityLevel'] + $row['serviceLevel'] + $row['replyLevel']) / 3);

    }

    message(1, '获取教练列表成功', $lists);
}

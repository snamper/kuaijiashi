<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;
if ($do == 'display') {
    $id                       = $_GPC['id'];
    $coach                    = pdo_fetch_one('drive_coach', array('id' => $id));
    $coach['avatar']          = tomedia($coach['avatar']);
    $coach['idcard_front']    = tomedia($coach['idcard_front']);
    $coach['idcard_reverse']  = tomedia($coach['idcard_reverse']);
    $coach['licence_front']   = tomedia($coach['licence_front']);
    $coach['licence_reverse'] = tomedia($coach['licence_reverse']);
    $coach['description']     = ihtml_entity_decode($coach['description']);
    $coach['tags']            = iunserializer($coach['tags']);

    $coach['orderCount'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('drive_coach_order') . " WHERE `cid`=:cid AND `status`='4'", array(':cid' => $id));

    $orders = pdo_fetchall("SELECT * FROM " . tablename('drive_coach_order') . " WHERE `cid` = :cid AND `status` >= '3' ORDER BY `createtime` DESC", array(':cid' => $id));
    $ids    = [0];
    foreach ($orders as $value) {
        $ids[] = $value['id'];
    }
    $ids = implode(',', $ids);

    $coach['evaluationCount'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('drive_evaluation') . " WHERE `pid` IN (" . $ids . ") ORDER BY `createtime` DESC");

    $coach['fansCount'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('drive_follow') . " WHERE `touid` = :touid", array(':touid' => $coach['uid']));

    $coach['rewardCount'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('drive_reward') . " WHERE `pid` = :pid AND `status`='2'", array(':pid' => $id));

    $followArray = pdo_fetch("SELECT * FROM " . tablename('drive_follow') . " WHERE `uid`=:uid AND `touid` = :touid", array(':uid' => $_W['uid'], ':touid' => $coach['uid']));
    if (!empty($followArray)) {
        $coach['followed'] = 1;
    } else {
        $coach['followed'] = 0;
    }

    //评价
    $evaluations = pdo_fetchall("SELECT A.*, B.nickname, B.avatar FROM " . tablename('drive_evaluation') . " AS A LEFT JOIN " . tablename('mc_member') . " AS B ON A.uid = B.uid WHERE `pid` IN (" . $ids . ") ORDER BY A.createtime DESC LIMIT 3");
    foreach ($evaluations as &$row) {
        $row['tags']       = iunserializer($row['tags']);
        $row['createtime'] = date("Y-m-d H:i:s", $row['createtime']);
        $userInfo          = mc_member($row['uid']);
        $row['nickname']   = $userInfo['nickname'];
    }

    //评星

    $coach['qualityLevel'] = pdo_fetchcolumn("SELECT FLOOR(SUM(qualityLevel)/COUNT(qualityLevel)) FROM " . tablename('drive_evaluation') . " WHERE `pid` IN (" . $ids . ") ORDER BY `createtime` ASC");
    $coach['serviceLevel'] = pdo_fetchcolumn("SELECT FLOOR(SUM(serviceLevel)/COUNT(serviceLevel)) FROM " . tablename('drive_evaluation') . " WHERE `pid` IN (" . $ids . ") ORDER BY `createtime` ASC");
    $coach['replyLevel']   = pdo_fetchcolumn("SELECT FLOOR(SUM(replyLevel)/COUNT(replyLevel)) FROM " . tablename('drive_evaluation') . " WHERE `pid` IN (" . $ids . ") ORDER BY `createtime` ASC");

    $return = array(
        'detail'      => $coach,
        'evaluations' => $evaluations,
    );

    message(1, '获取教练信息成功', $return);
}
if ($do == 'buy') {
    $id     = $_GPC['id'];
    $bid    = $_GPC['bid'];
    $coach  = pdo_fetch_one('drive_coach', array('id' => $id));
    $insert = array(
        'uid'        => $_W['uid'],
        'cid'        => $id,
        'seller_uid' => $coach['uid'],
        'status'     => 1,
        'total'      => $coach['price'],
        'payment'    => $coach['price'],
        'ordersn'    => date('ymdHis') . random(3, 1),
        'createtime' => TIMESTAMP,
    );

    if (!empty($bid) && ($bid != 'undefined')) {
        $bidding            = pdo_fetch_one('drive_bidding', array('id' => $bid));
        $bidprice           = $_W['setting']['drive']['bidprice'];
        $insert['bidprice'] = $bidprice;
        $insert['payment']  = $insert['payment'] - $bidprice;
    }

    if (pdo_insert('drive_coach_order', $insert)) {
        $oid = pdo_insertid();
        if (!empty($bid) && ($bid != 'undefined')) {
            pdo_update('drive_bidding', array('cid' => $id, 'oid' => $oid, 'status' => 4, 'selecttime' => TIMESTAMP), array('id' => $bid));
        }
        message(1, '创建订单成功', $oid);
    } else {
        message(0, '创建订单失败', null);
    }
}

if ($do == 'follow') {
    app_checklogin($_W['uid']);
    $touid = $_GPC['touid'];
    if ($touid == $_W['uid']) {
        $ret = array(
            'data'    => null,
            'message' => '您不能关注自己哦',
            'status'  => 0,
        );
        exit(json_encode($ret));
    }
    $follow = pdo_fetch("SELECT * FROM " . tablename('drive_follow') . " WHERE `uid` = :uid AND `touid` = :touid", array(':uid' => $_W['uid'], ':touid' => $touid));

    if (empty($follow)) {

        $followarry = array(

            'uid'        => $_W['uid'],
            'touid'      => $touid,
            'createtime' => TIMESTAMP,

        );

        if (pdo_insert('drive_follow', $followarry)) {
            pdo_query("UPDATE " . tablename('mc_member') . " SET `follow` = `follow` + 1 WHERE `uid`=:uid", array(':uid' => $touid));
            $follow = pdo_fetch("SELECT * FROM " . tablename('drive_follow') . " WHERE `uid` = :uid AND `touid` = :touid", array(':uid' => $_W['uid'], ':touid' => $touid));

            message(1, '关注成功', 1);

        } else {

            message(0, '关注失败，请重试', 0);

        }

    } else {

        if (pdo_delete('drive_follow', array('uid' => $_W['uid'], 'touid' => $touid))) {
            pdo_query("UPDATE " . tablename('mc_member') . " SET `follow` = `follow` - 1 WHERE `uid`=:uid", array(':uid' => $touid));
            $follow = pdo_fetch("SELECT * FROM " . tablename('drive_follow') . " WHERE `uid` = :uid AND `touid` = :touid", array(':uid' => $_W['uid'], ':touid' => $touid));

            message(1, '取消关注', 0);

        } else {

            message(0, '操作失败，请重试', 0);

        }

    }

}

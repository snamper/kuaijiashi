<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;
if ($do == 'display') {
    $coach                    = pdo_fetch_one('drive_coach', array('uid' => $_W['uid']));
    $coach['avatar']          = tomedia($coach['avatar']);
    $coach['idcard_front']    = tomedia($coach['idcard_front']);
    $coach['idcard_reverse']  = tomedia($coach['idcard_reverse']);
    $coach['licence_front']   = tomedia($coach['licence_front']);
    $coach['licence_reverse'] = tomedia($coach['licence_reverse']);
    $coach['description']     = ihtml_entity_decode($coach['description']);

    $settings = $_W['setting']['drive'];

    $settings['cityData'] = iunserializer($settings['cityData']);

    $return = array(
        'cityData' => $settings['cityData'],
        'coach'    => $coach,
    );

    message(1, '获取教练信息成功', $return);
}
if ($do == 'post') {
    if ($_W['setting']['remote']['type'] != '0') {
        $imgUrl = $_W['setting']['copyright']['ossUrl'];
    } else {
        $imgUrl = ATTACHMENT_ROOT;
    }

    $exist = pdo_fetch_one('drive_coach', array('uid' => $_W['uid']));
    if ($exist['status'] == 1) {
        message(0, '请不要重复申请', null);
    }

    $realname    = $_GPC['realname'];
    $mobile      = $_GPC['mobile'];
    $gender      = $_GPC['gender'];
    $idcard      = $_GPC['idcard'];
    $price       = $_GPC['price'];
    $year        = $_GPC['year'];
    $description = $_GPC['description'];
    $city        = $_GPC['city'];
    $type        = $_GPC['type'];

    $avatar          = str_ireplace($imgUrl, '', $_GPC['avatar']);
    $idcard_front    = str_ireplace($imgUrl, '', $_GPC['idcard_front']);
    $idcard_reverse  = str_ireplace($imgUrl, '', $_GPC['idcard_reverse']);
    $licence_front   = str_ireplace($imgUrl, '', $_GPC['licence_front']);
    $licence_reverse = str_ireplace($imgUrl, '', $_GPC['licence_reverse']);

    $reside      = $_GPC['reside'];
    $resideArray = explode(' ', $reside);

    $data = array(
        'uid'             => $_W['uid'],
        'avatar'          => $avatar,
        'realname'        => $realname,
        'mobile'          => $mobile,
        'gender'          => $gender,
        'idcard'          => $idcard,
        'idcard_front'    => $idcard_front,
        'idcard_reverse'  => $idcard_reverse,
        'licence_front'   => $licence_front,
        'licence_reverse' => $licence_reverse,
        'price'           => $price,
        'year'            => $year,
        'description'     => $description,
        'province'        => $resideArray[0],
        'city'            => $resideArray[1],
        'area'            => $resideArray[2],
        'type'            => $type,
        'status'          => 0,
        'createtime'      => TIMESTAMP,
    );
    if (!empty($exist)) {
        if (pdo_update('drive_coach', $data, array('uid' => $_W['uid']))) {
            message(1, '提交成功，请等待审核', null);
        } else {
            message(0, '提交失败，请重试', null);
        }
    } else {
        if (pdo_insert('drive_coach', $data)) {
            message(1, '提交成功，请等待审核', null);
        } else {
            message(0, '提交失败，请重试', null);
        }
    }
}

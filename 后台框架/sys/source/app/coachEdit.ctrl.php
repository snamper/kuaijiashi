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

    $coach['tags'] = iunserializer($coach['tags']);

    $settings = $_W['setting']['drive'];

    $tags = iunserializer($settings['tags']);

    $return = array(
        'coach' => $coach,
        'tags'  => $tags,
    );

    message(1, '获取用户详情成功', $return);
}

if ($do == 'delete') {
    if (!empty($_GPC['id'])) {
        $id = $_GPC['id'];
        pdo_delete('drive_coach', array('id' => $id));
        message(1, '删除该教练成功！', null);
    } else {
        message(0, '请选择需要删除的教练', null);
    }
}

if ($do == 'updatePassword') {
    $uid      = intval($_GPC['uid']);
    $sql      = 'SELECT `salt` FROM ' . tablename('mc_member') . ' WHERE `uid` = :uid';
    $salt     = pdo_fetchcolumn($sql, array(':uid' => $uid));
    $password = itrim($_GPC['password']);
    if (empty($password) || strlen($password) < 6 || strlen($password) > 18) {
        message(0, '密码不能为空或长度为6到18位', null);
    }
    $password = pwd_hash($password, $salt);
    pdo_update('mc_member', array('password' => $password), array('uid' => $uid));
    message(1, '更新密码成功', null);
}

if ($do == 'post') {
    $id = intval($_GPC['id']);
    if ($_W['setting']['remote']['type'] != '0') {
        $imgUrl = $_W['setting']['copyright']['ossUrl'];
    } else {
        $imgUrl = ATTACHMENT_ROOT;
    }

    $status = $_GPC['coach']['status'];

    $realname    = $_GPC['coach']['realname'];
    $mobile      = $_GPC['coach']['mobile'];
    $gender      = $_GPC['coach']['gender'];
    $idcard      = $_GPC['coach']['idcard'];
    $price       = $_GPC['coach']['price'];
    $year        = $_GPC['coach']['year'];
    $description = $_GPC['coach']['description'];
    $city        = $_GPC['coach']['city'];
    $type        = $_GPC['coach']['type'];
    $tags        = $_GPC['coach']['tags'];

    $avatar          = str_ireplace($imgUrl, '', $_GPC['coach']['avatar']);
    $idcard_front    = str_ireplace($imgUrl, '', $_GPC['coach']['idcard_front']);
    $idcard_reverse  = str_ireplace($imgUrl, '', $_GPC['coach']['idcard_reverse']);
    $licence_front   = str_ireplace($imgUrl, '', $_GPC['coach']['licence_front']);
    $licence_reverse = str_ireplace($imgUrl, '', $_GPC['coach']['licence_reverse']);
    if (!empty($_GPC['coach'])) {
        $data = array(
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
            'city'            => $city,
            'type'            => $type,
            'tags'            => iserializer($tags),
            'status'          => $status,
        );
    }

    if (pdo_update('drive_coach', $data, array('id' => $id))) {
        message(1, '更新成功', null);
    } else {
        message(0, '更新失败，请重试', null);
    }

}

<?php

defined('IN_IA') or exit('Access Denied');

$member = $_W['member'];

$result = mc_update($_W['uid'], array('city' => $_GPC['city']));
if (is_error($result)) {
    $return = array(
        'data'    => '',
        'message' => '更新城市失败,请重试',

        'status'  => 0,
    );
    exit(json_encode($return));

} else {
    $return = array(
        'data'    => '',
        'message' => '更新城市成功',

        'status'  => 1,
    );
    exit(json_encode($return));

}

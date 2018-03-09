<?php

defined('IN_IA') or exit('Access Denied');

if ($_W['accesstoken']['uid']) {
    $return = array(
        'data'    => null,
        'message' => '',
        'status'  => 1,
    );
} else {
    $return = array(
        'data'    => null,
        'message' => '登录过期啦',
        'status'  => 9,
    );
}
exit(json_encode($return));

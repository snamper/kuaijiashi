<?php

defined('IN_IA') or exit('Access Denied');

$pledge       = $_W['setting']['copyright']['pledge'];
$email        = $_W['setting']['copyright']['email'];
$hotline      = $_W['setting']['copyright']['hotline'];
$protocol     = ihtml_entity_decode($_W['setting']['copyright']['protocol']);
$creditInfo   = ihtml_entity_decode($_W['setting']['copyright']['creditInfo']);
$giveInfo     = ihtml_entity_decode($_W['setting']['copyright']['giveInfo']);
$registerInfo = ihtml_entity_decode($_W['setting']['copyright']['registerInfo']);
$pledgeInfo   = ihtml_entity_decode($_W['setting']['copyright']['pledgeInfo']);
$ossUrl       = $_W['setting']['copyright']['ossUrl'];
$version      = $_W['setting']['copyright']['version'];

$return = array(
    'data'   => array(
        'pledge'       => $pledge,
        'email'        => $email,
        'hotline'      => $hotline,
        'protocol'     => $protocol,
        'creditInfo'   => $creditInfo,
        'giveInfo'     => $giveInfo,
        'registerInfo' => $registerInfo,
        'pledgeInfo'   => $pledgeInfo,
        'ossUrl'       => $ossUrl,
        'version'      => $version,
    ),
    'status' => 1,
    'mag'    => '获取成功',
);
exit(json_encode($return));

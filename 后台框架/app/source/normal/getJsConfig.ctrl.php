<?php
defined('IN_IA') or exit('Access Denied');

$url = $_GPC['url'];
load()->classs('wechat.account');
$wechat      = new WechatAccount();
$jsapiTicket = $wechat->getJssdkConfig($url);

message(1, '', $jsapiTicket);

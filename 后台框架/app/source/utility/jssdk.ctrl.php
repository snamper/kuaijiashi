<?php

defined('IN_IA') or exit('Access Denied');

	$url = $_GPC['url'];
	
	load()->classs('wechat.account');

	$wechat = new WechatAccount();
	$jsapiTicket = $wechat->getJsApiTicket();
	if(is_error($jsapiTicket)){
		$jsapiTicket = $jsapiTicket['message'];
	}
	$nonceStr = random(16);
	$timestamp = TIMESTAMP;
	$string1 = "jsapi_ticket={$jsapiTicket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
	$signature = sha1($string1);

	$config = array(
		'data'=>array(
			"appId"		=> $_W['setting']['wechat_client']['appid'],
			"nonceStr"	=> $nonceStr,
			"timestamp" => "$timestamp",
			"signature" => $signature,
			"url" => $url
		),
		'dialog'=>'',
		'message'=>'',
		'status'=>'1'
	);
	exit(json_encode($config));	
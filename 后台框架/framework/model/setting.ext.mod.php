<?php

defined('IN_IA') or exit('Access Denied');

function setting_wechat() {
	global $_W;
	return $_W['setting']['wechat_client'];
}

function setting_wechat_valid() {
	$setting = setting_wechat();
	if ($setting && $setting['connect'] == YES) {
		return true;
	}
	return false;
}
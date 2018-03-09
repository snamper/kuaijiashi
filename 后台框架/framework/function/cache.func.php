<?php

defined('IN_IA') or exit('Access Denied');

load()->func('cache.' . $_W['config']['setting']['cache']);


function cache_load($cache_key) {
	global $_W;
	if ($cache_key == 'setting') {
		if (!isset($_W['setting'])) {
			$_W['setting'] = cache_read('setting');
		}
		return $_W['setting'];
	}
	if (empty($_W['cache'])) {
		$_W['cache'] = array();
	}
	if (!isset($_W['cache'][$cache_key])) {
		$_W['cache'][$cache_key] = cache_read($cache_key);
	}
	return $_W['cache'][$cache_key];
}

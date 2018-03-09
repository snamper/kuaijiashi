<?php

defined('IN_IA') or exit('Access Denied');


function setting_save($data, $key) {
	if (empty($key)) {
		return FALSE;
	}
	
	$record = array();
	$record['value'] = iserializer($data);
	$exists = pdo_select_count('core_setting', array('key'=>$key));
	if ($exists) {
		$return = pdo_update('core_setting', $record, array('key' => $key));
	} else {
		$record['key'] = $key;
		$return = pdo_insert('core_setting', $record);
	}
	cache_write('setting', '');
	
	return $return;
}


function setting_load($key = '') {
	global $_W;
	cache_load('setting');
	if (empty($_W['setting'])) {
		$settings = pdo_fetch_many('core_setting', array(), array('key', 'value'), 'key');
		if (is_array($settings)) {
			foreach ($settings as $k => &$v) {
				$settings[$k] = iunserializer($v['value']);
			}
		} else {
			$settings = array();
		}
		$_W['setting'] = $settings;
		cache_write('setting', $settings);
	}
	return $_W['setting'];
}


function setting_read($key){
	global $_W;
	return isset($_W['setting'][$key]) ? $_W['setting'][$key] : null;
}

function setting_upgrade_version($family, $version, $release) {
	$verfile = IA_ROOT . '/framework/version.inc.php';
	$verdat = <<<VER
<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

define('IMS_FAMILY', '{$family}');
define('IMS_VERSION', '{$version}');
define('IMS_RELEASE_DATE', '{$release}');
VER;
	file_put_contents($verfile, trim($verdat));
}

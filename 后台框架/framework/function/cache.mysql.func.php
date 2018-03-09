<?php

defined('IN_IA') or exit('Access Denied');


function cache_read($cache_key) {
	$cache = pdo_fetch_one('core_cache', array('key' => $cache_key));
	if (empty($cache)) {
		return null;
	}
	$expires = intval($cache['expires']);
	if ($expires > 0 && $expires < TIMESTAMP) {
		cache_delete($cache_key);
		return null;
	}
	
	return iunserializer($cache['value']);
}


function cache_search($prefix) {
	$sql = 'SELECT * FROM ' . tablename('core_cache') . ' WHERE `key` LIKE :key';
	$params = array();
	$params[':key'] = "{$prefix}%";
	$rs = pdo_fetchall($sql, $params);
	$result = array();
	foreach ((array)$rs as $v) {
		$result[$v['key']] = iunserializer($v['value']);
	}
	return $result;
}


function cache_write($cache_key, $data, $ttl = 0) {
	if (empty($cache_key) || !isset($data)) {
		return false;
	}
	
	global $_W;
	if (empty($_W['cache'])) {
		$_W['cache'] = array();
	}
	$_W['cache'][$cache_key] = $data;
	
	$ttl = intval($ttl);
	$record = array();
	$record['key'] = $cache_key;
	$record['value'] = iserializer($data);
	$record['expires'] = empty($ttl) ? 0 : (TIMESTAMP + $ttl);
	
	return pdo_insert('core_cache', $record, true);
}


function cache_delete($cache_key) {
	return pdo_delete('core_cache', array('key' => $cache_key));
}


function cache_clean($prefix = '') {
	global $_W;
	if (empty($prefix)) {
		$sql = 'DELETE FROM ' . tablename('core_cache');
		$result = pdo_query($sql);
		if ($result) {
			unset($_W['cache']);
		}
	} else {
		$sql = 'DELETE FROM ' . tablename('core_cache') . ' WHERE `key` LIKE :key';
		$params = array();
		$params[':key'] = "{$prefix}:%";
		$result = pdo_query($sql, $params);
	}
	return $result;
}

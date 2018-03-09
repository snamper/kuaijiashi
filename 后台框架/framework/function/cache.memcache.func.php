<?php 

defined('IN_IA') or exit('Access Denied');

function cache_memcache() {
	global $_W;
	static $memcacheobj;
	if (!extension_loaded('memcache')) {
		return error(1, 'Class Memcache is not found');
	}
	if(empty($memcacheobj)) {
		$config = $_W['config']['setting']['memcache'];
		$memcacheobj = new Memcache();
		if($config['pconnect']) {
			$connect = $memcacheobj->pconnect($config['server'], $config['port']);
		} else {
			$connect = $memcacheobj->connect($config['server'], $config['port']);
		}
	}
	return $memcacheobj;
}


function cache_read($cache_key) {
	$memcache = cache_memcache();
	if (is_error($memcache)) {
		return $memcache;
	}
	return $memcache->get(cache_prefix($cache_key));
}


function cache_search($cache_key) {
	return cache_read(cache_prefix($cache_key));
}


function cache_write($cache_key, $value, $ttl = 0) {
	$memcache = cache_memcache();
	if (is_error($memcache)) {
		return $memcache;
	}
	return $memcache->set(cache_prefix($cache_key), $value, MEMCACHE_COMPRESSED, $ttl);
}


function cache_delete($cache_key) {
	$memcache = cache_memcache();
	if (is_error($memcache)) {
		return $memcache;
	}
	return $memcache->delete(cache_prefix($cache_key));
}


function cache_clean($prefix = '') {
	$memcache = cache_memcache();
	if (is_error($memcache)) {
		return $memcache;
	}
	return $memcache->flush();
}

function cache_prefix($cache_key) {
	return $GLOBALS['_W']['config']['setting']['authkey'] . $cache_key;
}

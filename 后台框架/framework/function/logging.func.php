<?php

defined('IN_IA') or exit('Access Denied');


function logging_run($log, $type = 'normal', $filename = 'run', $includePost = true) {
	global $_W;
	$filename .= '.log';
	
	$path = IA_ROOT . '/data/logs/';
	if (empty($path)) {
		return error(1, '目录创建失败');
	}
	
	$logFormat = "%date %type %user %url %context";
	
	if ($includePost) {
		if(!empty($GLOBALS['_POST'])) {
			$context[] = logging_implode($GLOBALS['_POST']);
		}
	}
	
	if (is_array($log)) {
		$context[] = logging_implode($log);
	} else {
		$context[] = preg_replace('/[ \t\r\n]+/', ' ', $log);
	}
	
	$log = str_replace(explode(' ', $logFormat), array(
			'['.date('Y-m-d H:i:s', $_W['timestamp']).']',
			$type,
			$_W['username'],
			$_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"],
			implode("\n", $context),
			), $logFormat);
	
	file_put_contents($path.$filename, $log."\r\n", FILE_APPEND);
	return true;
}


function logging_error() {
	
}

function logging_mkdir() {
	$logRoot = IA_ROOT . '/data/logs/';
	$logDir = $logRoot . date('Ymd');
	if (mkdirs($logDir)) {
		return $logDir . '/';
	} else {
		return false;
	}
}

function logging_implode($array, $skip = array()) {
	$return = '';
	if(is_array($array) && !empty($array)) {
		foreach ($array as $key => $value) {
			if(empty($skip) || !in_array($key, $skip, true)) {
				if(is_array($value)) {
					$return .= "$key={".logging_implode($value, $skip)."}; ";
				} else {
					$return .= "$key=$value; ";
				}
			}
		}
	}
	return $return;
}

	
	function logging($level = 'info', $message = '') {
		$filename = IA_ROOT . '/data/logs/' . date('Ymd') . '.log';
		load()->func('file');
		mkdirs(dirname($filename));
		$content = date('Y-m-d H:i:s') . " {$level} :\n------------\n";
		if(is_string($message) && !in_array($message, array('post', 'get'))) {
			$content .= "String:\n{$message}\n";
		}
		if(is_array($message)) {
			$content .= "Array:\n";
			foreach($message as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		if($message === 'get') {
			$content .= "GET:\n";
			foreach($_GET as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		if($message === 'post') {
			$content .= "POST:\n";
			foreach($_POST as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		$content .= "\n";

		$fp = fopen($filename, 'a+');
		fwrite($fp, $content);
		fclose($fp);
	}
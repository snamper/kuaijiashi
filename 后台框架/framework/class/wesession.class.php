<?php

defined('IN_IA') or exit('Access Denied');


class WeSession {
	
	public static $openid;
	
	public static $expire;

	
	public static function start($openid, $expire = 3600) {
		WeSession::$openid = $openid;
		WeSession::$expire = $expire;
		$sess = new WeSession();
		session_set_save_handler(
			array(&$sess, 'open'),
			array(&$sess, 'close'),
			array(&$sess, 'read'),
			array(&$sess, 'write'),
			array(&$sess, 'destroy'),
			array(&$sess, 'gc')
		);
		session_start();
	}

	public function open() {
		return true;
	}

	public function close() {
		return true;
	}

	
	public function read($sessionid) {
		$sql = 'SELECT * FROM ' . tablename('core_session') . ' WHERE `sid`=:sessid AND `expiretime`>:time';
		$params = array();
		$params[':sessid'] = $sessionid;
		$params[':time'] = TIMESTAMP;
		$row = pdo_fetch($sql, $params);
		if(is_array($row) && !empty($row['data'])) {
			return $row['data'];
		}
		return false;
	}

	
	public function write($sessionid, $data) {
		$row = array();
		$row['sid'] = $sessionid;
		$row['openid'] = WeSession::$openid;
		$row['data'] = $data;
		$row['expiretime'] = TIMESTAMP + WeSession::$expire;

		return pdo_insert('core_session', $row, true) == 1;
	}

	
	public function destroy($sessionid) {
		$row = array();
		$row['sid'] = $sessionid;

		return pdo_delete('core_session', $row) == 1;
	}

	
	public function gc($expire) {
		$sql = 'DELETE FROM ' . tablename('core_session') . ' WHERE `expiretime`<:expire';

		return pdo_query($sql, array(':expire' => TIMESTAMP)) == 1;
	}
}
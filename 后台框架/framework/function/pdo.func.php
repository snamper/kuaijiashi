<?php

defined('IN_IA') or exit('Access Denied');


function pdo() {
	global $_W;
	static $db;
	if(empty($db)) {
		if ($_W['config']['db']['slave_status'] == true && !empty($_W['config']['db']['slave'])) {
			load()->classs('slave.db');
			$db = new SlaveDb('master');
		} else {
			load()->classs('db');
			if(empty($_W['config']['db']['master'])) {
				$_W['config']['db']['master'] = $GLOBALS['_W']['config']['db'];
				$db = new DB($_W['config']['db']);
			} else {
				$db = new DB('master');
			}
		}
	}
	return $db;
}


function pdo_query($sql, $params = array()) {
	return pdo()->query($sql, $params);
}


function pdo_fetchcolumn($sql, $params = array(), $column = 0) {
	return pdo()->fetchcolumn($sql, $params, $column);
}


function pdo_fetch($sql, $params = array()) {
	$result = pdo()->fetch($sql, $params);
	if (!is_array($result) || empty($result)) {
		return array();
	}
	return $result;
}


function pdo_fetch_one($tablename, $params = array(), $fields = array()){
	$result = pdo()->get($tablename, $params, $fields);
	if (!is_array($result) || empty($result)) {
		return array();
	}
	return $result;
}

function pdo_select_count($tablename, $params = array()){
	$result = pdo()->selectCount($tablename, $params);
	return intval($result);
}


function pdo_fetch_value($tablename, $field, $params = array()){
	$row = pdo_fetch_one($tablename, $params, array($field));
	return !empty($row) ? $row[$field] : NULL;
}


function pdo_fetch_many($tablename, $params = array(), $fields = array(), $key = '', $after_where = ''){
	if (!empty($fields) && !empty($key) && !in_array($key, $fields)) {
		$fields[] = $key;
	}
	$result = pdo()->getall($tablename, $params, $fields, $key, $after_where);
	if (!is_array($result) || empty($result)) {
		return array();
	}
	return $result;
}


function pdo_fetchall($sql, $params = array(), $keyfield = '') {
	$result = pdo()->fetchall($sql, $params, $keyfield);
	if (!is_array($result) || empty($result)) {
		return array();
	}
	return $result;
}


function pdo_pagination($sqlTotal, $sqlData, $params = array(), $keyfield = '',&$total = null, &$page = 1, $size = 50){
	$total = pdo_fetchcolumn($sqlTotal, $params);
	$page = max(1, intval($page));
	$size = max(1, intval($size));
	$sql = $sqlData.' LIMIT ' . ($page - 1) * $size . ',' . $size;
	$data = pdo_fetchall($sql, $params, $keyfield);
	return $data;
}

function pdo_sql_select_count_from($tablename){
	return 'SELECT COUNT(*) FROM '.tablename($tablename).' ';
}

function pdo_sql_select_all_from($tablename, $fields = array()){
	$fields = (empty($fields) || !is_array($fields)) ? '*' : ('`'.implode('`,`', $fields).'`');
	return 'SELECT ' . $fields . ' FROM '.tablename($tablename).' ';
}


function pdo_update($table, $data = array(), $params = array(), $glue = 'AND') {
	return pdo()->update($table, $data, $params, $glue);
}


function pdo_insert($table, $data = array(), $replace = FALSE) {
	return pdo()->insert($table, $data, $replace);
}


function pdo_delete($table, $params = array(), $glue = 'AND') {
	return pdo()->delete($table, $params, $glue);
}


function pdo_insertid() {
	return pdo()->insertid();
}


function pdo_begin() {
	pdo()->begin();
}


function pdo_commit() {
	pdo()->commit();
}


function pdo_rollback() {
	pdo()->rollBack();
}


function pdo_debug($output = true, $append = array()) {
	return pdo()->debug($output, $append);
}

function pdo_run($sql) {
	return pdo()->run($sql);
}


function pdo_fieldexists($tablename, $fieldname = '') {
	return pdo()->fieldexists($tablename, $fieldname);
}


function pdo_indexexists($tablename, $indexname = '') {
	return pdo()->indexexists($tablename, $indexname);
}


function pdo_fetchallfields($tablename){
	$fields = pdo_fetchall("DESCRIBE {$tablename}", array(), 'Field');
	$fields = array_keys($fields);
	return $fields;
}


function pdo_tableexists($tablename){
	return pdo()->tableexists($tablename);
}

function pdo_get($tablename, $condition = array(), $fields = array()) {
	return pdo()->get($tablename, $condition, $fields);
}

function pdo_getall($tablename, $condition = array(), $fields = array(), $keyfield = '') {
	return pdo()->getall($tablename, $condition, $fields, $keyfield);
}

function pdo_getslice($tablename, $condition = array(), $limit = array(), &$total = null, $fields = array(), $keyfield = '') {
	return pdo()->getslice($tablename, $condition, $limit, $total, $fields, $keyfield);
}

function pdo_getcolumn($tablename, $condition = array(), $field) {
	return pdo()->getcolumn($tablename, $condition, $field);
}
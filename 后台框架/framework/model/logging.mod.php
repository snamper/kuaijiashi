<?php

defined('IN_IA') or exit('Access Denied');

load()->func('logging');

function logging_operation_actions() {
	return array(
		LOG_USER_ADD => '管理员添加',
		LOG_USER_DELETE => '管理员删除',
		LOG_USER_EDIT => '管理员编辑信息',
		LOG_USER_STATUS => '管理员状态',

		LOG_GOODS_ADD => '添加商品',
		LOG_GOODS_EDIT => '商品编辑',
		LOG_GOODS_STATUS => '商品状态变更',

		LOG_ORDER_ADD => '订单创建',
		LOG_ORDER_PAY => '买家付款',
		LOG_ORDER_ADMIN_CLOSE => '管理员关闭订单',
		LOG_ORDER_ADMIN_REMARK => '管理员订单备注',
		LOG_ORDER_SELECT_EXPRESS => '修改订单快递',
		LOG_ORDER_SEND => '订单发货',
		LOG_ORDER_EDIT_ADDRESS => '编辑订单收货地址',
		LOG_ORDER_EDIT_PAYMENT => '编辑订单价格',

		LOG_ORDER_REFUND => '申请退款',
			
		LOG_SPEC_ADD => '添加规格',
		LOG_SPEC_DELETE => '删除规格',
		LOG_SPEC_EDIT => '编辑规格',
		
		LOG_ARTICLE_ADD => '添加文章',
		LOG_ARTICLE_DELETE => '删除文章',
		LOG_ARTICLE_EDIT => '编辑文章',

		LOG_BBS_ADD => '添加话题',
		LOG_BBS_DELETE => '删除话题',
		LOG_BBS_EDIT => '编辑话题',		
	);
}


function logging_operation($action, $extra = '') {
	global $_W;
	$actions = logging_operation_actions();
	
	if (empty($action) || empty($actions[$action])) {
		return error(-1, '非法操作日志动作');
	}
	$data = array(
		'uid' => $_W['uid'],
		'username' => $_W['username'],
		'nickname'=>$_W['nickname'],
		'realname'=>$_W['realname'],
		'action' => $action,
		'referer' => $_W['siteurl'],
		'ip' => CLIENT_IP,
		'extradata' => is_array($extra) ? logging_implode($extra) : $extra,
		'createtime' => TIMESTAMP,
	);
	pdo_insert('core_operate_log', $data);
	return true;
}
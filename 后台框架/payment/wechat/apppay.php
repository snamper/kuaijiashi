<?php

define('IN_APP', true);
require '../../framework/bootstrap.inc.php';


require_once '../../app/common/bootstrap.app.inc.php';

load()->model('payment');

$type = in_array($do, $dos) ? $do : '';

if($type == 'wechat'){
	$params = $_GPC['params'];

	if (!array_any($params)) {
		message(error(1, '非法的支付请求: error params'), '', 'ajax');
	}
	$paylog = paylog_fetch(array('sn' => $params['sn']));
	if (!array_any($paylog)) {
		message(error(1, '非法的支付请求: paylog not exist'), '', 'ajax');
	}

	if($_GPC['done'] == '1') {
		$input = file_get_contents('php://input');
		if ($paylog['status'] == PayLogStatus::PROCESS) {
			pdo_update('core_paylog', array('status' => PayLogStatus::SUCCESS), array('sn' => $params['sn']));
			$paylog['status'] = PayLogStatus::SUCCESS;
		}
		$paylog['from'] = 'return';
		message(error(0, $paylog), '', 'ajax');
	}

	if($paylog['status'] == PayLogStatus::SUCCESS) {
		message(error(2,'订单已支付成功,无需重复支付.'), '', 'ajax');
	}
	$params = array(
		'sn'			=> $paylog['sn'],
		'fee'			=> $paylog['fee'],
		'uid'			=> $paylog['uid'],
		'title' 		=> $paylog['title'],
		'module' 		=> $paylog['module'],
		'createtime' 	=> $paylog['createtime']
	);

	$types = payment_types_on();
	if (empty($types)) {
		message(error(2,'没有有效的支付方式, 请联系网站管理员.'), '', 'ajax');
	}
		
	$wechat = $_W['setting']['payment']['wechat_client'];
	$wOpt = app_wechat_build($params, $wechat);

	if (is_error($wOpt)) {
		if ($wOpt['message'] == 'invalid out_trade_no' || $wOpt['message'] == 'OUT_TRADE_NO_USED') {
			message(error(1, "抱歉，发起支付失败，系统已经修复此问题，请重新尝试支付。"), '', 'ajax');
		}
		message(error(1, "抱歉，发起支付失败，具体原因为：“{$wOpt['errno']}:{$wOpt['message']}”。请及时联系APP客服。"), '', 'ajax');
	}
	else{
		message(error(0, $wOpt), '', 'ajax');	
	}
}
elseif($type == 'credit2') {
	$setting = $_W['setting'];
	$credtis = mc_credit_fetch($_W['uid']);
	$credit = $credtis['credit2'];
	$fee = floatval($params['fee'] / 100.0);
	$fee = currency_format($fee);
	if($credit < $fee) {
		message(error(1, "余额不足，需余额 {$fee}，当前余额 {$credit}。"), '''', 'ajax');
	}

	$result = mc_credit_increase($paylog['uid'], 'credit2', -$fee, 0, $paylog['title']);
	if (is_error($result)) {
		message(error(1, $result['message']), '', 'ajax');
	}

	pdo_update('core_paylog', array('status' => '1'), array('sn' => $params['sn']));
	cache_clean_mc_member($_W['uid']);
	$paylog['status'] = PayLogStatus::SUCCESS;
	$paylog['from'] = 'return';
	$paylog['result'] = 'success';

	message(error(0, '支付成功'), '', 'ajax');
}
else{
	message(error(1, "抱歉，选择支付方式失败，请重新选择支付方式。"), '', 'ajax');
}
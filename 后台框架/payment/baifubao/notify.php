<?php

define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
require '../../web/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->model('payment');

global $_GET, $_POST;
$gpc = array_merge(array(), $_GET, $_POST);

$setting = $_W['setting'];
if(!is_array($setting['payment'])) {
	exit('没有设定支付参数.');
}
$payment = $setting['payment']['baifubao'];
require 'bfb_sdk.php';
$bfb_sdk = new bfb_sdk();
if (!empty($gpc['pay_result']) && $gpc['pay_result'] == '1') {
	if (true === $bfb_sdk->check_bfb_pay_result_notify()) {
		$paylog = paylog_fetch(array('sn' => $gpc['order_no']));
		if(!empty($paylog) && $paylog['status'] == PayLogStatus::PROCESS) {
			$paylog['status'] = PayLogStatus::SUCCESS;
			$paylog['tag']['bfb'] = $gpc;
			$paylog['tag']['bfb_order_no'] = $gpc['bfb_order_no'];
			
			$record = array();
			$record['status'] = PayLogStatus::SUCCESS;
			$record['tag'] = iserializer($paylog['tag']);
			pdo_update('core_paylog', $record, array('sn' => $paylog['order_no']));
			
			payResult($paylog);
			
			$bfb_sdk->notify_bfb();
			exit('success');
		}
	}
}

$bfb_sdk->notify_bfb();
exit('fail');

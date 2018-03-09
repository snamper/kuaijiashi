<?php

error_reporting(0);
define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
load()->model('payment');

$setting = $_W['setting'];
if(!is_array($setting['payment'])) {
	exit('没有设定支付参数.');
}
$payment = $setting['payment']['unionpay'];
require '__init.php';

if (!empty($_POST) && verify($_POST) && $_POST['respMsg'] == 'success') {
	$paylog = paylog_fetch(array('sn' => $_POST['orderId']));
	if(!empty($paylog) && ($paylog['status'] == PayLogStatus::PROCESS)) {
		
		$paylog['tag']['queryId'] = $_POST['queryId'];
		$paylog['tag']['unionpay'] = $_POST;
		$paylog['status'] = PayLogStatus::SUCCESS;
		
		$record = array();
		$record['status'] = PayLogStatus::SUCCESS;
		$record['tag'] = iserializer($paylog['tag']);
		pdo_update('core_paylog', $record, array('sn' => $paylog['sn']));
		
		payResult($paylog);
		
		exit('success');
	}
}

exit('fail');

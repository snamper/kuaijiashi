<?php
error_reporting(0);
define('IN_MOBILE', true);
if(empty($_GET['out_trade_no'])) {
	exit('request failed.');
}
require '../../framework/bootstrap.inc.php';
load()->app('common');
load()->app('template');
load()->model('payment');

$setting = $_W['setting'];
if(!is_array($setting['payment'])) {
	exit('request failed.');
}
$alipay = $setting['payment']['alipay'];
if(empty($alipay)) {
	exit('request failed.');
}
$prepares = array();
foreach($_GET as $key => $value) {
	if($key != 'sign' && $key != 'sign_type') {
		$prepares[] = "{$key}={$value}";
	}
}
sort($prepares);
$string = implode($prepares, '&');
$string .= $alipay['secret'];
$sign = md5($string);
if($sign == $_GET['sign'] && $_GET['is_success'] == 'T' && ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS')) {
	$paylog = paylog_fetch(array('sn' => $_GET['out_trade_no']));
	if(!empty($paylog)) {
		if($paylog['status'] == PayLogStatus::PROCESS) {
			pdo_update('core_paylog', array('status' => PayLogStatus::SUCCESS), array('sn' => $_GET['out_trade_no']));
			$paylog['status'] = PayLogStatus::SUCCESS;
		}
		$paylog['from'] = 'return';
		payResult($paylog);
	}
}

message('支付宝支付失败', url('member/home'));
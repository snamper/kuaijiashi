<?php

define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
require '../../web/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->model('payment');

$sl = $_GPC['params'];
$params = @json_decode(base64_decode($sl), true);

$setting = $_W['setting'];
if(!is_array($setting['payment'])) {
	error(2, '百付宝支付失败:未设置支付参数');
	exit('没有设定支付参数.');
}
$payment = $setting['payment']['baifubao'];
require 'bfb_sdk.php';

if (!empty($_GPC['pay_result']) && $_GPC['pay_result'] == '1') {
	
	global $_GET, $_POST;
	$gpc = array_merge(array(), $_GET, $_POST);
	
	$bfb_sdk = new bfb_sdk();
	if (true === $bfb_sdk->check_bfb_pay_result_notify()) {
		$paylog = paylog_fetch(array('sn' => $gpc['order_no']));
		if (empty($paylog)) {
			exit('fail: paylog not exists');
		}
		if($paylog['status'] == PayLogStatus::PROCESS) {
			$paylog['tag']['bfb'] = $gpc;
			$paylog['tag']['bfb_order_no'] = $gpc['bfb_order_no'];
			$record = array();
			$record['status'] = PayLogStatus::SUCCESS;
			$record['tag'] = iserializer($paylog['tag']);
			pdo_update('core_paylog', $record, array('sn' => $paylog['sn']));
		}
		
		$paylog['from'] = 'return';
		payResult($paylog);
		
		$bfb_sdk->notify_bfb();
		exit('success');
	}
}

$paylog = paylog_fetch(array('sn' => $params['sn']));
if(!empty($paylog) && $paylog['status'] == PayLogStatus::SUCCESS) {
	exit('这个订单已经支付成功, 不需要重复支付.');
}
$params = array(
	'sn'			=> $paylog['sn'],
	'fee'			=> $paylog['fee'],
	'uid'			=> $paylog['uid'],
	'title' 		=> $paylog['title'],
	'module' 		=> $paylog['module'],
	'createtime' 	=> $paylog['createtime']
);
$sl = base64_encode(json_encode($params));
$auth = sha1($sl . $_W['config']['setting']['authkey']);
if($auth != $_GPC['auth']) {
	exit('百付宝支付失败:参数传输错误.');
}

$bfb_sdk = new bfb_sdk();

$params = array (
	'service_code' => sp_conf::BFB_PAY_INTERFACE_SERVICE_ID,
	'sp_no' => sp_conf::$SP_NO,
	'order_create_time' => date("YmdHis"),
	'order_no' => $paylog['sn'],
	'goods_name' => iconv('utf-8', 'gbk', $params['title']),
	'total_amount' => $params['fee'],
	'currency' => sp_conf::BFB_INTERFACE_CURRENTCY,
	'buyer_sp_username' => $params['uid'],
	'return_url' => $_W['siteroot'] . 'notify.php',
	'page_url' => $_W['siteroot'] . 'pay.php',
	'pay_type' => '2',
	'bank_no' => '201', 
	'expire_time' => date('YmdHis', strtotime('+15 day')),
	'input_charset' => sp_conf::BFB_INTERFACE_ENCODING,
	'version' => sp_conf::BFB_INTERFACE_VERSION,
	'sign_method' => sp_conf::SIGN_METHOD_MD5,
	'extra' => '',
);

$order_url = $bfb_sdk->create_baifubao_pay_order_url($params, sp_conf::BFB_PAY_WAP_DIRECT_URL);
if(false !== $order_url) {
	echo "<script>window.location=\"" . $order_url . "\";</script>";
	exit;
}
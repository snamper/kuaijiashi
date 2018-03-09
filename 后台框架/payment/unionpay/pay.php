<?php

define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
require '../../web/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');

$sl = $_GPC['params'];
$params = @json_decode(base64_decode($sl), true);

$setting = $_W['setting'];
if(!is_array($setting['payment'])) {
	exit('没有设定支付参数.');
}
$payment = $setting['payment']['unionpay'];
require '__init.php';

if (!empty($_POST) && verify($_POST) && $_POST['respMsg'] == 'success') {
	$paylog = paylog_fetch(array('sn' => $params['sn']));
	if(!empty($paylog) && $paylog['status'] == PayLogStatus::PROCESS) {
		$paylog['tag'] = iunserializer($paylog['tag']);
		$paylog['tag']['queryId'] = $_POST['queryId'];

		$record = array();
		$record['status'] = PayLogStatus::SUCCESS;
		$record['tag'] = iserializer($paylog['tag']);
		pdo_update('core_paylog', $record, array('sn' => $paylog['sn']));
	}
	
	$paylog['from'] = 'return';
	
	payResult($paylog);
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
	exit('参数传输错误.');
}
$_W['uid'] = intval($paylog['uid']);

$params = array(
	'version' => '5.0.0',
	'encoding' => 'utf-8',
	'certId' => getSignCertId(),
	'txnType' => '01',
	'txnSubType' => '01',
	'bizType' => '000201',
	'frontUrl' =>  SDK_FRONT_NOTIFY_URL,
	'backUrl' => SDK_BACK_NOTIFY_URL,
	'signMethod' => '01',
	'channelType' => '08',
	'accessType' => '0',
	'merId' => SDK_MERID,
	'orderId' => $paylog['sn'],
	'txnTime' => date('YmdHis'),
	'txnAmt' => $paylog['fee'] * 100,
	'currencyCode' => '156',
	'defaultPayType' => '0001',
	'reqReserved' => $_W['uniacid'],
);
sign($params);
$html_form = create_html($params, SDK_FRONT_TRANS_URL);
echo $html_form;
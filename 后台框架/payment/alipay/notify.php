<?php
error_reporting(0);
define('IN_MOBILE', true);

require '../../framework/bootstrap.inc.php';
load()->model('payment');

if(!empty($_POST)) {
	$out_trade_no = $_POST['out_trade_no'];
	$setting = $_W['setting'];
	if(is_array($setting['payment'])) {
		$alipay = $setting['payment']['alipay'];
		if(!empty($alipay)) {
			$prepares = array();
			foreach($_POST as $key => $value) {
				if($key != 'sign' && $key != 'sign_type') {
					$prepares[] = "{$key}={$value}";
				}
			}
			sort($prepares);
			$string = implode($prepares, '&');
			$string .= $alipay['secret'];
			$sign = md5($string);
			if($sign == $_POST['sign']) {
				$paylog = paylog_fetch(array('sn' => $out_trade_no));
				if(!empty($paylog) && $paylog['status'] == PayLogStatus::PROCESS) {
					pdo_update('core_paylog', array('status' => PayLogStatus::SUCCESS), array('sn' => $paylog['sn']));
					$paylog['status'] = PayLogStatus::SUCCESS;
				}
				
				payResult($paylog);
				
				exit('success');
			}
		}
	}
}

exit('fail');

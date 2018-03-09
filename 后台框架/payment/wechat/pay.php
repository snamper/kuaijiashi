<?php

define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';

load()->classs('agent');
define('IS_WECHAT', Agent::isMicroMessage() == Agent::MICRO_MESSAGE_YES);
define('IS_MOBILE', Agent::deviceType() == Agent::DEVICE_MOBILE);
define('IS_DESKTOP', Agent::deviceType() == Agent::DEVICE_DESKTOP);	

if(IS_WECHAT || IS_MOBILE){
	require_once '../../wechat/common/bootstrap.wechat.inc.php';
}
else{
	require_once '../../web/common/bootstrap.web.inc.php';
}

load()->model('payment');

$sl = $_GPC['params'];
$params = @json_decode(base64_decode($sl), true);
if (!array_any($params)) {
	exit('非法的支付请求: error params');
}
$paylog = paylog_fetch(array('sn' => $params['sn']));
if (!array_any($paylog)) {
	exit('非法的支付请求: paylog not exist');
}

if($_GPC['done'] == '1') {
	$input = file_get_contents('php://input');
	if ($paylog['status'] == PayLogStatus::PROCESS) {
		pdo_update('core_paylog', array('status' => PayLogStatus::SUCCESS), array('sn' => $params['sn']));
		$paylog['status'] = PayLogStatus::SUCCESS;
	}
	$paylog['from'] = 'return';
	payResult($paylog);
}

if($paylog['status'] == PayLogStatus::SUCCESS) {
	exit('订单已支付成功,无需重复支付.');
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
	exit('支付签名错误.');
}

$setting = $_W['setting'];
if (empty($_W['setting']['payment']) || 
	empty($_W['setting']['payment']['wechat_client']) ||
	empty($_W['setting']['payment']['wechat_client']['switch'])) {
	exit('系统未启用微信支付.');
}
$wechat = $_W['setting']['payment']['wechat_client'];
$wOpt = wechat_build($params, $wechat);
if (is_error($wOpt)) {
	if ($wOpt['message'] == 'invalid out_trade_no' || $wOpt['message'] == 'OUT_TRADE_NO_USED') {
		message("抱歉，发起支付失败，系统已经修复此问题，请重新尝试支付。");
	}
	message("抱歉，发起支付失败，具体原因为：“{$wOpt['errno']}:{$wOpt['message']}”。请及时联系站点管理员。");
	exit;
}
?>
<script type="text/javascript">
document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
	WeixinJSBridge.invoke('getBrandWCPayRequest', {
		'appId' : '<?php echo $wOpt['appId'];?>',
		'timeStamp': '<?php echo $wOpt['timeStamp'];?>',
		'nonceStr' : '<?php echo $wOpt['nonceStr'];?>',
		'package' : '<?php echo $wOpt['package'];?>',
		'signType' : '<?php echo $wOpt['signType'];?>',
		'paySign' : '<?php echo $wOpt['paySign'];?>'
	}, function(res) {
		if(res.err_msg == 'get_brand_wcpay_request:ok') {
			location.search += '&done=1';
		} else {
			//alert('启动微信支付失败, 请检查你的支付参数. 详细错误为: ' + res.err_msg);
			history.go(-1);
		}
	});
}, false);
</script>
<?php

define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';

$input = file_get_contents('php://input');



if (preg_match('/(\<\!DOCTYPE|\<\!ENTITY)/i', $input)) {
	exit('fail');
}
libxml_disable_entity_loader(true);
$obj = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
if($obj instanceof SimpleXMLElement && !empty($obj->FeedBackId)) {
	$data = array(
		'openid' => trim($obj->OpenId),
		'appid' => trim($obj->AppId),
		'timestamp' => trim($obj->TimeStamp),
		'msgtype' => trim($obj->MsgType),
		'feedbackid' => trim($obj->FeedBackId),
		'transid' => trim($obj->TransId),
		'reason' => trim($obj->Reason),
		'solution' => trim($obj->Solution),
		'extinfo' => trim($obj->ExtInfo),
		'appsignature' => trim($obj->AppSignature),
		'signmethod' => trim($obj->SignMethod),
	);
	if (!empty($obj->PicInfo) && !empty($obj->PicInfo->item)) {
		foreach ($obj->PicInfo->item as $item) {
			$data['picinfo'][] = trim($item->PicUrl);
		}
	}
	
	mall_log('pay-rights', $input);
	
	$setting = $_W['setting'];
	if (empty($setting['payment'])) {
		exit('failed');
	}
	$data['appkey'] = $setting['payment']['wechat_client']['signkey'];
	if (!checkSign($data)) {
		exit('failed');
	}
	if ($data['msgtype'] == 'request') {
		$insert = array(
			'openid' => $data['openid'],
			'feedbackid' => $data['feedbackid'],
			'transid' => $data['transid'],
			'reason' => $data['reason'],
			'solution' => $data['solution'],
			'remark' => $data['extinfo'],
			'createtime' => $data['timestamp'],
			'status' => 0,
		);
		pdo_insert('order_feedback', $insert);
		exit('success');
	} elseif ($data['msgtype'] == 'confirm') {
		pdo_update('order_feedback', array('status' => 1), array('feedbackid' => $data['feedbackid']));
		exit('success');
	} elseif ($data['msgtype'] == 'reject') {
		pdo_update('order_feedback', array('status' => 2), array('feedbackid' => $data['feedbackid']));
		exit('success');
	} else {
		exit('failed');
	}
}
exit('failed');

function checkSign($data) {
	$string = '';
	$keys = array('appid', 'timestamp', 'openid', 'appkey');
	sort($keys);
	foreach($keys as $key) {
		$v = $data[$key];
		$key = strtolower($key);
		$string .= "{$key}={$v}&";
	}
	$string = sha1(rtrim($string, '&'));
	if ($data['appsignature'] == $string) {
		return true;
	} else {
		return false;
	}
}

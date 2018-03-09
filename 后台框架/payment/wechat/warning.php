<?php

define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
$input = file_get_contents('php://input');

$obj = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
if($obj instanceof SimpleXMLElement && !empty($obj->FeedBackId)) {
	$data = array(
		'appid' => trim($obj->AppId),
		'timestamp' => trim($obj->TimeStamp),
		'errortype' => trim($obj->ErrorType),
		'description' => trim($obj->Description),
		'alarmcontent' => trim($obj->AlarmContent),
		'appsignature' => trim($obj->AppSignature),
		'signmethod' => trim($obj->SignMethod),
	);
	mall_log('pay-warning', $input);
}
exit('success');

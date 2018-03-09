<?php

defined('IN_IA') or exit('Access Denied');

define('ALIPAY_GATEWAY', 'https://mapi.alipay.com/gateway.do');

load()->func('communication');

function paylog_create_sn($params)
{
    return date('YmdHis', TIMESTAMP) . random(8, true);
}

function paylog_fetch($params)
{
    $paylog = pdo_fetch_one('core_paylog', $params);
    if (empty($paylog)) {
        return array();
    }
    $paylog['tag'] = iunserializer($paylog['tag'], array());
    return $paylog;
}

function payment_types_on()
{
    global $_W;

    $setting = $_W['setting'];
    if (!array_any($setting['payment'])) {
        return array();
    }
    if ($setting['payment']['credit']['switch']) {
        $types[] = OrderPaymentType::CREDIT;
    }
    if ($setting['payment']['wechat_client']['switch']) {
        $types[] = OrderPaymentType::WECHAT_CLIENT;
    }
    if ($setting['payment']['wechat_app']['switch']) {
        $types[] = OrderPaymentType::WECHAT_APP;
    }
    if ($setting['payment']['wechat_web']['switch']) {
        $types[] = OrderPaymentType::WECHAT_WEB;
    }
    if ($setting['payment']['alipay']['switch']) {
        $types[] = OrderPaymentType::ALIPAY;
    }
    if ($setting['payment']['unionpay']['switch']) {
        $types[] = OrderPaymentType::UNIONPAY;
    }
    if ($setting['payment']['baifubao']['switch']) {
        $types[] = OrderPaymentType::BAIFUBAO;
    }
    if ($setting['payment']['delivery']['switch']) {
        $types[] = OrderPaymentType::DELIVERY;
    }
    return $types;
}

function alipay_build($params, $alipay = array())
{
    global $_W;

    load()->func('communication');

    $set                   = array();
    $set['service']        = 'alipay.wap.create.direct.pay.by.user';
    $set['partner']        = $alipay['partner'];
    $set['_input_charset'] = 'utf-8';
    $set['sign_type']      = 'MD5';
    $set['notify_url']     = $_W['siteroot'] . 'payment/alipay/notify.php';
    $set['return_url']     = $_W['siteroot'] . 'payment/alipay/return.php';
    $set['out_trade_no']   = $params['sn'];
    $set['subject']        = $params['title'];
    $set['payment_type']   = 1;
    $set['total_fee']      = $params['fee'] / 100.0;
    $set['seller_id']      = $alipay['account'];
    $set['body']           = $params['title'];

    $prepares = array();
    foreach ($set as $key => $value) {
        if ($key != 'sign' && $key != 'sign_type') {
            $prepares[] = "{$key}={$value}";
        }
    }
    sort($prepares);
    $string = implode('&', $prepares);
    $string .= $alipay['secret'];
    $set['sign'] = md5($string);

    $response = ihttp_request(ALIPAY_GATEWAY . '?' . http_build_query($set, '', '&'), array(), array('CURLOPT_FOLLOWLOCATION' => 0));

    return array('url' => $response['headers']['Location']);
}

function wechat_pay_sign(&$data, $signKey)
{
    ksort($data, SORT_STRING);

    $stringA = '';
    foreach ($data as $key => $value) {
        if ($value) {
            $stringA .= "{$key}={$value}&";
        }
    }
    $stringA .= "key={$signKey}";
    $sign = strtoupper(md5($stringA));

    return $sign;
}

function wechat_pay_check_sign($data, $signKey)
{
    if (empty($data)) {
        return false;
    }
    $packet = $data;
    unset($packet['sign']);
    $sign = wechat_pay_sign($packet, $signKey);
    return $sign == $data['sign'];
}

function wechat_pay_result($boolean)
{
    if ($boolean) {
        $result = array(
            'return_code' => 'SUCCESS',
            'return_msg'  => 'OK',
        );
    } else {
        $result = array(
            'return_code' => 'FAIL',
            'return_msg'  => '',
        );
    }
    return $result;
}

function wechat_pay_build($params, $wechat)
{
    global $_W, $_GPC;
    $package                     = array();
    $package['appid']            = $wechat['appid'];
    $package['mch_id']           = $wechat['mchid'];
    $package['nonce_str']        = random(8);
    $package['body']             = $params['title'];
    $package['out_trade_no']     = $params['sn'];
    $package['total_fee']        = $params['fee'];
    $package['spbill_create_ip'] = CLIENT_IP;
    $package['time_start']       = date('YmdHis', TIMESTAMP);
    $package['time_expire']      = date('YmdHis', TIMESTAMP + 600);
    $package['notify_url']       = $_W['siteroot'] . 'payment/wechat/notify.php';
    $package['trade_type']       = empty($params['trade_type']) ? 'JSAPI' : $params['trade_type'];
    if ($params['trade_type'] == 'JSAPI') {
        $package['openid'] = $params['openid'];
    }

    $package['sign'] = wechat_pay_sign($package, $wechat['signkey']);

    $dat      = array2xml($package);
    $response = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);

    if (is_error($response)) {
        return $response;
    }
    $xml = @isimplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
    if (strval($xml->return_code) == 'FAIL') {
        return error(-1, strval($xml->return_msg));
    }
    if (strval($xml->result_code) == 'FAIL') {
        return error(-1, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
    }
    $prepayid = $xml->prepay_id;

    if ($params['trade_type'] == 'APP') {
        $wOpt              = array();
        $wOpt['appid']     = $wechat['appid'];
        $wOpt['partnerid'] = $wechat['mchid'];
        $wOpt['prepayid']  = "{$prepayid}";
        $wOpt['package']   = 'Sign=WXPay';
        $wOpt['noncestr']  = random(8);
        $wOpt['timestamp'] = (string) TIMESTAMP;
        $wOpt['sign']      = wechat_pay_sign($wOpt, $wechat['signkey']);
    } elseif ($params['trade_type'] == 'JSAPI') {
        $wOpt              = array();
        $wOpt['appId']     = $wechat['appid'];
        $wOpt['timeStamp'] = (string) TIMESTAMP;
        $wOpt['nonceStr']  = random(8);
        $wOpt['package']   = 'prepay_id=' . $prepayid;
        $wOpt['signType']  = 'MD5';
        $wOpt['paySign']   = wechat_pay_sign($wOpt, $wechat['signkey']);
    }
    return $wOpt;
}

function wechat_app_pay_build($params, $wechat)
{
    global $_W;

    $package                     = array();
    $package['appid']            = $wechat['appid'];
    $package['mch_id']           = $wechat['mchid'];
    $package['device_info']      = 'WEB';
    $package['nonce_str']        = random(8);
    $package['sign']             = wechat_pay_sign($package, $wechat['signkey']);
    $package['body']             = $params['title'];
    $package['out_trade_no']     = $params['sn'];
    $package['total_fee']        = $params['fee'];
    $package['spbill_create_ip'] = CLIENT_IP;
    $package['time_start']       = date('YmdHis', TIMESTAMP);
    $package['time_expire']      = date('YmdHis', TIMESTAMP + 600);
    $package['notify_url']       = $_W['siteroot'] . 'payment/wechat/notify.php';
    $package['trade_type']       = 'APP';
    $package['openid']           = $_W['uid'];

    $dat      = array2xml($package);
    $response = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);
    if (is_error($response)) {
        return $response;
    }
    $xml = @isimplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
    if (strval($xml->return_code) == 'FAIL') {
        return error(-1, strval($xml->return_msg));
    }
    if (strval($xml->result_code) == 'FAIL') {
        return error(-1, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
    }
    $prepayid = $xml->prepay_id;

    $wOpt              = array();
    $wOpt['appId']     = $wechat['appid'];
    $wOpt['mchId']     = $wechat['mchid'];
    $wOpt['prepayId']  = $prepayid;
    $wOpt['timeStamp'] = (string) TIMESTAMP;
    $wOpt['nonceStr']  = random(8);
    $wOpt['package']   = 'prepay_id=' . $prepayid;
    $wOpt['signType']  = 'MD5';
    $wOpt['paySign']   = wechat_pay_sign($wOpt, $wechat['signkey']);

    return $wOpt;
}

function findKJsetting($wechat)
{

    global $_W;

    $kjsetting['appid'] = $wechat['appid'];

    $kjsetting['appsecret'] = $wechat['signkey'];

    $kjsetting['mchid'] = $wechat['mchid'];

    $kjsetting['shkey'] = $wechat['apikey'];

    $kjsetting['certpath'] = IA_ROOT . '/data/cert/apiclient_cert.pem';

    $kjsetting['keypath'] = IA_ROOT . '/data/cert/apiclient_key.pem';

    return $kjsetting;

}

function pay_cash($openid, $fee)
{

    global $_W;

    $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

    $kjsetting = findKJsetting();

    $pars = array();

    $pars['mch_appid'] = $kjsetting['appid'];

    $pars['mchid'] = $kjsetting['mchid'];

    $pars['nonce_str'] = random(32);

    $pars['partner_trade_no'] = time() . random(3, 1);

    $pars['openid'] = $openid;

    $pars['check_name'] = 'NO_CHECK';

    $pars['amount'] = $fee * 100;

    $pars['desc'] = '直播提现';

    $pars['spbill_create_ip'] = CLIENT_IP;

    ksort($pars, SORT_STRING);

    $string1 = '';

    foreach ($pars as $k => $v) {

        $string1 .= "{$k}={$v}&";

    }

    $string1 .= "key=" . $kjsetting['shkey'];

    $pars['sign'] = strtoupper(md5($string1));

    $xml = array2xml($pars);

    $extras = array();

    $extras['CURLOPT_CAINFO'] = IA_ROOT . '/data/cert/apiclient_root.pem';

    $extras['CURLOPT_SSLCERT'] = IA_ROOT . '/data/cert/apiclient_cert.pem';

    $extras['CURLOPT_SSLKEY'] = IA_ROOT . '/data/cert/apiclient_key.pem';

    $procResult = null;

    load()->func('communication');

    $resp = ihttp_request($url, $xml, $extras);

    if (is_error($resp)) {

        $procResult = $resp;

    } else {

        $arr = json_decode(json_encode((array) simplexml_load_string($resp['content'])), true);

        $xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];

        $dom = new DOMDocument();

        if ($dom->loadXML($xml)) {

            $xpath = new DOMXPath($dom);

            $code = $xpath->evaluate('string(//xml/return_code)');

            $ret = $xpath->evaluate('string(//xml/result_code)');

            if (strtolower($code) == 'success' && strtolower($ret) == 'success') {

                $payment_no = $xpath->evaluate('string(//xml/payment_no)');

                $procResult = array(

                    'errno'      => 0,

                    'error'      => 'success',

                    'payment_no' => $payment_no,

                );

            } else {

                $error = $xpath->evaluate('string(//xml/err_code_des)');

                $procResult = array(

                    'errno' => -2,

                    'error' => $error,

                );

            }

        } else {

            $procResult = array(

                'errno' => -1,

                'error' => '未知错误',

            );

        }

    }

    return $procResult;

}

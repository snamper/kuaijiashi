<?php

defined('IN_IA') or exit('Access Denied');
$do        = empty($do) ? 'display' : $do;
$key_path  = IA_ROOT . '/data/cert/apiclient_key.pem';
$cert_path = IA_ROOT . '/data/cert/apiclient_cert.pem';
$root_path = IA_ROOT . '/data/cert/apiclient_root.pem';

if ($do == 'display') {
    $settings = $_W['setting'];
    $pay      = $settings['payment'];

    if (!is_array($pay)) {
        $pay = array();
    }
    message(1, '获取数据成功', $pay);
}
if ($do == 'post') {
    $credit = array_elements(array('switch'), $_GPC['credit']);

    $alipay            = array_elements(array('switch', 'account', 'partner', 'secret'), $_GPC['alipay']);
    $alipay['account'] = trim($alipay['account']);
    $alipay['partner'] = trim($alipay['partner']);
    $alipay['secret']  = trim($alipay['secret']);
    if ($alipay['switch'] == 1 && (empty($alipay['account']) || empty($alipay['partner']) || empty($alipay['secret']))) {
        message(0, '请输入完整的支付宝接口信息.', null);
    }

    $delivery = array_elements(array('switch'), $_GPC['delivery']);

    $wechat_client            = array_elements(array('switch', 'account', 'appid', 'appsecret', 'mchid', 'apikey'), $_GPC['wechat_client']);
    $wechat_client['signkey'] = trim($wechat_client['apikey']);
    if ($wechat_client['switch'] == 1 && empty($wechat_client['account'])) {
        message(0, 'H5:请输入完整的微信支付接口信息.', null);
    }

    $wechat_app            = array_elements(array('switch', 'name', 'appid', 'appsecret', 'mchid', 'apikey'), $_GPC['wechat_app']);
    $wechat_app['signkey'] = trim($wechat_app['apikey']);
    if ($wechat_app['switch'] == 1 && empty($wechat_app['name'])) {
        message(0, 'APP:请输入完整的微信支付接口信息.', null);
    }

    $wechat_mina            = array_elements(array('switch', 'name', 'appid', 'appsecret', 'mchid', 'apikey'), $_GPC['wechat_mina']);
    $wechat_mina['signkey'] = trim($wechat_mina['apikey']);
    if ($wechat_mina['switch'] == 1 && empty($wechat_mina['name'])) {
        message(0, 'MINA:请输入完整的微信支付接口信息.', null);
    }

    $unionpay = array_elements(array('switch', 'signcertpwd', 'merid'), $_GPC['unionpay']);
    if ($unionpay['switch'] == 1 && (empty($unionpay['merid']) || empty($unionpay['signcertpwd']))) {
        message(0, '请输入完整的银联支付接口信息.', null);
    }
    if ($unionpay['switch'] == 1 && empty($_FILES['unionpay']['tmp_name']['signcertpath']) && !file_exists(IA_ROOT . '/attachment/unionpay/PM_acp.pfx')) {
        message(0, '请上联银商户私钥证书.', null);
    }

    $baifubao = array_elements(array('switch', 'signkey', 'mchid'), $_GPC['baifubao']);
    if ($baifubao['switch'] == 1 && (empty($baifubao['signkey']) || empty($baifubao['mchid']))) {
        message(0, '请输入完整的百付宝支付接口信息.', null);
    }
    if (!is_array($pay)) {
        $pay = array();
    }
    $pay['credit']        = $credit;
    $pay['alipay']        = $alipay;
    $pay['wechat_client'] = $wechat_client;
    $pay['wechat_app']    = $wechat_app;
    $pay['wechat_mina']   = $wechat_mina;
    $pay['delivery']      = $delivery;
    $pay['unionpay']      = $unionpay;
    $pay['baifubao']      = $baifubao;

    if ($unionpay['switch'] == 1 && !empty($_FILES['unionpay']['tmp_name']['signcertpath'])) {
        load()->func('file');
        mkdirs(IA_ROOT . '/attachment/unionpay/');
        file_put_contents(IA_ROOT . '/attachment/unionpay/PM_acp.pfx', file_get_contents($_FILES['unionpay']['tmp_name']['signcertpath']));
        $public_rsa = '-----BEGIN CERTIFICATE-----
MIIDNjCCAh6gAwIBAgIQEAAAAAAAAAAAAAAQBQdAIDANBgkqhkiG9w0BAQUFADAh
MQswCQYDVQQGEwJDTjESMBAGA1UEChMJQ0ZDQSBPQ0ExMB4XDTEyMTIxODAyMDA1
MVoXDTE1MTIxODAyMDA1MVowfDELMAkGA1UEBhMCQ04xDTALBgNVBAoTBE9DQTEx
ETAPBgNVBAsTCENGQ0EgTFJBMRkwFwYDVQQLExBPcmdhbml6YXRpb25hbC0xMTAw
LgYDVQQDFCc4MzEwMDAwMDAwMDgzMDQwQDAwMDQwMDAwOlNJR05AMDAwMDAwMDEw
ggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDFG+NnBXN++aUUAbgVFOt/
pi2McB79P+tmkS98Pnlj+pEvCc2nltq2VZzfJvGb1UE6lXKXoCG+NosZMj64uda9
Du2up78Z92HGdT2tkZ0RaoouR4jCY0Bmz0+5zObjR607vwBTvln9idG9ZGK2Lm35
QSxjpLolRPEnz/rgxFG9ezxVfI9eQ7JmuBk/OXyzjA1JQwAMhdAT3GJO0JMmMDvC
Q0pNyTsu1oyQPJoCaV3qPfpcvatMKYsVxo2Zeogqw2x2L6KE8BODrj6m6Ue1aUMn
9Ch1XbR/dB8M2M+nVtOAVb6DA6kVuNFlMl2uzxD8MQlhos8aT+vCx1v9p21k3+jz
AgMBAAGjDzANMAsGA1UdDwQEAwIGwDANBgkqhkiG9w0BAQUFAAOCAQEAhgW/gcDa
fqs0oWDH81XnTVvDCp5mwDo+wxgzVRTEtudU6seKcc2kiBe1RqegtUX2le/eAzcD
mo7nxHMy73ANdP/wha+P2gp+mo3buhO244pQphMV+Yu8djHTFH8+hRkCbnsrndYc
qNiJ/yhsUpaJ4nY+oEoyut0id6QddKiNPYoTFz0fy/VqNP6g+23zFy6sIg+gffVZ
6o3CsZVu9z5umUjzfV384iSWovq+/IdSZ4g/jerdPtje/CKYTmzG5nsCa/s+i7Rf
D5scSlfi7iW2Q7Sc/HlrtOAglt7IyjRSsFPPxuBXmSITc2GDKyKI46u8RXpccAUh
YspJ5MXOYLZN7A==
-----END CERTIFICATE-----';
        file_put_contents(IA_ROOT . '/attachment/unionpay/UpopRsaCert.cer', trim($public_rsa));
    }
    $pay['unionpay']['signcertexists'] = file_exists(IA_ROOT . '/attachment/unionpay/PM_acp.pfx');

    //微信支付证书
    $wechat_pem = array_elements(array('switch', 'signcert', 'signkey', 'signroot'), $_GPC['wechat_pem']);

    load()->func('file');
    $result = mkdirs(IA_ROOT . '/data/cert/');
    if (!$result) {
        message(0, "系统没有对data文件夹的写权限", null);
        return;
    }
    if (!empty($wechat_pem['signcert'])) {
        file_put_contents($cert_path, $wechat_pem['signcert']);
    } else {
        $wechat_pem['signcert'] = $pay['wechat_pem']['signcert'];
    }
    if (!empty($wechat_pem['signcert'])) {
        file_put_contents($key_path, $wechat_pem['signkey']);
    } else {
        $wechat_pem['signkey'] = $pay['wechat_pem']['signkey'];
    }
    if (!empty($wechat_pem['signroot'])) {
        file_put_contents($root_path, $wechat_pem['signroot']);
    } else {
        $wechat_pem['signroot'] = $pay['wechat_pem']['signroot'];
    }

    $wechat_pem = array(
        'switch'   => $wechat_pem['switch'],
        'signcert' => $wechat_pem['signcert'],
        'signkey'  => $wechat_pem['signkey'],
        'signroot' => $wechat_pem['signroot'],
    );
    $pay['wechat_pem'] = $wechat_pem;

    setting_save($pay, 'payment');
    message(1, '保存支付信息成功. ', null);
}

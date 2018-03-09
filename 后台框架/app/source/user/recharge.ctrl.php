<?php
defined('IN_IA') or exit('Access Denied');

$member = $_W['member'];

if ($do == 'getParams') {
    $money      = $_GPC['money'];
    $type       = $_GPC['type'];
    $tag        = array();
    if ($type == 'app') {
        $wechatPayType        = '1';
        $wechat               = $_W['setting']['payment']['wechat_app'];
        $params['trade_type'] = 'APP';
    }
    if ($type == 'wechat') {
        $wechatPayType        = '2';
        $openid               = $_W['member']['client_openid'];
        $wechat               = $_W['setting']['payment']['wechat_client'];
        $tag['client_openid'] = $openid;
        $params['openid']     = $openid;
        $params['trade_type'] = 'JSAPI';
    }
    if ($type == 'mina') {
        $wechatPayType        = '3';
        $openid               = $_W['member']['mina_openid'];
        $wechat               = $_W['setting']['payment']['wechat_mina'];
        $tag['mina_openid']   = $openid;
        $params['openid']     = $openid;
        $params['trade_type'] = 'JSAPI';
    }

    $out_trade_no = date('ymdHis') . random(3, 1) . $_W['uid'];
    $data         = array(
        'uid'        => $_W['uid'],
        'nickname'   => $member['nickname'],
        'avatar'     => $member['avatar'],
        'money'      => $money,
        'type'       => $wechatPayType,
        "ordersn"    => $out_trade_no,
        'status'     => 0,
        "createtime" => time(),
    );

    if (pdo_insert("drive_recharge_order", $data)) {
        $status  = 1;
        $message = '记录充值数据成功';

        $setting = $_W['setting'];
        if (!is_array($setting['payment'])) {
            $status  = 0;
            $message = '没有有效的支付方式, 请联系网站管理员.';
        }

        $params['oid']    = pdo_insertid();
        $params['sn']     = $out_trade_no;
        $params['uid']    = $_W['uid'];
        $params['fee']    = $money * 100;
        $params['sn']     = $out_trade_no;
        $params['module'] = 'recharge';
        $params['status'] = PayLogStatus::PROCESS;
        $params['type']   = $wechatPayType;

        $tag['uid']           = $_W['uid'];
        $params['tag']        = iserializer($tag);
        $params['createtime'] = TIMESTAMP;

        $log = pdo_get('core_paylog', array('module' => $params['module'], 'sn' => $params['sn']));
        if (empty($log)) {
            pdo_insert('core_paylog', $params);
        }

        load()->model('payment');
        $params['title']  = '微信端充值' . $money . '元';

        $wOpt = wechat_pay_build($params, $wechat);

        if (is_error($wOpt)) {
            $status = 0;
            if ($wOpt['message'] == 'invalid out_trade_no' || $wOpt['message'] == 'OUT_TRADE_NO_USED') {
                $message = '抱歉，发起支付失败，系统已经修复此问题，请重新尝试支付.';
            }
            $message = '抱歉，发起支付失败，具体原因为："' . $wOpt['errno'] . ':' . $wOpt['message'] . '"。请及时联系客服.';
        }
        $return = array(
            'data'    => $wOpt,
            'message' => $message,
            'status'  => $status,
        );
        exit(json_encode($return));
    } else {
        $return = array(
            'data'    => null,
            'message' => '记录充值数据失败',
            'status'  => '0',
        );
        exit(json_encode($return));
    }
}

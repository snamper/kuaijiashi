<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;

if ($do == 'post') {
    if ($_W['setting']['remote']['type'] != '0') {
        $imgUrl = $_W['setting']['copyright']['ossUrl'];
    } else {
        $imgUrl = ATTACHMENT_ROOT;
    }

    $description = $_GPC['description'];

    $cid = $_GPC['cid'];

    $setting  = $_W['setting']['drive'];
    $bidprice = $setting['bidprice'];

    $data = array(
        'uid'         => $_W['uid'],
        'tuijian_cid' => $cid,
        'description' => $description,
        'bidprice'    => $bidprice,
        'status'      => 1,
        'createtime'  => TIMESTAMP,
    );

    if (pdo_insert('drive_bidding', $data)) {
        $bid = pdo_insertid();
        message(1, '提交成功，请等待审核', $bid);
    } else {
        message(0, '提交失败，请重试', null);
    }
}

if ($do == 'getParams') {
    $id         = $_GPC['id'];
    $type       = $_GPC['type'];
    $useCredit1 = $_GPC['useCredit1'];
    $tag        = array();
    $params     = array();
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

    $order             = pdo_fetch("SELECT * FROM " . tablename('drive_bidding') . " WHERE `id`=:id", array(':id' => $id));
    $order['bidprice'] = (float) $order['bidprice'];
    $credit1           = (float) $_W['member']['credit1'];

    if (empty($order)) {
        message(0, '该订单不存在', null);
    }

    load()->model('payment');
    $params['title'] = '购买竞标服务';
    $cash            = 0;
    if ($useCredit1 == 1) {
        $params['fee'] = ($order['bidprice'] - $credit1) * 100;
        $wechatPayType = $wechatPayType . '2';
        $cash          = $params['fee'] <= 0 ? $credit1 * 100 : 0;
    } else {
        $params['fee'] = $order['bidprice'] * 100;
        $wechatPayType = $wechatPayType . '1';
    }

    $paylog_exists = pdo_get('core_paylog', array('module' => 'bidding', 'oid' => $id));
    if ($paylog_exists['status'] == 2) {
        message(0, '该订单已经支付过了', null);
    }
    pdo_delete('core_paylog', array('uid' => $_W['uid'], 'oid' => $id));

    $out_trade_no = date('ymdHis') . random(9, 1);

    $sn = $out_trade_no;

    $params['sn'] = $sn;

    pdo_update('drive_bidding', array('ordersn' => $out_trade_no), array('id' => $order['id']));

    //往core_play表写入数据

    //余额优先支付，且余额足够支付本次费用
    if ($useCredit1 == 1 && $params['fee'] <= 0) {
        // $paylog = array(
        //     'uid'        => $_W['uid'],
        //     'oid'        => $id,
        //     'module'     => 'bidding',
        //     'sn'         => $sn,
        //     'fee'        => 0,
        //     'cash'       => $order['bidprice'] * 100,
        //     'status'     => '1',
        //     'paytype'    => $wechatPayType,
        //     'type'       => 2,
        //     'createtime' => TIMESTAMP,
        // );
        // pdo_insert('core_paylog', $paylog);
        $remark = '余额支付:购买竞标服务' . $order['bidprice'] . '元';
        mc_credit_increase($paylog['uid'], 'credit1', -$order['bidprice'], 0, $remark);
        //pdo_update('core_paylog', array('status' => 2, 'remark' => $remark), array('sn' => $sn));
        $bidding_order = array(
            'status'  => 2,
            'paytime' => TIMESTAMP,
        );
        pdo_update('drive_bidding', $bidding_order, array('id' => $order['id']));
        message(1, '余额支付成功', 'credit1');
    }
    $paylog = array(
        'uid'        => $_W['uid'],
        'oid'        => $id,
        'module'     => 'bidding',
        'sn'         => $sn,
        'fee'        => $params['fee'],
        'cash'       => $cash,
        'status'     => '1',
        'paytype'    => $wechatPayType,
        'type'       => 2,
        'createtime' => TIMESTAMP,
    );
    pdo_insert('core_paylog', $paylog);
    $wOpt    = wechat_pay_build($params, $wechat);
    $status  = 1;
    $message = '获取支付参数成功';
    if (is_error($wOpt)) {
        $status = 0;
        if ($wOpt['message'] == 'invalid out_trade_no' || $wOpt['message'] == 'OUT_TRADE_NO_USED') {
            $message = '抱歉，发起支付失败，系统已经修复此问题，请重新尝试支付.';
        }
        $message = '抱歉，发起支付失败，具体原因为："' . $wOpt['errno'] . ':' . $wOpt['message'] . '"。请及时联系客服.';
    }
    message($status, $message, $wOpt);
}

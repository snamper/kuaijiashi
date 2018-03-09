<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;

$member = $_W['member'];

if ($do == 'display') {
    $settings = $_W['setting']['drive'];

    $vips = iunserializer($settings['vips']);

    message(1, '获取套餐列表', $vips);
}

if ($do == 'getParams') {

    $drive = $_W['setting']['drive'];

    $vips = iunserializer($drive['vips']);

    $money      = $_GPC['money'];
    $type       = $_GPC['type'];
    $useCredit1 = $_GPC['useCredit1'];
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

    $credit1 = (float) $_W['member']['credit1'];

    $viptime = 0;
    foreach ($vips as $vip) {
        if ($money == $vip['price']) {
            $viptime = (int) $vip['days'] * 86400;
            $title   = '购买VIP套餐【' . $vip['name'] . '】' . $vip['price'] . '元';
            break;
        }
    }

    $out_trade_no = date('ymdHis') . random(3, 1) . $_W['uid'];
    $data         = array(
        'uid'        => $_W['uid'],
        'nickname'   => $member['nickname'],
        'avatar'     => $member['avatar'],
        'money'      => $money,
        'viptime'    => $viptime,
        'type'       => $wechatPayType,
        "ordersn"    => $out_trade_no,
        'status'     => 0,
        "createtime" => time(),
    );

    if (pdo_insert("drive_vip_order", $data)) {

        $id = pdo_insertid();

        $status  = 1;
        $message = '记录vip下单信息成功';

        load()->model('payment');
        $params['title'] = $title;
        $cash            = 0;
        if ($useCredit1 == 1) {
            $params['fee'] = ($money - $credit1) * 100;
            $wechatPayType = $wechatPayType . '2';
            $cash          = $params['fee'] <= 0 ? $credit1 * 100 : 0;
        } else {
            $params['fee'] = $money * 100;
            $wechatPayType = $wechatPayType . '1';
        }

        $out_trade_no = date('ymdHis') . random(9, 1);

        $sn = $out_trade_no;

        $params['sn'] = $sn;

        //余额优先支付，且余额足够支付本次费用
        if ($useCredit1 == 1 && $params['fee'] <= 0) {
            // $paylog = array(
            //     'uid'        => $_W['uid'],
            //     'oid'        => $id,
            //     'module'     => 'vip',
            //     'sn'         => $sn,
            //     'fee'        => 0,
            //     'cash'       => $money * 100,
            //     'status'     => '1',
            //     'paytype'    => $wechatPayType,
            //     'type'       => 2,
            //     'createtime' => TIMESTAMP,
            // );
            // pdo_insert('core_paylog', $paylog);
            $remark = '余额支付:' . $title;
            mc_credit_increase($paylog['uid'], 'credit1', -$money, 0, $remark);
            //pdo_update('core_paylog', array('status' => 2, 'remark' => $remark), array('sn' => $sn));
            $service_order = array(
                'status'  => 2,
                'paytime' => TIMESTAMP,
            );
            pdo_update('drive_vip_order', $service_order, array('id' => $id));

            //增加时间
            $to_member = mc_member($_W['uid']);
            if (empty($to_member['is_vip'])) {
                $to_member['viptime'] = time();
            }
            mc_update($_W['uid'], array('viptime' => (int) $to_member['viptime'] + $viptime, 'is_vip' => 1));

            message(1, '余额支付成功', 'credit1');
        }

        $paylog = array(
            'uid'        => $_W['uid'],
            'oid'        => $id,
            'module'     => 'vip',
            'sn'         => $sn,
            'fee'        => $params['fee'],
            'cash'       => $cash,
            'status'     => '1',
            'paytype'    => $wechatPayType,
            'type'       => 2,
            'createtime' => TIMESTAMP,
        );
        pdo_insert('core_paylog', $paylog);

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
            'message' => '记录vip下单信息失败',
            'status'  => '0',
        );
        exit(json_encode($return));
    }
}

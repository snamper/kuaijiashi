<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;
if ($do == 'display') {
    $id                       = $_GPC['id'];
    $coach                    = pdo_fetch_one('drive_coach', array('id' => $id));
    $coach['avatar']          = tomedia($coach['avatar']);
    $coach['idcard_front']    = tomedia($coach['idcard_front']);
    $coach['idcard_reverse']  = tomedia($coach['idcard_reverse']);
    $coach['licence_front']   = tomedia($coach['licence_front']);
    $coach['licence_reverse'] = tomedia($coach['licence_reverse']);

    $coach['rewardCount'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('drive_reward') . " WHERE `pid` = :pid AND `status`='2'", array(':pid' => $id));
    $coach['rewardSum']   = pdo_fetchcolumn("SELECT SUM(total) FROM " . tablename('drive_reward') . " WHERE `pid` = :pid AND `status`='2'", array(':pid' => $id));

    message(1, '获取教练信息成功', $coach);
}

if ($do == 'getParams') {
    $to_id      = $_GPC['to_id'];
    $pid        = $_GPC['pid'];
    $type       = $_GPC['type'];
    $useCredit1 = $_GPC['useCredit1'];
    $money      = $_GPC['money'];
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

    $out_trade_no = date('ymdHis') . random(9, 1);

    $sn = $out_trade_no;

    $params['sn'] = $sn;

    $coach = pdo_fetch_one('drive_coach', array('id' => $pid));

    //不限制个人订单数
    $order = array(
        'uid'        => $_W['uid'],
        'to_uid'     => $to_id,
        'pid'        => $pid,
        'total'      => $money,
        'type'       => 'coach_reward',
        'status'     => 1,
        'ordersn'    => $out_trade_no,
        'createtime' => TIMESTAMP,
    );
    if (!pdo_insert('drive_reward', $order)) {
        messgae(0, '创建订单失败', null);
    }
    $order['id'] = pdo_insertid();

    $order['total'] = $money;
    $order['total'] = (float) $order['total'];
    $credit1        = (float) $_W['member']['credit1'];

    $to_member = mc_member($to_uid);

    load()->model('payment');
    $params['title'] = '打赏教练【' . $coach['realname'] . '】';

    $cash = 0;
    if ($useCredit1 == 1) {
        $params['fee'] = ($order['total'] - $credit1) * 100;
        $wechatPayType = $wechatPayType . '2';
        $cash          = $params['fee'] <= 0 ? $credit1 * 100 : 0;
    } else {
        $params['fee'] = $order['total'] * 100;
        $wechatPayType = $wechatPayType . '1';
    }

    //往core_play表写入数据

    //余额优先支付，且余额足够支付本次费用
    if ($useCredit1 == 1 && $params['fee'] <= 0) {
        // $paylog = array(
        //     'uid'        => $_W['uid'],
        //     'oid'        => $order['id'],
        //     'module'     => 'coach_reward',
        //     'sn'         => $sn,
        //     'fee'        => 0,
        //     'cash'       => $order['total'] * 100,
        //     'status'     => '1',
        //     'paytype'    => $wechatPayType,
        //     'type'       => 2,
        //     'createtime' => TIMESTAMP,
        // );
        // pdo_insert('core_paylog', $paylog);
        $remark1 = '余额支付:打赏教练【' . $coach['realname'] . '】' . $order['total'] . '元';
        $remark2 = '余额收入:【' . $_W['member']['nickname'] . '】' . $order['total'] . '元';
        mc_credit_increase($paylog['uid'], 'credit1', -$order['total'], 0, $remark1);
        mc_credit_increase($coach['uid'], 'credit1', $order['total'], 0, $remark2);
        //pdo_update('core_paylog', array('status' => 2, 'remark' => $remark1), array('sn' => $sn));
        $coach_award_order = array(
            'status'  => 2,
            'paytime' => TIMESTAMP,
        );
        pdo_update('drive_reward', $coach_award_order, array('id' => $order['id']));
        message(1, '余额支付成功', 'credit1');
    }

    $paylog = array(
        'uid'        => $_W['uid'],
        'oid'        => $order['id'],
        'module'     => 'coach_reward',
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

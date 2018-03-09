<?php

defined('IN_IA') or exit('Access Denied');

$do = empty($do) ? 'display' : $do;

$list = $_W['setting'];
if (!empty($list['creditnames'])) {
    $list = iunserializer($list['creditnames']);
    if (is_array($list)) {
        foreach ($list as $k => $v) {
            $credits[$k] = $v;
        }
    }
}

if ($do == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize  = 10;
    $type   = $_GPC['type'];
    $lists  = [];
    if ($type == 'credit') {
        $lists = pdo_fetchall("SELECT * FROM " . tablename('mc_credits_record') . " WHERE `uid`=:uid ORDER BY createtime DESC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':uid' => $_W['uid']));
        foreach ($lists as &$row) {
            $row['money']      = $row['num'];
            $row['createtime'] = date("Y-m-d H:i", $row['createtime']);
            $row['remark']     = $row['remark'];
            $row['type']       = $row['money'] >= 0 ? 1 : 0;
        }
    } elseif ($type == 'paylog') {
        $lists = pdo_fetchall("SELECT * FROM " . tablename('core_paylog') . " WHERE `uid`=:uid AND `status`='2' ORDER BY createtime DESC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':uid' => $_W['uid']));

        foreach ($lists as &$row) {
            $row['money']      = ($row['fee'] + $row['cash']) / 100;
            $row['createtime'] = date("Y-m-d H:i", $row['createtime']);
        }

    } elseif ($type == 'withdraw') {
        $lists = pdo_fetchall("SELECT * FROM " . tablename('drive_withdraw') . " WHERE `uid`=:uid ORDER BY `createtime` DESC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':uid' => $_W['uid']));

        foreach ($lists as &$row) {
            if ($row['status'] == 1) {
                $row['remark'] = '已审核';
            } else {
                $row['remark'] = '未审核';
            }
            $row['createtime'] = date("Y-m-d H:i", $row['createtime']);
        }

    }

    message(1, '获取积分记录成功', $lists);

}

if ($do == 'withdraw') {
    $money    = $_GPC['money'];
    $zhifubao = $_GPC['zhifubao'];
    $wechat   = $_GPC['wechat'];

    $insert = array(
        'uid'        => $_W['uid'],
        'money'      => $money,
        'zhifubao'   => $zhifubao,
        'wechat'     => $wechat,
        'status'     => 0,
        'createtime' => TIMESTAMP,
    );

    if ($_W['member']['credit1'] < $money || $money <= 0) {
        message(0, '余额不足，无法提现', null);
    }
    if (pdo_insert('drive_withdraw', $insert)) {
        mc_credit_increase($_W['uid'], 'credit1', -$money, 0, '余额提现');
        message(1, '提现成功，请等待审核', null);
    } else {
        message(0, '提现失败，请重试', null);
    }
}

if ($do == 'getParams') {
    $money  = $_GPC['money'];
    $type   = $_GPC['type'];
    $tag    = array();
    $params = array();
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

    $order = array(
        'uid'        => $_W['uid'],
        'total'      => $money,
        'type'       => 'recharge',
        'status'     => 1,
        'ordersn'    => $out_trade_no,
        'createtime' => TIMESTAMP,
    );
    if (!pdo_insert('drive_recharge_order', $order)) {
        message(0, '创建订单失败', null);
    }
    $order['id'] = pdo_insertid();

    $order['total'] = $money;
    $order['total'] = (float) $order['total'];

    load()->model('payment');
    $params['title'] = '充值' . $money . '元';
    $cash            = 0;

    $params['fee'] = $order['total'] * 100;
    $wechatPayType = $wechatPayType . '1';

    $paylog_exists = pdo_get('core_paylog', array('module' => 'recharge', 'oid' => $order['id']));
    if ($paylog_exists['status'] == 2) {
        message(0, '该订单已经支付过了', null);
    }

    //往core_play表写入数据
    $paylog = array(
        'uid'        => $_W['uid'],
        'oid'        => $order['id'],
        'module'     => 'recharge',
        'sn'         => $sn,
        'fee'        => $params['fee'],
        'cash'       => $cash,
        'status'     => '1',
        'paytype'    => $wechatPayType,
        'type'       => 1,
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

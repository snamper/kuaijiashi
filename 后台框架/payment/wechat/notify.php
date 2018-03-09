<?php

define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
load()->model('payment');

$success = false;
$input   = file_get_contents('php://input');
pdo_insert('wechat_notify', array('createtime' => TIMESTAMP, 'content' => $input));
if (!empty($input)) {
    $obj = isimplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
    $get = json_decode(json_encode($obj), true);
    if ($get['result_code'] == 'SUCCESS') {
        //先查找系统支付记录
        $paylog = paylog_fetch(array('sn' => $get['out_trade_no']));

        if (10 < $paylog['paytype'] && $paylog['paytype'] < 13) {
            $wechat                      = $_W['setting']['payment']['wechat_app'];
            $paylog['tag']['app_openid'] = $get['openid'];
            $typetext                    = 'APP端微信';
        } elseif (20 < $paylog['paytype'] && $paylog['paytype'] < 23) {
            $wechat                         = $_W['setting']['payment']['wechat_client'];
            $paylog['tag']['client_openid'] = $get['openid'];
            $typetext                       = '微信H5端';
        } elseif (30 < $paylog['paytype'] && $paylog['paytype'] < 33) {
            $wechat                       = $_W['setting']['payment']['wechat_mina'];
            $paylog['tag']['mina_openid'] = $get['openid'];
            $typetext                     = '微信小程序端';
        }

        $check_sign_result = wechat_pay_check_sign($get, $wechat['signkey']);

        if ($check_sign_result) {
            if ($paylog['status'] == PayLogStatus::PROCESS) {
                //该支付记录还处于待支付阶段
                $now                             = TIMESTAMP;
                $paylog['tag']                   = iunserializer($paylog['tag']);
                $paylog['tag']['transaction_id'] = $get['transaction_id'];
                $paylog['tag']['paytime']        = $now;
                $record                          = array();
                $record['status']                = PayLogStatus::SUCCESS;
                $record['tag']                   = iserializer($paylog['tag']);
                $remark                          = '';

                $order_update = array(
                    'transaction_id' => $get['transaction_id'],
                    'status'         => 2,
                    'paytime'        => $now,
                );
                //支付成功的回调

                //购买服务
                if ($paylog['module'] == 'coach') {

                    $order = pdo_fetch("SELECT A.*, B.realname FROM " . tablename('drive_coach_order') . " AS A LEFT JOIN " . tablename('drive_coach') . " AS B ON A.cid = B.id WHERE A.uid=:uid AND A.ordersn=:sn", array(':uid' => $paylog['uid'], ':sn' => $get['out_trade_no']));

                    //是否有使用余额一起支付
                    $useCredit1 = fmod($paylog['paytype'], 10) == 2 ? 1 : 0;
                    //使用余额支付但余额不足够
                    if ($useCredit1 && !empty($paylog['cash'])) {
                        $remark = '余额支付：购买教练【' . $order['realname'] . '】的服务' . $paylog['cash'] / 100 . '元；';
                        load()->model('mc');
                        mc_credit_increase($paylog['uid'], 'credit1', -$paylog['cash'] / 100, 0, $remark);

                    }
                    //写入core_paylog
                    $record['remark'] = $remark . '第三方支付：购买教练【' . $order['realname'] . '】的服务' . $paylog['fee'] / 100 . '元';
                    pdo_update('core_paylog', $record, array('sn' => $get['out_trade_no']));

                    if (pdo_update('drive_coach_order', $order_update, array('id' => $paylog['oid'], 'uid' => $paylog['uid']))) {
                        $success = true;
                    }
                }

                //打赏
                if ($paylog['module'] == 'coach_reward') {

                    $order = pdo_fetch("SELECT A.*, B.uid AS c_uid, B.realname FROM " . tablename('drive_reward') . " AS A LEFT JOIN " . tablename('drive_coach') . " AS B ON A.pid = B.id WHERE A.uid=:uid AND A.ordersn=:sn", array(':uid' => $paylog['uid'], ':sn' => $get['out_trade_no']));

                    //是否有使用余额一起支付
                    $useCredit1 = fmod($paylog['paytype'], 10) == 2 ? 1 : 0;
                    //使用余额支付但余额不足够
                    if ($useCredit1 && !empty($paylog['cash'])) {
                        load()->model('mc');
                        $member    = mc_member($order['uid']);
                        $to_member = mc_member($order['c_uid']);
                        $remark1   = '余额支付:打赏教练【' . $order['realname'] . '】' . $paylog['cash'] / 100 . '元；';
                        mc_credit_increase($paylog['uid'], 'credit1', -$paylog['cash'] / 100, 0, $remark1);
                        $remark2 = '余额收入:【' . $member['nickname'] . '】' . $paylog['cash'] / 100 . '元；';
                        mc_credit_increase($order['c_uid'], 'credit1', $paylog['cash'] / 100, 0, $remark2);

                    }
                    //写入core_paylog
                    $record['remark'] = $remark . '第三方支付:打赏教练【' . $order['realname'] . '】' . $paylog['fee'] / 100 . '元';
                    pdo_update('core_paylog', $record, array('sn' => $get['out_trade_no']));

                    if (pdo_update('drive_reward', $order_update, array('id' => $paylog['oid'], 'uid' => $paylog['uid']))) {
                        $success = true;
                    }
                }

                //充值
                if ($paylog['module'] == 'recharge') {

                    $order = pdo_fetch("SELECT * FROM " . tablename('drive_recharge_order') . " WHERE `uid`=:uid AND `ordersn`=:sn", array(':uid' => $paylog['uid'], ':sn' => $get['out_trade_no']));
                    //写入core_paylog
                    $record['remark'] = '第三方支付:充值' . $paylog['fee'] / 100 . '元';
                    pdo_update('core_paylog', $record, array('sn' => $get['out_trade_no']));

                    if (pdo_update('drive_recharge_order', $order_update, array('id' => $paylog['oid'], 'uid' => $paylog['uid']))) {
                        load()->model('mc');
                        mc_credit_increase($order['uid'], 'credit1', $order['total'], 0, $record['remark']);
                        $success = true;
                    }
                }

                //购买VIP
                if ($paylog['module'] == 'vip') {

                    $order = pdo_fetch("SELECT A.*, B.title FROM " . tablename('drive_vip_order') . " WHERE `uid`=:uid AND `ordersn`=:sn", array(':uid' => $paylog['uid'], ':sn' => $get['out_trade_no']));
                    //写入core_paylog
                    $record['remark'] = '第三方支付:购买VIP' . $paylog['fee'] / 100 . '元';
                    pdo_update('core_paylog', $record, array('sn' => $get['out_trade_no']));

                    if (pdo_update('drive_vip_order', $order_update, array('id' => $paylog['oid'], 'uid' => $paylog['uid']))) {
                        load()->model('mc');
                        $to_member = mc_member($order['uid']);
                        if (empty($to_member['viptime'])) {
                            $to_member['viptime'] = TIMESTAMP;
                        }
                        $viptime = $to_member['viptime'] + $order['viptime'];
                        mc_update($order['uid'], array('viptime' => $viptime, 'is_vip' => 1));
                        $success = true;
                    }
                }

                //竞标
                if ($paylog['module'] == 'bidding') {

                    $order = pdo_fetch("SELECT * FROM " . tablename('drive_bidding') . " WHERE `ordersn`=:s", array(':sn' => $get['out_trade_no']));

                    //是否有使用余额一起支付
                    $useCredit1 = fmod($paylog['paytype'], 10) == 2 ? 1 : 0;
                    //使用余额支付但余额不足够
                    if ($useCredit1 && !empty($paylog['cash'])) {
                        $remark = '余额支付：购买竞标服务' . $paylog['cash'] / 100 . '元；';
                        load()->model('mc');
                        mc_credit_increase($paylog['uid'], 'credit1', -$paylog['cash'] / 100, 0, $remark);

                    }
                    //写入core_paylog
                    $record['remark'] = $remark . '第三方支付：购买竞标服务' . $paylog['fee'] / 100 . '元';
                    pdo_update('core_paylog', $record, array('sn' => $get['out_trade_no']));

                    if (pdo_update('drive_bidding', $order_update, array('id' => $paylog['oid'], 'uid' => $paylog['uid']))) {
                        $success = true;
                    }
                }
            }

        }
    }
}

$result = wechat_pay_result($success);
echo array2xml($result);
exit;

<?php


defined('IN_IA') or exit('Access Denied');

class OrderPaymentType
{

    const CREDIT = 1;

    const WECHAT_CLIENT = 2;

    const WECHAT_APP = 3;

    const WECHAT_WEB = 4;

    const ALIPAY = 5;

    const UNIONPAY = 6;

    const BAIFUBAO = 7;

    public static function all()
    {
        return array(
            self::CREDIT,
            self::WECHAT_CLIENT,
            self::WECHAT_APP,
            self::WECHAT_WEB,
            self::ALIPAY,
            self::UNIONPAY,
            self::BAIFUBAO,
        );
    }

    public static function getText($payment_type)
    {
        $texts = array(
            self::CREDIT        => '余额支付',
            self::WECHAT_CLIENT => '微信支付',
            self::WECHAT_APP    => '微信支付',
            self::WECHAT_WEB    => '微信支付',
            self::ALIPAY        => '支付宝支付',
            self::UNIONPAY      => '网银支付',
            self::BAIFUBAO      => '百付宝支付',
        );
        return $texts[$payment_type];
    }
}

class OrderPaymentMethod
{

    const ONLINE = 1;

    const CASH_ON_DELIVERY = 2;

    public static function all()
    {
        return array(
            self::ONLINE,
            self::CASH_ON_DELIVERY,
        );
    }

    public static function getText($payment_method)
    {
        $texts = array(
            self::ONLINE           => '在线支付',
            self::CASH_ON_DELIVERY => '货到付款',
        );
        return $texts[$payment_method];
    }
}

class PayLogStatus
{

    const PROCESS = 1;

    const SUCCESS = 2;

    public static function all()
    {
        return array(
            self::PROCESS,
            self::SUCCESS,
        );
    }

    public static function getText($status)
    {
        $texts = array(
            self::PROCESS => '支付中',
            self::SUCCESS => '支付成功',
        );
        return $texts[$status];
    }
}
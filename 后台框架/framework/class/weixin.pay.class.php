<?php

defined('IN_IA') or exit('Access Denied');
class WeiXinPay
{
    public $wxpay;
    public function __construct($wechat)
    {
        global $_W;
        $wxpay = empty($wechat) ? $_W['setting']['payment']['wechat'] : $wechat;

        $this->wxpay = array(
            'appid'      => $wxpay['appid'],
            'mch_id'     => $wxpay['mchid'],
            'key'        => $wxpay['signkey'],
            'notify_url' => $_W['siteroot'] . 'payment/wechat/notify.php',
        );

    }

    public function array2url($params)
    {
        $str    = '';
        $ignore = array('coupon_refund_fee', 'coupon_refund_count');
        foreach ($params as $key => $val) {
            if ((empty($val) || is_array($val)) && !in_array($key, $ignore)) {
                continue;
            }
            $str .= "{$key}={$val}&";
        }
        $str = trim($str, '&');
        return $str;
    }

    public function bulidSign($params)
    {
        unset($params['sign']);
        ksort($params);
        $string = $this->array2url($params);
        $string = $string . "&key={$this->wxpay['key']}";
        $string = md5($string);
        $result = strtoupper($string);
        return $result;
    }

    public function parseResult($result)
    {
        if (substr($result, 0, 5) != "<xml>") {
            return $result;
        }
        $result = json_decode(json_encode(isimplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if (!is_array($result)) {
            return error(-1, 'xml结构错误');
        }
        if ((isset($result['return_code']) && $result['return_code'] != 'SUCCESS') || ($result['err_code'] == 'ERROR' && !empty($result['err_code_des']))) {
            $msg = empty($result['return_msg']) ? $result['err_code_des'] : $result['return_msg'];
            return error(-1, $msg);
        }
        if ($this->bulidsign($result) != $result['sign']) {
            return error(-1, '验证签名出错');
        }
        return $result;
    }

    public function requestApi($url, $params, $extra = array())
    {
        load()->func('communication');
        $xml      = array2xml($params);
        $response = ihttp_request($url, $xml, $extra);
        if (is_error($response)) {
            return $response;
        }
        $result = $this->parseResult($response['content']);
        return $result;
    }

    public function shortUrl($url)
    {
        $params = array(
            'appid'     => $this->wxpay['appid'],
            'mch_id'    => $this->wxpay['mch_id'],
            'long_url'  => $url,
            'nonce_str' => random(32),
        );
        $params['sign'] = $this->bulidSign($params);
        $result         = $this->requestApi('https://api.mch.weixin.qq.com/tools/shorturl', $params);
        if (is_error($result)) {
            return $result;
        }
        return $result['short_url'];
    }

    public function bulidNativePayurl($product_id, $short_url = true)
    {
        $params = array(
            'appid'      => $this->wxpay['appid'],
            'mch_id'     => $this->wxpay['mch_id'],
            'time_stamp' => TIMESTAMP,
            'nonce_str'  => random(32),
            'product_id' => $product_id,
        );
        $params['sign'] = $this->bulidSign($params);
        $url            = "weixin://wxpay/bizpayurl?" . $this->array2url($params);
        if ($short_url) {
            $url = $this->shortUrl($url);
        }
        return $url;
    }

    public function paylog2NativeUrl($params)
    {
        $result = $this->buildPayLog($params);
        if (is_error($result)) {
            return $result;
        }
        $url = $this->bulidNativePayurl($result);
        if (is_error($url)) {
            return $url;
        }
        return array('url' => $url, 'product_id' => $result);
    }

    public function buildUnifiedOrder($params)
    {
        if (empty($params['out_trade_no'])) {
            return error(-1, '缺少统一支付接口必填参数out_trade_no:商户订单号');
        }
        if (empty($params['body'])) {
            return error(-1, '缺少统一支付接口必填参数body:商品描述');
        }
        if (empty($params['total_fee'])) {
            return error(-1, '缺少统一支付接口必填参数total_fee:总金额');
        }
        if (empty($params['trade_type'])) {
            return error(-1, '缺少统一支付接口必填参数trade_type:交易类型');
        }

        if ($params['trade_type'] == 'JSAPI' && empty($params['openid'])) {
            return error(-1, '统一支付接口中，缺少必填参数openid！交易类型为JSAPI时，openid为必填参数！');
        }
        if ($params['trade_type'] == 'NATIVE' && empty($params['product_id'])) {
            return error(-1, '统一支付接口中，缺少必填参数product_id！交易类型为NATIVE时，product_id为必填参数！');
        }

        if (empty($params['notify_url'])) {
            $params['notify_url'] = $this->wxpay['notify_url'];
        }
        $params['appid']            = $this->wxpay['appid'];
        $params['mch_id']           = $this->wxpay['mch_id'];
        $params['spbill_create_ip'] = CLIENT_IP;
        $params['nonce_str']        = random(32);
        $params['sign']             = $this->bulidSign($params);

        $result = $this->requestApi('https://api.mch.weixin.qq.com/pay/unifiedorder', $params);
        if (is_error($result)) {
            return $result;
        }
        return $result;
    }

    public function buildMicroOrder($params)
    {
        if (empty($params['out_trade_no'])) {
            return error(-1, '缺少刷卡支付接口必填参数out_trade_no:商户订单号');
        }
        if (empty($params['body'])) {
            return error(-1, '缺少刷卡支付接口必填参数body:商品描述');
        }
        if (empty($params['total_fee'])) {
            return error(-1, '缺少刷卡支付接口必填参数total_fee:总金额');
        }
        if (empty($params['auth_code'])) {
            return error(-1, '缺少刷卡支付接口必填参数auth_code:授权码');
        }
        $uniontid = $params['uniontid'];
        unset($params['uniontid']);

        $params['appid']            = $this->wxpay['appid'];
        $params['mch_id']           = $this->wxpay['mch_id'];
        $params['spbill_create_ip'] = CLIENT_IP;
        $params['nonce_str']        = random(32);
        if (!empty($this->wxpay['sub_mch_id'])) {
            $params['sub_mch_id'] = $this->wxpay['sub_mch_id'];
        }
        $params['sign'] = $this->bulidSign($params);
        $result         = $this->requestApi('https://api.mch.weixin.qq.com/pay/micropay', $params);
        if (is_error($result)) {
            return $result;
        }
        if ($result['result_code'] != 'SUCCESS') {
            return array('errno' => -10, 'message' => $result['err_code_des'], 'uniontid' => $uniontid);
        }
        return $result;
    }

    public function buildJsApiPrepayid($product_id)
    {
        $order = pdo_get('core_paylog', array('plid' => $product_id));
        if (empty($order)) {
            return error(-1, '订单不存在');
        }
        if ($order['status'] == 1) {
            return error(-1, '该订单已经支付,请勿重复支付');
        }

        $jspai = array(
            'out_trade_no' => $order['uniontid'],
            'trade_type'   => 'JSAPI',
            'openid'       => $order['openid'],
            'body'         => $order['body'],
            'total_fee'    => $order['fee'] * 100,
            'attach'       => $order['uniacid'],
        );
        $result = $this->buildUnifiedOrder($jspai);
        if (is_error($result)) {
            return $result;
        }
        $jspai = array(
            'appId'     => $this->wxpay['appid'],
            'timeStamp' => TIMESTAMP,
            'nonceStr'  => random(32),
            'package'   => 'prepay_id=' . $result['prepay_id'],
            'signType'  => 'MD5',
        );
        $jspai['paySign'] = $this->bulidSign($jspai);

        $jspai = <<<EOF
		<script type="text/javascript">
			document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
				WeixinJSBridge.invoke(
					'getBrandWCPayRequest', {
						appId:'{$jspai['appId']}',
						timeStamp:'{$jspai['timeStamp']}',
						nonceStr:'{$jspai['nonceStr']}',
						package:'{$jspai['package']}',
						signType:'MD5',
						paySign:'{$jspai['paySign']}'
					},
					function(res){
						if(res.err_msg == 'get_brand_wcpay_request：ok' ) {
						 alert('支付成功')
						} else {
						}
					}
				);
			 }, false);
		</script>
EOF;
        return $jspai;
    }

    public function buildNativePrepayid($product_id)
    {
        $order = pdo_get('core_paylog', array('plid' => $product_id));
        if (empty($order)) {
            return error(-1, '订单不存在');
        }
        if ($order['status'] == 1) {
            return error(-1, '该订单已经支付,请勿重复支付');
        }

        $data = array(
            'body'         => $order['body'],
            'out_trade_no' => $order['uniontid'],
            'total_fee'    => $order['fee'] * 100,
            'trade_type'   => 'NATIVE',
            'product_id'   => $order['plid'],
            'attach'       => $order['uniacid'],
        );
        $result = $this->buildUnifiedOrder($data);
        if (is_error($result)) {
            return $result;
        }
        $params = array(
            'return_code' => 'SUCCESS',
            'appid'       => $this->wxpay['appid'],
            'mch_id'      => $this->wxpay['mch_id'],
            'prepay_id'   => $result['prepay_id'],
            'nonce_str'   => random(32),
            'result_code' => 'SUCCESS',
            'code_url'    => $result['code_url'],
        );
        $params['sign'] = $this->bulidSign($params);
        return $params;
    }

    public function replyErrorNotify($msg)
    {
        $result = array(
            'return_code' => 'FAIL',
            'return_msg'  => $msg,
        );
        echo array2xml($result);
    }

    public function closeOrder($trade_no)
    {
        $params = array(
            'appid'        => $this->wxpay['appid'],
            'mch_id'       => $this->wxpay['mch_id'],
            'nonce_str'    => random(32),
            'out_trade_no' => trim($trade_no),
        );
        $params['sign'] = $this->bulidSign($params);
        $result         = $this->requestApi('https://api.mch.weixin.qq.com/pay/closeorder', $params);
        if (is_error($result)) {
            return $result;
        }
        if ($result['result_code'] == 'SUCCESS') {
            pdo_update('paycenter_order', array('status' => 'CLOSED'), array('tradeno' => $result['out_trade_no']));
        }
        return true;
    }

    public function queryOrder($id, $type = 1)
    {
        $params = array(
            'appid'     => $this->wxpay['appid'],
            'mch_id'    => $this->wxpay['mch_id'],
            'nonce_str' => random(32),
        );
        if ($type == 1) {
            $params['transaction_id'] = $id;
        } else {
            $params['out_trade_no'] = $id;
        }
        $params['sign'] = $this->bulidSign($params);
        $result         = $this->requestApi('https://api.mch.weixin.qq.com/pay/orderquery', $params);
        if (is_error($result)) {
            return $result;
        }
        if ($result['result_code'] != 'SUCCESS') {
            return error(-1, $result['err_code_des']);
        }
        $result['total_fee'] = $result['total_fee'] / 100;return $result;
    }

    public function downloadBill($date, $type = 'ALL')
    {
        $params = array(
            'appid'     => $this->wxpay['appid'],
            'mch_id'    => $this->wxpay['mch_id'],
            'nonce_str' => random(32),
            'bill_date' => $date,
            'bill_type' => $type,
        );

        $params['sign'] = $this->bulidSign($params);
        $result         = $this->requestApi('https://api.mch.weixin.qq.com/pay/downloadbill', $params);
        return $result;
    }

    public function refundOrder($date, $type = 'ALL')
    {
        $params = array(
            'appid'     => $this->wxpay['appid'],
            'mch_id'    => $this->wxpay['mch_id'],
            'nonce_str' => random(32),
            'bill_date' => $date,
            'bill_type' => $type,
        );
        $params['sign'] = $this->bulidSign($params);
        $result         = $this->requestApi('https://api.mch.weixin.qq.com/pay/downloadbill', $params);
        return $result;
    }

    public function refund($params, $cert)
    {
        global $_W;
        $package                   = array();
        $package['appid']          = $this->wxpay['appid'];
        $package['mch_id']         = $this->wxpay['mch_id'];
        $package['nonce_str']      = random(8);
        $package['transaction_id'] = $params['transaction_id'];
        $package['out_refund_no']  = $this->wxpay['mch_id'] . date("YmdHis") . random(8);
        $package['total_fee']      = $params['fee'];
        $package['refund_fee']     = $params['fee'];
        $package['sign']           = $this->bulidSign($package);
        $result                    = $this->requestApi('https://api.mch.weixin.qq.com/secapi/pay/refund', $package, array(CURLOPT_SSLCERT => $cert['certpath'], CURLOPT_SSLKEY => $cert['keypath']));
        return $result;
    }
}

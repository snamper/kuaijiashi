<?php

defined('IN_IA') or exit('Access Denied');

load()->func('communication');

class WechatAccount
{
    public function __construct()
    {
        global $_W;

        $this->wechat_client_account = $_W['setting']['wechat_client'];
        $this->wechat_app_account    = $_W['setting']['wechat_app'];
        $this->wechat_web_account    = $_W['setting']['wechat_web'];
        $this->redirect_url          = $_W['siteroot'] . "api.php?oauth=wechat";
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

    public function is_error($resp)
    {
        return !empty($resp['errcode']);
    }

    public function error($platform_error)
    {
        $errmsg = $this->error_code($platform_error['errcode']);
        return error($platform_error['errcode'], "错误信息: {$errmsg} ({$platform_error['errcode']}: {$platform_error['errmsg']})");
    }

    public function forward($url = '')
    {
        global $_W, $_GPC;

        if ($url) {
            $url = tourl($url);
            $url = str_replace(array('&oauth=wechat'), '', $url);
            isetcookie(COOKIE_FORWARD, urlencode($url), 60);
        } else {
            $url = $_GPC[COOKIE_FORWARD];
            if ($url) {
                $url = str_replace(array('&oauth=wechat'), '', urldecode($url));
                isetcookie(COOKIE_FORWARD, '', -1);
            } else {
                $url = web_url('home/index');
            }
        }

        return $url;
    }

    public function wechat_client_oauth_url($scope = '')
    {

        global $_W;

        $scope        = empty($scope) ? 'snsapi_base' : $scope;
        $state        = 'holdskill-' . $_W['session_id'];
        $redirect_uri = urlencode($this->redirect_url);
        if (!empty($this->wechat_client_account['host'])) {
            $code_url = $this->wechat_client_account['host'] . "?appid={$this->wechat_client_account['appid']}&scope={$scope}&state={$state}&redirect_uri={$redirect_uri}";
        } else {
            $code_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->wechat_client_account['appid']}&redirect_uri={$redirect_uri}&response_type=code&scope={$scope}&state={$state}#wechat_redirect";
        }
        return $code_url;
    }

    public function wechat_client_oauth($scope = '', $code, $callback)
    {
        global $_W;

        if (empty($code)) {
            $this->forward($callback);
            $this->redirect_url = $_W['siteroot'] . "api.php?oauth=wechat&callback=" . $callback;
            header('Location: ' . $this->wechat_client_oauth_url($scope));
            exit();
        }

        load()->func('communication');
        $url      = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->wechat_client_account['appid']}&secret={$this->wechat_client_account['appsecret']}&code={$code}&grant_type=authorization_code";
        $response = ihttp_get($url);
        if ($this->is_error($response)) {
            var_dump($response);
        }

        $oauth_token = @json_decode($response['content'], true);
        if ($this->is_error($oauth_token)) {
            return $this->error($oauth_token);
        }

        $record           = array();
        $record['token']  = $oauth_token['access_token'];
        $record['expire'] = TIMESTAMP + $oauth_token['expires_in'] - 200;
        cache_write('wechat_client_access_token', $record, $record['expire']);

        $unionid           = $oauth_token['unionid'];
        $wechat_openid     = $oauth_token['openid'];
        $record['openid']  = $wechat_openid;
        $record['unionid'] = $unionid;

        $wechat_fan_data                                  = array();
        $wechat_fan_data['wechat_client_openid']          = $wechat_openid;
        $wechat_fan_data['wechat_client_last_visit_time'] = TIMESTAMP;
        $wechat_fan_data['wechat_client_access_token']    = iserializer($record);
        $wechat_fan_data['unionid']                       = $unionid;
        $wechat_fan_data['origin']                        = 'wechat_client';

        load()->model('mc');
        $member = pdo_fetch_one('mc_member', array('client_openid' => $wechat_openid));
        if (empty($member)) {
            $userinfo                = $this->SnsUserInfo($oauth_token['access_token'], $wechat_openid);
            $member                  = array();
            $member['nickname']      = $userinfo['nickname'];
            $member['avatar']        = $userinfo['headimgurl'];
            $member['unionid']       = $unionid;
            $member['client_openid'] = $wechat_openid;
            $member['createtime']    = TIMESTAMP;
            pdo_insert('mc_member', $member);
            $member['uid'] = pdo_insertid();
            pdo_insert('mc_member_profile', array('uid' => $member['uid']));

        } else {
            $userinfo                        = $this->SnsUserInfo($oauth_token['access_token'], $wechat_openid);
            $updateUserInfo                  = array();
            $updateUserInfo['nickname']      = $userinfo['nickname'];
            $updateUserInfo['avatar']        = $userinfo['headimgurl'];
            $updateUserInfo['client_openid'] = $wechat_openid;
            mc_update($member['uid'], $updateUserInfo);
        }

        isetcookie(COOKIE_UID, $member['uid'], 3600 * 10);
        isetcookie(COOKIE_UNIONID, $unionid, 3600 * 10);
        isetcookie(COOKIE_CLIENT_OPENID, $wechat_openid, 3600 * 10);

        require_once IA_ROOT . '/app/common/accesstoken.inc.php';

        $appkey = app_create_accesstoken($member['uid']);
        header('Location: ' . $this->forward() . '?#/account/login/?appkey=' . $appkey);

        exit;
    }

    public function wechat_app_oauth($code)
    {
        global $_W, $_GPC;

        if (empty($code)) {
            exit();
        }

        load()->model('mc');

        $url      = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->wechat_app_account['appid']}&secret={$this->wechat_app_account['appsecret']}&code={$code}&grant_type=authorization_code";
        $response = ihttp_get($url);
        if (is_error($response)) {
            return $response;
        }

        $token = @json_decode($response['content'], true);

        if ($this->is_error($token)) {
            return $this->error($token);
        }
        $record           = array();
        $record['token']  = $token['access_token'];
        $record['expire'] = TIMESTAMP + $token['expires_in'] - 200;
        cache_write('wechat_app_access_token', $record, $record['expire']);

        $unionid           = $token['unionid'];
        $app_openid        = $token['openid'];
        $record['openid']  = $app_openid;
        $record['unionid'] = $unionid;

        $wechat_fan_data                     = array();
        $wechat_fan_data['app_openid']       = $web_openid;
        $wechat_fan_data['last_visit_time']  = TIMESTAMP;
        $wechat_fan_data['app_access_token'] = iserializer($record);
        $wechat_fan_data['unionid']          = $unionid;
        $wechat_fan_data['origin']           = 'wechat_app';

        load()->model('mc');
        $member = mc_unionid($unionid);

        if (empty($member)) {
            $userinfo             = $this->SnsUserInfo($token['access_token'], $app_openid);
            $member               = array();
            $member['nickname']   = $userinfo['nickname'];
            $member['avatar']     = $userinfo['headimgurl'];
            $member['unionid']    = $unionid;
            $member['app_openid'] = $app_openid;
            $member['createtime'] = TIMESTAMP;
            pdo_insert('mc_member', $member);
            $member['uid'] = pdo_insertid();
            pdo_insert('mc_member_profile', array('uid' => $member['uid']));
        } else {
            $userinfo                     = $this->SnsUserInfo($token['access_token'], $app_openid);
            $updateUserInfo               = array();
            $updateUserInfo['nickname']   = $userinfo['nickname'];
            $updateUserInfo['avatar']     = $userinfo['headimgurl'];
            $updateUserInfo['app_openid'] = $app_openid;
            mc_update($member['uid'], $updateUserInfo);
        }
        isetcookie(COOKIE_UNIONID, $token['unionid'], 3600 * 10);
        return $member;

    }

    public function getWechatAppAccessToken($code)
    {

        $cache = cache_read('wechat_client_access_token');
        if ($cache && $cache['token'] && $cache['expire'] > TIMESTAMP) {
            $this->wechat_app_account['wechat_client_access_token'] = $cache;
            return $cache['token'];
        }

        $url     = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->wechat_app_account['appid']}&secret={$this->wechat_app_account['appsecret']}&code={$code}&grant_type=authorization_code";
        $content = ihttp_get($url);
        if (is_error($content)) {
            $resp = array(
                'data'    => null,
                'message' => '获取微信授权失败！错误详情: ' . $content['message'],
                'status'  => 0,
            );
            exit(json_encode($resp));
        }
        $token = json_decode($content['content'], true);
        if (empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['expires_in'])) {
            $errorinfo = substr($content['meta'], strpos($content['meta'], '{'));
            $errorinfo = json_decode($errorinfo, true);
            $resp      = array(
                'data'    => null,
                'message' => '获取微信授权失败, 请稍后重试！ 返回原始数据为: 错误代码-' . $errorinfo['errcode'] . '，错误信息-' . $errorinfo['errmsg'],
                'status'  => 0,
            );
            exit(json_encode($resp));
        }

        $record           = array();
        $record['token']  = $token['access_token'];
        $record['expire'] = TIMESTAMP + $token['expires_in'] - 200;
        cache_write('wechat_app_access_token', $record, $record['expire']);
        return $record['token'];
    }

    public function SnsUserInfo($token, $openid)
    {
        $url      = "https://api.weixin.qq.com/sns/userinfo?access_token={$token}&openid={$openid}&lang=zh_CN";
        $response = ihttp_get($url);
        if (is_error($response)) {
            return error(-1, "访问微信接口失败, 错误: {$response['message']}");
        }
        $result = @json_decode($response['content'], true);
        if (empty($result)) {
            return error(-1, "接口调用失败, 元数据: {$response['meta']}");
        } elseif (!empty($result['errcode'])) {
            return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
        }
        return $result;
    }

    public function error_code($code)
    {
        $errors = array(
            '-1'    => '系统繁忙',
            '0'     => '请求成功',
            '40001' => '获取access_token时AppSecret错误，或者access_token无效',
            '40002' => '不合法的凭证类型',
            '40003' => '不合法的OpenID',
            '40004' => '不合法的媒体文件类型',
            '40005' => '不合法的文件类型',
            '40006' => '不合法的文件大小',
            '40007' => '不合法的媒体文件id',
            '40008' => '不合法的消息类型',
            '40009' => '不合法的图片文件大小',
            '40010' => '不合法的语音文件大小',
            '40011' => '不合法的视频文件大小',
            '40012' => '不合法的缩略图文件大小',
            '40013' => '不合法的APPID',
            '40014' => '不合法的access_token',
            '40015' => '不合法的菜单类型',
            '40016' => '不合法的按钮个数',
            '40017' => '不合法的按钮个数',
            '40018' => '不合法的按钮名字长度',
            '40019' => '不合法的按钮KEY长度',
            '40020' => '不合法的按钮URL长度',
            '40021' => '不合法的菜单版本号',
            '40022' => '不合法的子菜单级数',
            '40023' => '不合法的子菜单按钮个数',
            '40024' => '不合法的子菜单按钮类型',
            '40025' => '不合法的子菜单按钮名字长度',
            '40026' => '不合法的子菜单按钮KEY长度',
            '40027' => '不合法的子菜单按钮URL长度',
            '40028' => '不合法的自定义菜单使用用户',
            '40029' => '不合法的oauth_code',
            '40030' => '不合法的refresh_token',
            '40031' => '不合法的openid列表',
            '40032' => '不合法的openid列表长度',
            '40033' => '不合法的请求字符，不能包含\uxxxx格式的字符',
            '40035' => '不合法的参数',
            '40038' => '不合法的请求格式',
            '40039' => '不合法的URL长度',
            '40050' => '不合法的分组id',
            '40051' => '分组名字不合法',
            '41001' => '缺少access_token参数',
            '41002' => '缺少appid参数',
            '41003' => '缺少refresh_token参数',
            '41004' => '缺少secret参数',
            '41005' => '缺少多媒体文件数据',
            '41006' => '缺少media_id参数',
            '41007' => '缺少子菜单数据',
            '41008' => '缺少oauth code',
            '41009' => '缺少openid',
            '42001' => 'access_token超时',
            '42002' => 'refresh_token超时',
            '42003' => 'oauth_code超时',
            '43001' => '需要GET请求',
            '43002' => '需要POST请求',
            '43003' => '需要HTTPS请求',
            '43004' => '需要接收者关注',
            '43005' => '需要好友关系',
            '44001' => '多媒体文件为空',
            '44002' => 'POST的数据包为空',
            '44003' => '图文消息内容为空',
            '44004' => '文本消息内容为空',
            '45001' => '多媒体文件大小超过限制',
            '45002' => '消息内容超过限制',
            '45003' => '标题字段超过限制',
            '45004' => '描述字段超过限制',
            '45005' => '链接字段超过限制',
            '45006' => '图片链接字段超过限制',
            '45007' => '语音播放时间超过限制',
            '45008' => '图文消息超过限制',
            '45009' => '接口调用超过限制',
            '45010' => '创建菜单个数超过限制',
            '45015' => '回复时间超过限制',
            '45016' => '系统分组，不允许修改',
            '45017' => '分组名字过长',
            '45018' => '分组数量超过上限',
            '46001' => '不存在媒体数据',
            '46002' => '不存在的菜单版本',
            '46003' => '不存在的菜单数据',
            '46004' => '不存在的用户',
            '47001' => '解析JSON/XML内容错误',
            '48001' => 'api功能未授权',
            '50001' => '用户未授权该api',
            '40070' => '基本信息baseinfo中填写的库存信息SKU不合法。',
            '41011' => '必填字段不完整或不合法，参考相应接口。',
            '40056' => '无效code，请确认code长度在20个字符以内，且处于非异常状态（转赠、删除）。',
            '43009' => '无自定义SN权限，请参考开发者必读中的流程开通权限。',
            '43010' => '无储值权限,请参考开发者必读中的流程开通权限。',
            '43011' => '无积分权限,请参考开发者必读中的流程开通权限。',
            '40078' => '无效卡券，未通过审核，已被置为失效。',
            '40079' => '基本信息base_info中填写的date_info不合法或核销卡券未到生效时间。',
            '45021' => '文本字段超过长度限制，请参考相应字段说明。',
            '40080' => '卡券扩展信息cardext不合法。',
            '40097' => '基本信息base_info中填写的url_name_type或promotion_url_name_type不合法。',
            '49004' => '签名错误。',
            '43012' => '无自定义cell跳转外链权限，请参考开发者必读中的申请流程开通权限。',
            '40099' => '该code已被核销。',
        );
        $code = strval($code);
        if ($code == '40001' || $code == '42001') {
            $cachekey = "access_token";
            cache_delete($cachekey);
            return '微信公众平台授权异常, 系统已修复这个错误, 请刷新页面重试.';
        }
        if ($errors[$code]) {
            return $errors[$code];
        } else {
            return '未知错误';
        }
    }

    public function getAccessToken()
    {

        $cache = cache_read('client_account_access_token');
        if ($cache && $cache['token'] && $cache['expire'] > TIMESTAMP) {
            $this->wechat_client_account['access_token'] = $cache;
            return $cache['token'];
        }

        $url     = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->wechat_client_account['appid']}&secret={$this->wechat_client_account['appsecret']}";
        $content = ihttp_get($url);
        if (is_error($content)) {
            message('获取微信公众号授权失败！错误详情: ' . $content['message']);
        }
        $token = json_decode($content['content'], true);
        if (empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['expires_in'])) {
            $errorinfo = substr($content['meta'], strpos($content['meta'], '{'));
            $errorinfo = json_decode($errorinfo, true);
            message('获取微信公众号授权失败, 请稍后重试！ 公众平台返回原始数据为: 错误代码-' . $errorinfo['errcode'] . '，错误信息-' . $errorinfo['errmsg']);
        }

        $record           = array();
        $record['token']  = $token['access_token'];
        $record['expire'] = TIMESTAMP + $token['expires_in'] - 200;
        cache_write('client_account_access_token', $record, $record['expire']);

        return $record['token'];
    }

    public function getJsApiTicket()
    {
        $cache = cache_load('jsapi_ticket');
        if ($cache && $cache['ticket'] && $cache['expire'] > TIMESTAMP) {
            return $cache['ticket'];
        }
        $access_token = $this->getAccessToken();
        if (is_error($access_token)) {
            return $access_token;
        }
        $url     = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi";
        $content = ihttp_get($url);
        if (is_error($content)) {
            return error(-1, '调用接口获取微信公众号 jsapi_ticket 失败, 错误信息: ' . $content['message']);
        }
        $result = json_decode($content['content'], true);
        if (empty($result) || intval(($result['errcode'])) != 0 || $result['errmsg'] != 'ok') {
            return error(-1, '获取微信公众号 jsapi_ticket 结果错误, 错误信息: ' . $result['errmsg']);
        }
        $record                                      = array();
        $record['ticket']                            = $result['ticket'];
        $record['expire']                            = TIMESTAMP + $result['expires_in'] - 200;
        $this->wechat_client_account['jsapi_ticket'] = $record;
        cache_write('jsapi_ticket', $record);

        return $record['ticket'];
    }

    public function getJssdkConfig($url)
    {
        global $_W;
        $jsapiTicket = $this->getJsApiTicket();
        if (is_error($jsapiTicket)) {
            return $jsapiTicket;
        }
        $nonceStr  = random(16);
        $timestamp = TIMESTAMP;
        $url       = empty($url) ? $_W['siteurl'] : $url;
        $string1   = "jsapi_ticket={$jsapiTicket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
        $signature = sha1($string1);
        $config    = array(
            "appId"     => $this->wechat_client_account['appid'],
            "nonceStr"  => $nonceStr,
            "timestamp" => "$timestamp",
            "signature" => $signature,
        );
        if (DEVELOPMENT) {
            $config['url']     = $url;
            $config['string1'] = $string1;
            $config['name']    = $this->wechat_client_account['name'];
        }
        return $config;
    }

    public function register_jssdk($debug = false)
    {
        global $_W;

        if (defined('JSSDK')) {
            return;
        }
        define('JSSDK', true);

        $sysinfo = array(
            'siteroot'  => $_W['siteroot'],
            'siteurl'   => $_W['siteurl'],
            'attachurl' => $_W['attachurl'],
            'cookie'    => array('pre' => $_W['config']['cookie']['pre']),
        );
        if (!empty($_W['openid'])) {
            $sysinfo['openid'] = $_W['openid'];
        }

        $sysinfo     = json_encode($sysinfo);
        $jssdkconfig = json_encode($this->wechat_client_account['jssdkconfig']);
        $debug       = $debug ? 'true' : 'false';

        $script = <<<EOF
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript">
    window.sysinfo = window.sysinfo || $sysinfo || {};

    // jssdk config 对象
    jssdkconfig = $jssdkconfig || {};

    // 是否启用调试
    jssdkconfig.debug = $debug;

    jssdkconfig.jsApiList = [
        'checkJsApi',
        'onMenuShareTimeline',
        'onMenuShareAppMessage',
        'onMenuShareQQ',
        'onMenuShareWeibo',
        'hideMenuItems',
        'showMenuItems',
        'hideAllNonBaseMenuItem',
        'showAllNonBaseMenuItem',
        'translateVoice',
        'startRecord',
        'stopRecord',
        'onRecordEnd',
        'playVoice',
        'pauseVoice',
        'stopVoice',
        'uploadVoice',
        'downloadVoice',
        'chooseImage',
        'previewImage',
        'uploadImage',
        'downloadImage',
        'getNetworkType',
        'openLocation',
        'getLocation',
        'hideOptionMenu',
        'showOptionMenu',
        'closeWindow',
        'scanQRCode',
        'chooseWXPay',
        'openProductSpecificView',
        'addCard',
        'chooseCard',
        'openCard'
    ];
    wx.config(jssdkconfig);
</script>
EOF;
        echo $script;
    }

    public function sendTemplateMessage($touser, $template_id, $postdata, $url = '', $topcolor = '#FF683F')
    {
        if (empty($touser)) {
            return error(2, '微信模板消息发送失败:粉丝openid不能为空');
        }
        if (empty($template_id)) {
            return error(2, '微信模板消息发送失败:模板标示不能为空');
        }
        if (empty($postdata) || !is_array($postdata)) {
            return error(2, '微信模板消息发送失败:模板消息内容不完善');
        }
        $token = $this->getAccessToken();
        if (is_error($token)) {
            return $token;
        }

        $data                = array();
        $data['touser']      = $touser;
        $data['template_id'] = trim($template_id);
        $data['url']         = trim($url);
        $data['topcolor']    = trim($topcolor);
        $data['data']        = $postdata;
        $data                = json_encode($data);
        $post_url            = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$token}";
        $response            = ihttp_request($post_url, $data);
        if (is_error($response)) {
            return error(2, "访问公众平台接口失败, 错误: {$response['message']}");
        }
        $result = @json_decode($response['content'], true);
        if (empty($result)) {
            return error(2, "接口调用失败, 元数据: {$response['meta']}");
        } elseif (!empty($result['errcode'])) {
            return error(2, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},信息详情：{$this->error_code($result['errcode'])}");
        }
        return true;
    }

    public function uploadMedia($path, $type = 'image')
    {
        if (empty($path)) {
            return error(-1, '参数错误');
        }
        if (in_array(substr(ltrim($path, '/'), 0, 6), array('images', 'videos', 'audios'))) {
            $path = ATTACHMENT_ROOT . ltrim($path, '/');
        }
        $token = $this->getAccessToken();
        if (is_error($token)) {
            return $token;
        }
        $url  = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$token}&type={$type}";
        $data = array(
            'media' => '@' . $path,
        );
        return $this->requestApi($url, $data);
    }

    public function downloadMedia($media_id, $savefile = true)
    {
        $mediatypes = array('image', 'voice', 'thumb');
        $media_id   = is_array($media_id) ? $media_id['media_id'] : $media_id;
        if (empty($media_id)) {
            return error(-1, '微信下载媒体资源参数错误');
        }

        $token = $this->getAccessToken();
        if (is_error($token)) {
            return $token;
        }
        $url      = "https://api.weixin.qq.com/cgi-bin/media/get?access_token={$token}&media_id={$media_id}";
        $response = ihttp_get($url);
        if (empty($response['headers']['Content-disposition'])) {
            $response = json_decode($response['content'], true);
            if (!empty($response['video_url'])) {
                $response                                   = ihttp_get($response['video_url']);
                $response['headers']['Content-disposition'] = $response['headers']['Content-Disposition'];
            }
        }
        if ($savefile && !empty($response['headers']['Content-disposition']) && strexists($response['headers']['Content-disposition'], 'filename=')) {
            global $_W;
            preg_match('/filename=\"?([^"]*)/', $response['headers']['Content-disposition'], $match);
            $filename = date('Y/m/d/') . $match[1];
            $pathinfo = pathinfo($filename);
            if (in_array(strtolower($pathinfo['extension']), array('mp4'))) {
                $filename = 'videos/' . $filename;
                $type     = 'video';
            } elseif (in_array(strtolower($pathinfo['extension']), array('amr', 'mp3', 'wma', 'wmv'))) {
                $filename = 'audios/' . $filename;
                $type     = 'audio';
            } else {
                $filename = 'images/' . $filename;
                $type     = 'image';
            }
            load()->func('file');
            file_write($filename, $response['content']);
            if (!empty($_W['setting']['remote']['type'])) {
                if ($type == 'audio') {
                    $filename = file_remote_upload($filename, true, $type);
                    return $filename['message'];
                } else {
                    $filename = file_remote_upload($filename, true, $type);
                    return $filename['message'];
                }
            }
            return tomedia($filename);

        } else {
            return $response['content'];
        }
    }
}

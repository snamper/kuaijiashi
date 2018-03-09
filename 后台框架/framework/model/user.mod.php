<?php

defined('IN_IA') or exit('Access Denied');

require IA_ROOT . '/framework/library/alidayu/api_sdk/vendor/autoload.php';

use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\Config;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;

function user($uid, $isPwd = false)
{
    $user = pdo_fetch_one('user', array('uid' => $uid));
    if (!$isPwd) {
        unset($user['salt'], $user['password']);
    }
    if (!empty($user)) {
        $user['avatar']        = tomedia($user['avatar']);
        $user['role']          = iunserializer($user['role']);
        $user['joindateText']  = date("Y-m-d H:i", $user['joindate']);
        $user['lastvisitText'] = date("Y-m-d H:i", $user['lastvisit']);
    }
    return $user;
}

function user_by_username($username, $isPwd = false)
{
    $user = pdo_fetch_one('user', array('username' => $username));
    if (!$isPwd) {
        unset($user['salt'], $user['password']);
    }
    if (!empty($user)) {
        $user['avatar']        = tomedia($user['avatar']);
        $user['role']          = iunserializer($user['role']);
        $user['joindateText']  = date("Y-m-d H:i", $user['joindate']);
        $user['lastvisitText'] = date("Y-m-d H:i", $user['lastvisit']);
    }
    return $user;
}

function user_by_mobile($mobile, $isPwd = false)
{
    $user = pdo_fetch_one('user', array('mobile' => $mobile));
    if (!$isPwd) {
        unset($user['salt'], $user['password']);
    }
    if (!empty($user)) {
        $user['avatar']        = tomedia($user['avatar']);
        $user['role']          = iunserializer($user['role']);
        $user['joindateText']  = date("Y-m-d H:i", $user['joindate']);
        $user['lastvisitText'] = date("Y-m-d H:i", $user['lastvisit']);
    }
    return $user;
}

function user_login($username, $password)
{
    $user = user_by_username($username, 1);
    if ($user) {
        $pwd = pwd_hash($password, $user['salt']);
        if ($pwd == $user['password']) {
            $data              = array();
            $data['lastvisit'] = TIMESTAMP;
            $data['lastip']    = CLIENT_IP;
            pdo_update('user', $data, array('uid' => $user['uid']));

            $user['lastip']    = CLIENT_IP;
            $user['lastvisit'] = TIMESTAMP;
            unset($user['salt'], $user['password']);
            return $user;
        }
    }
    return array();
}

function user_register(array $user)
{
    if (empty($user) || empty($user['username']) || empty($user['password'])) {
        return error(1, '用户名或密码密码错误.');
    }
    $record = user_by_username($username);
    if ($record) {
        return error(2, '已存在当前用户名.');
    }

    unset($user['uid']);
    $user['salt']      = random(8);
    $user['password']  = pwd_hash($user['password'], $user['salt']);
    $user['joinip']    = CLIENT_IP;
    $user['joindate']  = TIMESTAMP;
    $user['lastip']    = CLIENT_IP;
    $user['lastvisit'] = TIMESTAMP;
    pdo_insert('user', $user);
    $user['uid'] = pdo_insertid();

    return $user;
}

function user_update(array $user)
{
    $uid = intval($user['uid']);
    unset($user['uid'], $user['username'], $user['salt']);

    $record = user($uid, 1);
    if (empty($record)) {
        return error(1, '操作用户保存失败: 不存在.');
    }
    if (!empty($user['password'])) {
        $user['password'] = pwd_hash($user['password'], $record['salt']);
    } else {
        unset($user['password']);
    }

    if (empty($user)) {
        return error(1, '操作用户保存失败.');
    }
    if (pdo_update('user', $user, array('uid' => $uid))) {
        return user($uid);
    } else {
        return error(1, '更新失败.');
    }
}

function user_fetch($uid, $fields = array())
{
    global $_W;
    if (empty($uid)) {
        return array();
    }
    if (is_array($uid)) {
        $result = pdo_fetch_many('user', array('uid' => $uid), $fields, 'uid');
    } else {
        $result = pdo_fetch_one('user', array('uid' => $uid), $fields);
    }
    return $result;
}

/**验证码发送start**/
function user_sms_send($mobile, $templateId, $params, $outId = null)
{
    global $_W;

    //加载区域结点配置
    Config::load();

    $msg_setting = $_W['setting']['sms'];
    if (empty($msg_setting)) {
        return error(2, '未配置短信设置');
    }
    //此处需要替换成自己的AK信息
    $accessKeyId     = $msg_setting['AccessKeyId']; //参考本文档步骤2
    $accessKeySecret = $msg_setting['AccessKeySecret']; //参考本文档步骤2
    //短信API产品名（短信产品名固定，无需修改）
    $product = "Dysmsapi";
    //短信API产品域名（接口地址固定，无需修改）
    $domain = "dysmsapi.aliyuncs.com";
    //暂时不支持多Region（目前仅支持cn-hangzhou请勿修改）
    $region = "cn-hangzhou";
    // 服务结点
    $endPointName = "cn-hangzhou";
    //初始化访问的acsCleint
    $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
    DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);
    $acsClient = new DefaultAcsClient($profile);
    $request   = new SendSmsRequest();
    //必填-短信接收号码。支持以逗号分隔的形式进行批量调用，批量上限为1000个手机号码,批量调用相对于单条调用及时性稍有延迟,验证码类型的短信推荐使用单条调用的方式
    $request->setPhoneNumbers($mobile);
    //必填-短信签名
    $request->setSignName($msg_setting['signature']);
    //必填-短信模板Code
    $request->setTemplateCode($templateId);
    //选填-假如模板中存在变量需要替换则为必填(JSON格式),友情提示:如果JSON中需要带换行符,请参照标准的JSON协议对换行符的要求,比如短信内容中包含\r\n的情况在JSON中需要表示成\\r\\n,否则会导致JSON在服务端解析失败
    $request->setTemplateParam(json_encode($params));
    //选填-发送短信流水号
    if ($outId) {
        $request->setOutId($outId);
    }
    //发起访问请求
    $acsResponse = $acsClient->getAcsResponse($request);

    $error = array(
        'OK'                              => '请求成功',
        'isp.RAM_PERMISSION_DENY'         => 'RAM权限DENY',
        'isv.OUT_OF_SERVICE'              => '业务停机',
        'isv.PRODUCT_UN_SUBSCRIPT'        => '未开通云通信产品的阿里云客户',
        'isv.PRODUCT_UNSUBSCRIBE'         => '产品未开通',
        'isv.ACCOUNT_NOT_EXISTS'          => '账户不存在',
        'isv.ACCOUNT_ABNORMAL'            => '账户异常',
        'isv.SMS_TEMPLATE_ILLEGAL'        => '短信模板不合法',
        'isv.SMS_SIGNATURE_ILLEGAL'       => '短信签名不合法',
        'isv.INVALID_PARAMETERS'          => '参数异常',
        'isp.SYSTEM_ERROR'                => '系统错误',
        'isv.MOBILE_NUMBER_ILLEGAL'       => '非法手机号',
        'isv.MOBILE_COUNT_OVER_LIMIT'     => '手机号码数量超过限制',
        'isv.TEMPLATE_MISSING_PARAMETERS' => '模板缺少变量',
        'isv.BUSINESS_LIMIT_CONTROL'      => '业务限流',
        'isv.INVALID_JSON_PARAM'          => 'JSON参数不合法，只接受字符串值',
        'isv.BLACK_KEY_CONTROL_LIMIT'     => '黑名单管控',
        'isv.PARAM_LENGTH_LIMIT'          => '参数超出长度限制',
        'isv.PARAM_NOT_SUPPORT_URL'       => '不支持url',
        'isv.AMOUNT_NOT_ENOUGH'           => '账户余额不足',
    );

    $result = object2array($acsResponse);

    if ($result['code']) {
        return error(1, $result['sub_msg']);
    }
    return true;

}

function captcha_send($mobile, $type = '1', $templateid = 'SMS_93930009')
{
    global $_W;
    if (empty($mobile)) {
        return error(1, '请填写手机号');
    }
    $exist = user_by_mobile($mobile);
    if ($exist && $type == '1') {
        return error(1, '当前手机已注册.');
    }
    pdo_query('DELETE FROM ' . tablename('captcha') . ' WHERE `createtime`<' . (TIMESTAMP - 3600));

    $captcha = pdo_fetch_one('captcha', array('receiver' => $mobile));

    if ($captcha['createtime'] >= TIMESTAMP - 60) {
        return error(1, '请稍后再重新获取');
    }

    $data = array(
        'receiver'   => $mobile,
        'captcha'    => random(6, true),
        'createtime' => TIMESTAMP,
    );
    $captcha = pdo_fetch_one('captcha', array('receiver' => $mobile));
    if (empty($captcha)) {
        $data['num'] = 1;
        pdo_insert('captcha', $data);
    } else {
        if (date('Ymd', $captcha['createtime']) == date('Ymd', TIMESTAMP) && $captcha['num'] > 5) {
            return error(1, '您已经超过今日发送验证码的最大次数');
        } else {
            $data['num'] = $captcha['num'] + 1;
            pdo_update('captcha', $data, array('receiver' => $mobile));
        }
    }
    $code    = $data['captcha'];
    $content = array('code' => $code);

    $result = user_sms_send($mobile, $templateid, $content);
    if (is_error($result)) {
        return error(1, $result['message']);
    }
    return error(0, '发送成功');
}

function captcha_check($mobile, $captcha)
{
    if (empty($mobile)) {
        return error(1, '手机号为空');
    }
    if (!preg_match(REGULAR_MOBILE, $mobile)) {
        return error(1, '手机号格式错误');
    }
    if (empty($captcha)) {
        return error(1, '验证码为空');
    }
    if (istrlen($captcha) < 6) {
        return error(1, '验证码长度小于六位');
    }
    $receiver = pdo_fetch_one('captcha', array('receiver' => $mobile));
    if (empty($receiver)) {
        return error(1, '请重新发送验证码');
    }
    if (TIMESTAMP - $receiver['createtime'] > 60 * 20) {
        return error(1, '验证码过期');
    }
    if ($captcha != $receiver['captcha']) {
        return error(1, '验证码错误');
    }
    return true;
}

/**验证码发送end**/

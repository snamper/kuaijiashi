<?php

defined('IN_IA') or exit('Access Denied');

require IA_ROOT . '/framework/library/alidayu/api_sdk/vendor/autoload.php';

use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\Config;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;

function mc_checklogin()
{
    global $_W, $controller, $action;

    if ($_W['member']) {
        return;
    }

    $acls = array(
        'address' => array(),
        'article' => array('article'),
        'bbs'     => array('bbs'),
        'coupon'  => array(),
        'user'    => array('index', 'cash', 'message', 'mypayment', 'mypayment_other', 'mywallet', 'pay', 'profile', 'recharge', 'scan_pay'),
        'order'   => array(),
        'utility' => array('file'),
        'form'    => array(),
    );

    if (isset($acls[$controller])) {
        $actions = $acls[$controller];
        if (empty($actions) || in_array($action, $actions)) {
            if ($_W['isajax']) {
                message(error(2, '请登陆系统.'), '', 'ajax');
            } else {
                if (!$_W['ispost']) {
                    isetcookie(COOKIE_FORWARD, urlencode($_W['siteurl']));
                }
                header('Location: ' . url('account/login'));
                exit;
            }
        }
    }
}

function mc_login_validate($mobile, $password)
{
    if (empty($mobile)) {
        return error(1, '手机号不能为空');
    }
    if (empty($password)) {
        return error(1, '密码不能为空');
    }
    if (!is_mobile($mobile)) {
        return error(1, '手机号格式不正确');
    }
    if (!is_password($password)) {
        return error(1, '密码格式不正确');
    }
    return true;
}

function mc_login($mobile, $password)
{
    global $_W;

    $result = mc_login_validate($mobile, $password);
    if (is_error($result)) {
        return $result;
    }

    $member = mc_fetch_by_mobile($mobile);
    if (empty($member)) {
        return error(1, '当前手机号尚未注册');
    }
    $pwd = pwd_hash($password, $member['salt']);
    if ($member['password'] != $pwd) {
        return error(1, '密码错误');
    }
    $uid     = $member['uid'];
    $profile = mc_profile($uid);
    if (empty($profile)) {
        pdo_insert('mc_member_profile', array('uid' => $uid));
    }

    $member = mc_member($member['uid']);

    return $member;
}

function mc_change_password($mobile, $password)
{
    $result = mc_login_validate($mobile, $password);
    if (is_error($result)) {
        return $result;
    }

    $member = pdo_fetch_one('mc_member', array('mobile' => $mobile));
    if (empty($member)) {
        return error(1, '手机号不存在');
    }

    $data             = array();
    $data['salt']     = random(8);
    $data['password'] = pwd_hash($password, $data['salt']);
    pdo_update('mc_member', $data, array('uid' => $member['uid']));
    load()->model('cache');
    cache_clean();
    return $member;
}

function mc_register($mobile, $password)
{
    global $_W;

    $result = mc_login_validate($mobile, $password);
    if (is_error($result)) {
        return $result;
    }

    $exist = mc_fetch_by_mobile($mobile);
    if ($exist) {
        return error(1, '当前手机已注册.');
    }

    $member               = array();
    $member['mobile']     = $mobile;
    $member['salt']       = random(8);
    $member['password']   = pwd_hash($password, $member['salt']);
    $member['createtime'] = TIMESTAMP;
    pdo_insert('mc_member', $member);
    $member['uid'] = pdo_insertid();
    pdo_insert('mc_member_profile', array('uid' => $member['uid']));

    $member = mc_member($member['uid']);

    return $member;
}

function mc_update_with_pro_member($member, $pro_member)
{
    $uid = $member['uid'];

    $member_update = array();
    if (empty($member['realname']) && !empty($pro_member['realname'])) {
        $member_update['realname'] = $pro_member['realname'];
    }
    if (empty($member['nickname']) && !empty($pro_member['nickname'])) {
        $member_update['nickname'] = $pro_member['nickname'];
    }
    if (empty($member['avatar']) && !empty($pro_member['avatar'])) {
        $member_update['avatar'] = $pro_member['avatar'];
    }
    if ($member_update) {
        pdo_update('mc_member', $member_update, array('uid' => $uid));
        $member = array_merge($member, $member_update);
    }

    $profile        = mc_profile($uid);
    $profile_exist  = !empty($profile);
    $profile_update = array();
    $profile_fields = mc_profile_fields();
    foreach ($profile_fields as $field => $cnname) {
        if (in_array($field, array('reside', 'birth'))) {
            continue;
        }
        if (empty($profile[$field]) && $pro_member[$field]) {
            $profile_update[$field] = $pro_member[$field];
        }
    }

    if ($profile_update) {
        pdo_update('mc_member_profile', $profile_update, array('uid' => $uid));
    }

    return $member;
}

function mc_update_with_faninfo($member)
{
    global $_W;

    $member_update = array();
    if ($_W['openid']) {
        pdo_update('wechat_fan', array('uid' => $member['uid']), array('openid' => $_W['openid']));
        $wechat_fan = wechat_fan($_W['openid']);
        if (empty($member['nickname']) && $wechat_fan['nickname']) {
            $member_update['nickname'] = $wechat_fan['nickname'];
        }
        if (empty($member['avatar']) && $wechat_fan['avatar']) {
            $member_update['avatar'] = $wechat_fan['avatar'];
        }
    }

    if ($member_update) {
        pdo_update('mc_member', $member_update, array('uid' => $member['uid']));
        $member = array_merge($member, $member_update);
    }

    return $member;
}

function mc_update($uid, $fields, $mobile = false)
{
    global $_W;
    if (!empty($fields['birth'])) {
        $fields['birthyear']  = $fields['birth']['year'];
        $fields['birthmonth'] = $fields['birth']['month'];
        $fields['birthday']   = $fields['birth']['day'];
    }
    if (!empty($fields['reside'])) {
        $fields['resideprovince'] = $fields['reside']['province'];
        $fields['residecity']     = $fields['reside']['city'];
        $fields['residedist']     = $fields['reside']['district'];
    }

    unset(
        $fields['reside'], $fields['birth'],
        $fields['createtime'],
        $fields['password'], $fields['salt'],
        $fields['credit1'], $fields['credit2'], $fields['credit3'], $fields['credit4'], $fields['credit5']
    );

    if (!$mobile) {
        unset($fields['mobile']);
    }

    $member  = array();
    $profile = array();

    $member_fields  = array_keys(mc_fields());
    $profile_fields = array_keys(mc_profile_fields());
    foreach ($fields as $field => $value) {
        if (in_array($field, $member_fields)) {
            $member[$field] = $value;
        } elseif (in_array($field, $profile_fields)) {
            $profile[$field] = $value;
        }
    }
    if ($member) {
        pdo_update('mc_member', $member, array('uid' => $uid));
    }
    if ($profile) {
        $mc_profile = mc_profile($uid);
        if ($mc_profile) {
            pdo_update('mc_member_profile', $profile, array('uid' => $uid));
        } else {
            $profile['uid'] = $uid;
            pdo_insert('mc_member_profile', $profile);
        }
    }
    load()->model('cache');
    cache_clean();
    return true;
}

function mc_fetch_by_mobile($mobile)
{
    return pdo_fetch_one('mc_member', array('mobile' => $mobile));
}

function mc_fetch($uid, $fields = array())
{
    global $_W;
    if (empty($uid)) {
        return array();
    }
    if (is_array($uid)) {
        $result = pdo_fetch_many('mc_member', array('uid' => $uid), $fields, 'uid');
    } else {
        $result = pdo_fetch_one('mc_member', array('uid' => $uid), $fields);
    }
    unset($result['password']);
    unset($result['salt']);
    return $result;
}

function mc_profile($uid, $fields = array())
{
    global $_W;
    if (empty($uid)) {
        return array();
    }
    if (empty($fields) || !is_array($fields)) {
        $fields = array();
    } else {
        if (in_array('birth', $fields)) {
            $fields[] = 'birthyear';
            $fields[] = 'birthmonth';
            $fields[] = 'birthday';
        }
        if (in_array('reside', $fields)) {
            $fields[] = 'resideprovince';
            $fields[] = 'residecity';
            $fields[] = 'residedist';
        }

        $profile_fields = array_keys(mc_profile_fields());
        $fields         = array_intersect($profile_fields, $fields);
        $fields[]       = 'uid';
    }
    if (is_array($uid)) {
        $result = pdo_fetch_many('mc_member_profile', array('uid' => $uid), $fields, 'uid');
    } else {
        $result = pdo_fetch_one('mc_member_profile', array('uid' => $uid), $fields);
    }
    return $result;
}

function mc_credit_increase($uid, $credittype, $value, $operator = 0, $remark = '')
{
    $value = floatval($value);
    if (empty($value)) {
        return error('-1', "积分变动不可以为 0");
    }

    $credit_types = mc_credit_types();
    if (!in_array($credittype, $credit_types)) {
        return error('-1', "积分类型 {$credittype} 不存在");
    }
    $creditname = credit_name($credittype);

    $credit = pdo_fetch_value('mc_member', $credittype, array('uid' => $uid));
    $credit = floatval($credit);

    if ($credit >= 0 && ($credit + $value >= 0)) {
        $data = array(
            $credittype => currency_format($credit + $value),
        );
        pdo_update('mc_member', $data, array('uid' => $uid));
    } else {
        return error('-1', "{$creditname}不足,无法操作");
    }
    load()->model('cache');
    cache_clean();
    return true;
}

function mc_credit_fetch($uid, $types = array())
{
    $all_types = mc_credit_types();
    if ($types) {
        $types = !is_array($types) ? array($types) : $types;
        $types = array_intersect($types, $all_types);
        $types = array_unique($types);
    }
    if (empty($types)) {
        $types = $all_types;
    }

    $credits = pdo_fetch_one('mc_member', array('uid' => $uid), $types);
    foreach ($credits as &$credit) {
        $credit = floatval($credit);
    }
    unset($credit);

    return $credits;
}

function mc_credit_types()
{
    return array('credit1', 'credit2', 'credit3', 'credit4', 'credit5');
}

function mc_fields()
{
    global $_W;
    $result = array(
        'credit1'    => $_W['setting']['creditnames']['credit1']['title'],
        'credit2'    => $_W['setting']['creditnames']['credit2']['title'],
        'credit3'    => $_W['setting']['creditnames']['credit3']['title'],
        'credit4'    => $_W['setting']['creditnames']['credit4']['title'],
        'credit5'    => $_W['setting']['creditnames']['credit5']['title'],
        'createtime' => '创建时间',
    );

    $mc_member_fields = pdo_fetch_many('mc_member_field', array('type' => '1'), array(), 'id', 'ORDER BY `displayorder` DESC');
    foreach ($mc_member_fields as $row) {
        $result[$row['field']] = $row['title'];
    }
    return $result;
}

function mc_profile_fields()
{
    $mc_profile_fields = pdo_fetch_many('mc_member_field', array('type' => '2'), array(), 'id', 'ORDER BY `displayorder` DESC');
    $result            = array();
    foreach ($mc_profile_fields as $row) {
        $result[$row['field']] = $row['title'];
    }
    return $result;
}

function mc_member($uid)
{

    $member = mc_fetch($uid);
    if (empty($member)) {
        return array();
    }
    $member['profile']         = mc_profile($uid);
    $member['addresses']       = mc_address_all($uid);
    $member['default_address'] = array();
    foreach ($member['addresses'] as $address_id => $item) {
        if ($item['isdefault'] == YES) {
            $member['default_address'] = $item;
            break;
        }
    }
    return $member;
}

function mc_unionid($unionid)
{
    $record = pdo_fetch_one('mc_member', array('unionid' => $unionid));
    if (!empty($record['uid'])) {
        $member = mc_member($record['uid']);
        if (empty($member)) {
            return array();
        }
        $member['profile']         = mc_profile($uid);
        $member['addresses']       = mc_address_all($uid);
        $member['default_address'] = array();
        foreach ($member['addresses'] as $address_id => $item) {
            if ($item['isdefault'] == YES) {
                $member['default_address'] = $item;
                break;
            }
        }
        return $member;
    }
}

function mc_address_all($uid)
{
    $addresses = pdo_fetch_many('mc_member_address', array('uid' => $uid), array(), 'id');
    return $addresses;
}

function credit_name($credit_field)
{
    global $_W;
    return $_W['setting']['creditnames'][$credit_field]['title'];
}

function credit_currency_field()
{
    global $_W;
    return $_W['setting']['creditbehaviors']['currency'];
}

function cloud_sms_send($mobile, $templateId, $params, $outId = null)
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

function verifycode_send($mobile, $type = '1', $templateid = 'SMS_125120007')
{
    global $_W;
    if (empty($mobile)) {
        return error(1, '请填写手机号');
    }
    $exist = mc_fetch_by_mobile($mobile);
    if ($exist && $type == '1') {
        return error(1, '当前手机已注册.');
    }
    pdo_query('DELETE FROM ' . tablename('verifycode') . ' WHERE `createtime`<' . (TIMESTAMP - 3600));

    $verifycode = pdo_fetch_one('verifycode', array('receiver' => $mobile));

    if ($verifycode['createtime'] >= TIMESTAMP - 60) {
        return error(1, '请稍后再重新获取');
    }

    $data = array(
        'receiver'   => $mobile,
        'verifycode' => random(6, true),
        'createtime' => TIMESTAMP,
    );
    $verifycode = pdo_fetch_one('verifycode', array('receiver' => $mobile));
    if (empty($verifycode)) {
        $data['num'] = 1;
        pdo_insert('verifycode', $data);
    } else {
        if (date('Ymd', $verifycode['createtime']) == date('Ymd', TIMESTAMP) && $verifycode['num'] > 5) {
            return error(1, '您已经超过今日发送验证码的最大次数');
        } else {
            $data['num'] = $verifycode['num'] + 1;
            pdo_update('verifycode', $data, array('receiver' => $mobile));
        }
    }
    $code    = $data['verifycode'];
    $content = array('code' => $code);

    $result = cloud_sms_send($mobile, $templateid, $content);
    if (is_error($result)) {
        return error(1, $result['message']);
    }
    return error(0, '发送成功');
}

function verifycode_check($mobile, $verifycode)
{
    if (empty($mobile)) {
        return error(1, '手机号为空');
    }
    if (!preg_match(REGULAR_MOBILE, $mobile)) {
        return error(1, '手机号格式错误');
    }
    if (empty($verifycode)) {
        return error(1, '验证码为空');
    }
    if (istrlen($verifycode) < 6) {
        return error(1, '验证码长度小于六位');
    }
    $receiver = pdo_fetch_one('verifycode', array('receiver' => $mobile));
    if (empty($receiver)) {
        return error(1, '请重新发送验证码');
    }
    if (TIMESTAMP - $receiver['createtime'] > 60 * 20) {
        return error(1, '验证码过期');
    }
    if ($verifycode != $receiver['verifycode']) {
        return error(1, '验证码错误');
    }
    return true;
}

function mc_coupons_can_use($uid, $at_least = 0)
{
    $sql = 'SELECT * FROM ' . tablename('coupon') .
        ' WHERE `uid` = :uid AND `start_time` <= :start_time AND `end_time` >= :end_time AND `use_time` = :use_time ' .
        ' AND (`is_at_least` != 2 OR (`is_at_least` = 2 AND `at_least` <= :at_least)) ' .
        ' ORDER BY `end_time` ASC, `cash` ASC';
    $params = array(
        ':uid'        => $uid,
        ':start_time' => TIMESTAMP,
        ':end_time'   => TIMESTAMP,
        ':use_time'   => 0,
        ':at_least'   => $at_least,
    );
    $coupons_can_use = pdo_fetchall($sql, $params);
    return $coupons_can_use;
}

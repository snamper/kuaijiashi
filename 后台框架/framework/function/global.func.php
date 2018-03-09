<?php

defined('IN_IA') or exit('Access Denied');

function message($status, $message = '', $data = null)
{
    $return = array(
        'data'    => $data,
        'message' => $message,
        'status'  => $status,
    );
    exit(json_encode($return));
}

function app_checklogin($uid)
{
    if (empty($uid)) {
        message(9, '请先登录', null);
    }
    return true;
}

function uniqid_random($length = 6)
{
    return uniqid(random($length));
}

/**
 * Create sign
 * @param array $arr
 * @return string
 */
function create_sign($arr)
{
    sort($arr);
    $str = "";
    foreach ($arr as $v) {
        $str .= $v;
    }
    return md5($str);
}

function ver_compare($version1, $version2)
{
    if (strlen($version1) != strlen($version2)) {
        $version1_tmp = explode('.', $version1);
        $version2_tmp = explode('.', $version2);
        if (strlen($version1_tmp[1]) == 1) {
            $version1 .= '0';
        }
        if (strlen($version2_tmp[1]) == 1) {
            $version2 .= '0';
        }
    }
    return version_compare($version1, $version2);
}

function istripslashes($var)
{
    if (is_array($var)) {
        foreach ($var as $key => $value) {
            $var[stripslashes($key)] = istripslashes($value);
        }
    } else {
        $var = stripslashes($var);
    }
    return $var;
}

function ihtmlspecialchars($var)
{
    if (is_array($var)) {
        foreach ($var as $key => $value) {
            $var[htmlspecialchars($key)] = ihtmlspecialchars($value);
        }
    } else {
        $var = str_replace('&amp;', '&', htmlspecialchars($var, ENT_QUOTES));
    }
    return $var;
}

function ihtml_entity_decode($var)
{
    if (is_array($var)) {
        foreach ($var as $key => $value) {
            $var[ihtml_entity_decode($key)] = ihtml_entity_decode($value);
        }
    } else {
        $var = html_entity_decode($var, ENT_QUOTES | ENT_HTML401, 'UTF-8');
    }
    return $var;
}

function isetcookie($key, $value, $maxage = 0)
{
    global $_W;
    $expire = $maxage != 0 ? (TIMESTAMP + $maxage) : 0;
    return setcookie($_W['config']['cookie']['pre'] . $key, $value, $expire, $_W['config']['cookie']['path'], $_W['config']['cookie']['domain']);
}

function getip()
{
    static $ip = '';
    $ip        = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_CDN_SRC_IP'])) {
        $ip = $_SERVER['HTTP_CDN_SRC_IP'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] as $xip) {
            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                $ip = $xip;
                break;
            }
        }
    }
    return $ip;
}

function token($specialadd = '')
{
    global $_W;
    $hashadd = defined('IN_MANAGEMENT') ? 'for management' : '';
    return substr(md5($_W['config']['setting']['authkey'] . $hashadd . $specialadd), 8, 8);
}

function random($length, $numeric = false)
{
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    if ($numeric) {
        $hash = '';
    } else {
        $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
        $length--;
    }
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}

function checksubmit($var = 'submit', $allowget = 0)
{
    global $_W, $_GPC;
    if (empty($_GPC[$var])) {
        return false;
    }
    if ($allowget || (($_W['ispost'] && !empty($_W['token']) && $_W['token'] == $_GPC['token']) && (empty($_SERVER['HTTP_REFERER']) || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])))) {
        return true;
    }
    return false;
}

function hash_verify_code($code)
{
    global $_W;
    return md5(strtolower($code) . $_W['config']['setting']['authkey']);
}

function checkcaptcha($code)
{
    global $_GPC;

    session_start();
    $hash_code = hash_verify_code($code);
    $flag      = ($hash_code == $_GPC[COOKIE_VERIFY_CODE]) ||
        ($hash_code == $_SESSION[COOKIE_VERIFY_CODE]);
    unset($_SESSION[COOKIE_VERIFY_CODE]);
    isetcookie(COOKIE_VERIFY_CODE, '', -1);

    return $flag;
}

function tablename($table)
{
    return "`{$GLOBALS['_W']['config']['db']['master']['tablepre']}{$table}`";
}

function array_any($array)
{
    return !empty($array) && is_array($array);
}

function array_elements($keys, $src, $default = false)
{
    $return = array();
    if (!is_array($keys)) {
        $keys = array($keys);
    }
    foreach ($keys as $key) {
        if (isset($src[$key])) {
            $return[$key] = $src[$key];
        } else {
            $return[$key] = $default;
        }
    }
    return $return;
}

function array_select($array, $key)
{
    $arr = array();
    foreach ($array as $row) {
        $arr[] = $row[$key];
    }
    return $arr;
}

function array_select_many($array, $keys)
{
    $arr = array();
    foreach ($array as $row) {
        $item = array();
        foreach ($keys as $key) {
            $item[$key] = $row[$key];
        }
        $arr[] = $item;
    }
    return $arr;
}

function range_limit($num, $downline, $upline, $returnNear = true)
{
    $num      = intval($num);
    $downline = intval($downline);
    $upline   = intval($upline);
    if ($num < $downline) {
        return empty($returnNear) ? false : $downline;
    } elseif ($num > $upline) {
        return empty($returnNear) ? false : $upline;
    } else {
        return empty($returnNear) ? true : $num;
    }
}

function ijson_encode($value)
{
    if (empty($value)) {
        return false;
    }
    return addcslashes(json_encode($value), "\\\'\"");
}

function iserializer($value)
{
    return serialize($value);
}

function iunserializer($value)
{
    if (empty($value)) {
        return array();
    }
    if (!is_serialized($value)) {
        return $value;
    }
    $result = unserialize($value);
    if ($result === false) {
        $temp = preg_replace_callback('!s:(\d+):"(.*?)";!s', function ($matchs) {
            return 's:' . strlen($matchs[2]) . ':"' . $matchs[2] . '";';
        }, $value);
        return unserialize($temp);
    } else {
        return $result;
    }
}

function is_base64($str)
{
    if (!is_string($str)) {
        return false;
    }
    return $str == base64_encode(base64_decode($str));
}

function is_serialized($data, $strict = true)
{
    if (!is_string($data)) {
        return false;
    }
    $data = trim($data);
    if ('N;' == $data) {
        return true;
    }
    if (strlen($data) < 4) {
        return false;
    }
    if (':' !== $data[1]) {
        return false;
    }
    if ($strict) {
        $lastc = substr($data, -1);
        if (';' !== $lastc && '}' !== $lastc) {
            return false;
        }
    } else {
        $semicolon = strpos($data, ';');
        $brace     = strpos($data, '}');
        if (false === $semicolon && false === $brace) {
            return false;
        }

        if (false !== $semicolon && $semicolon < 3) {
            return false;
        }

        if (false !== $brace && $brace < 4) {
            return false;
        }

    }
    $token = $data[0];
    switch ($token) {
        case 's':
            if ($strict) {
                if ('"' !== substr($data, -2, 1)) {
                    return false;
                }
            } elseif (false === strpos($data, '"')) {
                return false;
            }
        case 'a':
        case 'O':
            return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
        case 'b':
        case 'i':
        case 'd':
            $end = $strict ? '$' : '';
            return (bool) preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
    }
    return false;
}

function url($segment, $params = array())
{
    list($controller, $action, $do, $op) = explode('/', $segment);
    $url                                 = './index.php?';
    if (!empty($controller)) {
        $url .= "c={$controller}&";
    }
    if (!empty($action)) {
        $url .= "a={$action}&";
    }
    if (!empty($do)) {
        $url .= "do={$do}&";
    }
    if (!empty($op)) {
        $url .= "op={$op}&";
    }
    if (!empty($params)) {
        $queryString = http_build_query($params, '', '&');
        $url .= $queryString;
    }
    return $url;
}

function admin_url($segment, $params = array())
{
    global $_W;
    list($controller, $action, $do, $op) = explode('/', $segment);
    $url                                 = $_W['siteroot'] . 'admin/index.php?';
    if (!empty($controller)) {
        $url .= "c={$controller}&";
    }
    if (!empty($action)) {
        $url .= "a={$action}&";
    }
    if (!empty($do)) {
        $url .= "do={$do}&";
    }
    if (!empty($op)) {
        $url .= "op={$op}&";
    }
    if (!empty($params)) {
        $queryString = http_build_query($params, '', '&');
        $url .= $queryString;
    }
    return $url;
}

function web_url($segment, $params = array())
{
    global $_W;
    list($controller, $action, $do, $op) = explode('/', $segment);
    $url                                 = $_W['siteroot'] . 'index.php?';
    if (!empty($controller)) {
        $url .= "c={$controller}&";
    }
    if (!empty($action)) {
        $url .= "a={$action}&";
    }
    if (!empty($do)) {
        $url .= "do={$do}&";
    }
    if (!empty($op)) {
        $url .= "op={$op}&";
    }
    if (!empty($params)) {
        $queryString = http_build_query($params, '', '&');
        $url .= $queryString;
    }
    return $url;
}

function oauth_url($url, $platform)
{
    $value   = parse_url($url);
    $new_url = $value['scheme'] . '://';
    if ($value['user'] && $value['pass']) {
        $new_url .= $value['user'] . ':' . $value['pass'] . '@';
    }
    if ($value['host']) {
        $new_url .= $value['host'];
    }
    if ($value['port']) {
        $new_url .= ':' . $value['port'];
    }
    if ($value['path'] && $value['path'] != '/') {
        $new_url .= $value['path'];
    } else {
        $new_url .= '/index.php';
    }
    if ($value['query']) {
        $new_url .= '?' . $value['query'];
    } else {
        $new_url .= '?c=home&a=home';
    }
    if ($platform == PLATFORM_WEIBO) {
        $new_url .= '&oauth=weibo';
    } elseif ($platform == PLATFORM_WECHAT) {
        $new_url .= '&oauth=wechat';
    } else {
        $new_url .= '&oauth=1';
    }
    if ($value['fragment']) {
        $new_url .= '#' . $value['fragment'];
    }
    return $new_url;
}

function pagenavi($count, $page_now, $size = false, $url = '', $context = array('before' => 5, 'after' => 4, 'ajaxcallback' => ''))
{
    global $redis;

    //$count总数据数

    //默认每页数据数
    if ($size) {
        $Page_size = $size;
    } else {
        $Page_size = 10;
    }
    ;

    $page_count = ceil($count / $Page_size);

    $init     = 1;
    $page_len = 5;
    $max_p    = $page_count;
    $pages    = $page_count;

    //判断当前页码
    if (empty($page_now) || $page_now < 0) {
        $page = 1;
    } else {
        $page = $page_now;
    }
    ;
    $offset     = $Page_size * ($page - 1);
    $page_len   = ($page_len % 2) ? $page_len : $pagelen + 1; //页码个数
    $pageoffset = ($page_len - 1) / 2; //页码个数左右偏移量

    $key = '<div class="mt-10" style="background:#fff;padding:10px;margin-bottom:10px;"><ul class="pagenavi">';
    $key .= "<li class='pnum disabled'><a href='javascript:;'>$page/$pages</a></li>"; //第几页,共几页
    unset($_GET['page']);
    $page_url = $_W['script_name'] . '?' . http_build_query($_GET);

    if ($page != 1) {
        $key .= "<li class='first'><a href=\"" . $page_url . "&page=1\">&laquo;</a></li>"; //第一页
        $key .= "<li class='prev'><a href=\"" . $page_url . "&page=" . ($page - 1) . "\">&lsaquo;</a></li>"; //上一页
    } else {
        $key .= "<li class='first disabled'><a href='javascript:;'>&laquo;</a></li>"; //第一页
        $key .= "<li class='prev disabled'><a href='javascript:;'>&lsaquo;</a></li>"; //上一页
    }
    if ($pages > $page_len) {
        //如果当前页小于等于左偏移
        if ($page <= $pageoffset) {
            $init  = 1;
            $max_p = $page_len;
        } else {
//如果当前页大于左偏移
            //如果当前页码右偏移超出最大分页数
            if ($page + $pageoffset >= $pages + 1) {
                $init = $pages - $page_len + 1;
            } else {
                //左右偏移都存在时的计算
                $init  = $page - $pageoffset;
                $max_p = $page + $pageoffset;
            }
        }
    }
    for ($i = $init; $i <= $max_p; $i++) {
        if ($i == $page) {
            $key .= '<li class="active"><a href="javascript:;">' . $i . '</a></li>';
        } else {
            $key .= "<li><a href=\"" . $page_url . "&page=" . $i . "\">" . $i . "</a></li>";
        }
    }
    if ($page != $pages) {
        $key .= "<li class='next'><a href=\"" . $page_url . "&page=" . ($page + 1) . "\">&rsaquo;</a>"; //下一页
        $key .= "<li class='last'><a href=\"" . $page_url . "&page={$pages}\">&raquo;</a></li>"; //最后一页
    } else {
        $key .= '<li class="next disabled"><a href="javascript:;">&rsaquo;</a></li>'; //下一页
        $key .= '<li class="last disabled"><a href="javascript:;">&raquo;</a></li>'; //最后一页
    }
    $key .= '</ul></div>';

    if ($count > $Page_size) {
        return $key;
    }
}

function pagination($total, $pageIndex, $pageSize = 15, $url = '', $context = array('before' => 5, 'after' => 4, 'ajaxcallback' => ''))
{
    global $_W;
    $pdata = array(
        'tcount'  => 0,
        'tpage'   => 0,
        'cindex'  => 0,
        'findex'  => 0,
        'pindex'  => 0,
        'nindex'  => 0,
        'lindex'  => 0,
        'options' => '',
    );
    if ($context['ajaxcallback']) {
        $context['isajax'] = true;
    }
    if (!empty($context['ajaxcallback']) && $context['ajaxcallback'] != 'null') {
        $callbackfunc = $context['ajaxcallback'];
    } elseif ($context['ajaxcallback'] == 'null') {
        $callbackfunc = '';
    } else {
        $callbackfunc = 'util.page';
    }

    $pdata['tcount'] = $total;
    $pdata['tpage']  = ceil($total / $pageSize);
    if ($pdata['tpage'] <= 1) {
        return '';
    }
    $cindex          = $pageIndex;
    $cindex          = min($cindex, $pdata['tpage']);
    $cindex          = max($cindex, 1);
    $pdata['cindex'] = $cindex;
    $pdata['findex'] = 1;
    $pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
    $pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
    $pdata['lindex'] = $pdata['tpage'];

    if ($context['isajax']) {
        if (!$url) {
            $url = $_W['script_name'] . '?' . http_build_query($_GET);
        }
        $pdata['faa'] = 'href="javascript:;" page="' . $pdata['findex'] . '" ' . ($callbackfunc ? 'onclick="' . $callbackfunc . '(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['findex'] . '\', this);return false;"' : '');
        $pdata['paa'] = 'href="javascript:;" page="' . $pdata['pindex'] . '" ' . ($callbackfunc ? 'onclick="' . $callbackfunc . '(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['pindex'] . '\', this);return false;"' : '');
        $pdata['naa'] = 'href="javascript:;" page="' . $pdata['nindex'] . '" ' . ($callbackfunc ? 'onclick="' . $callbackfunc . '(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['nindex'] . '\', this);return false;"' : '');
        $pdata['laa'] = 'href="javascript:;" page="' . $pdata['lindex'] . '" ' . ($callbackfunc ? 'onclick="' . $callbackfunc . '(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['lindex'] . '\', this);return false;"' : '');
    } else {
        if ($url) {
            $pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
            $pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
            $pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
            $pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
        } else {
            $_GET['page'] = $pdata['findex'];
            $pdata['faa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['pindex'];
            $pdata['paa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['nindex'];
            $pdata['naa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['lindex'];
            $pdata['laa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
        }
    }

    $html = '<div><ul class="pagination pagination-centered">';
    if ($pdata['cindex'] > 1) {
        $html .= "<li><a {$pdata['faa']} class=\"pager-nav\">首页</a></li>";
        $html .= "<li><a {$pdata['paa']} class=\"pager-nav\">&laquo;上一页</a></li>";
    }
    if (!$context['before'] && $context['before'] != 0) {
        $context['before'] = 5;
    }
    if (!$context['after'] && $context['after'] != 0) {
        $context['after'] = 4;
    }

    if ($context['after'] != 0 && $context['before'] != 0) {
        $range          = array();
        $range['start'] = max(1, $pdata['cindex'] - $context['before']);
        $range['end']   = min($pdata['tpage'], $pdata['cindex'] + $context['after']);
        if ($range['end'] - $range['start'] < $context['before'] + $context['after']) {
            $range['end']   = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
            $range['start'] = max(1, $range['end'] - $context['before'] - $context['after']);
        }
        for ($i = $range['start']; $i <= $range['end']; $i++) {
            if ($context['isajax']) {
                $aa = 'href="javascript:;" page="' . $i . '" ' . ($callbackfunc ? 'onclick="' . $callbackfunc . '(\'' . $_W['script_name'] . $url . '\', \'' . $i . '\', this);return false;"' : '');
            } else {
                if ($url) {
                    $aa = 'href="?' . str_replace('*', $i, $url) . '"';
                } else {
                    $_GET['page'] = $i;
                    $aa           = 'href="?' . http_build_query($_GET) . '"';
                }
            }
            $html .= ($i == $pdata['cindex'] ? '<li class="active"><a href="javascript:;">' . $i . '</a></li>' : "<li><a {$aa}>" . $i . '</a></li>');
        }
    }

    if ($pdata['cindex'] < $pdata['tpage']) {
        $html .= "<li><a {$pdata['naa']} class=\"pager-nav\">下一页&raquo;</a></li>";
        $html .= "<li><a {$pdata['laa']} class=\"pager-nav\">尾页</a></li>";
    }
    $html .= '</ul></div>';
    return $html;
}

function tomedia($src)
{
    global $_W;
    if (empty($src) || !is_string($src)) {
        return '';
    }
    $t = strtolower($src);
    if (strexists($t, 'http://') || strexists($t, 'https://')) {
        return $src;
    }
    return $_W['attachurl'] . $src;
}

function tourl($src)
{
    global $_W;
    if (empty($src) || !is_string($src)) {
        return '';
    }
    $t = strtolower($src);
    if (strexists($t, 'http://') || strexists($t, 'https://')) {
        return $src;
    }
    if (strpos($src, './') === 0) {
        return $_W['siteroot'] . substr($src, 2);
    }
    return $src;
}

function error($errno, $message = '')
{
    if ($errno > 1) {
        $trace     = array();
        $backtrace = debug_backtrace();
        if ($backtrace) {
            foreach ($backtrace as $key => $item) {
                if ($key < 10) {
                    $item['file'] = str_replace('\\', '/', $item['file']);
                    $item['file'] = str_replace(IA_ROOT, '', $item['file']);
                    $trace[]      = $item;
                }
            }
            pdo_insert('core_text', array('content' => iserializer($trace)));
            $text_id = pdo_insertid();
        }
        $error_log = array(
            'errno'      => $errno,
            'message'    => $message,
            'text_id'    => intval($text_id),
            'createtime' => TIMESTAMP,
            'ip'         => CLIENT_IP,
        );
        pdo_insert('core_error_log', $error_log);
    }
    return array(
        'errno'   => $errno,
        'message' => $message,
    );
}

function is_error($data)
{
    return $data && is_array($data) && isset($data['errno']) && isset($data['message']) && !empty($data['errno']);
}

function referer($default = '')
{
    global $_GPC, $_W;

    $_W['referer'] = !empty($_GPC['referer']) ? $_GPC['referer'] : $_SERVER['HTTP_REFERER'];
    $_W['referer'] = substr($_W['referer'], -1) == '?' ? substr($_W['referer'], 0, -1) : $_W['referer'];

    if (strpos($_W['referer'], 'member.php?act=login')) {
        $_W['referer'] = $default;
    }
    $_W['referer'] = $_W['referer'];
    $_W['referer'] = str_replace('&amp;', '&', $_W['referer']);
    $reurl         = parse_url($_W['referer']);

    if (!empty($reurl['host']) && !in_array($reurl['host'], array($_SERVER['HTTP_HOST'], 'www.' . $_SERVER['HTTP_HOST'])) && !in_array($_SERVER['HTTP_HOST'], array($reurl['host'], 'www.' . $reurl['host']))) {
        $_W['referer'] = $_W['siteroot'];
    } elseif (empty($reurl['host'])) {
        $_W['referer'] = $_W['siteroot'] . './' . $_W['referer'];
    }
    return strip_tags($_W['referer']);
}

function strexists($string, $segment)
{
    return !(strpos($string, $segment) === false);
}

function cutstr($string, $length, $havedot = false, $charset = '')
{
    global $_W;
    if (empty($charset)) {
        $charset = $_W['charset'];
    }
    if (strtolower($charset) == 'gbk') {
        $charset = 'gbk';
    } else {
        $charset = 'utf8';
    }
    if (istrlen($string, $charset) <= $length) {
        return $string;
    }
    if (function_exists('mb_strcut')) {
        $string = mb_substr($string, 0, $length, $charset);
    } else {
        $pre    = '{%';
        $end    = '%}';
        $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end), $string);

        $strcut = '';
        $strlen = strlen($string);

        if ($charset == 'utf8') {
            $n = $tn = $noc = 0;
            while ($n < $strlen) {
                $t = ord($string[$n]);
                if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $tn = 1;
                    $n++;
                    $noc++;
                } elseif (194 <= $t && $t <= 223) {
                    $tn = 2;
                    $n += 2;
                    $noc++;
                } elseif (224 <= $t && $t <= 239) {
                    $tn = 3;
                    $n += 3;
                    $noc++;
                } elseif (240 <= $t && $t <= 247) {
                    $tn = 4;
                    $n += 4;
                    $noc++;
                } elseif (248 <= $t && $t <= 251) {
                    $tn = 5;
                    $n += 5;
                    $noc++;
                } elseif ($t == 252 || $t == 253) {
                    $tn = 6;
                    $n += 6;
                    $noc++;
                } else {
                    $n++;
                }
                if ($noc >= $length) {
                    break;
                }
            }
            if ($noc > $length) {
                $n -= $tn;
            }
            $strcut = substr($string, 0, $n);
        } else {
            while ($n < $strlen) {
                $t = ord($string[$n]);
                if ($t > 127) {
                    $tn = 2;
                    $n += 2;
                    $noc++;
                } else {
                    $tn = 1;
                    $n++;
                    $noc++;
                }
                if ($noc >= $length) {
                    break;
                }
            }
            if ($noc > $length) {
                $n -= $tn;
            }
            $strcut = substr($string, 0, $n);
        }
        $string = str_replace(array($pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
    }

    if ($havedot) {
        $string = $string . "...";
    }

    return $string;
}

function istrlen($string, $charset = '')
{
    global $_W;
    if (empty($charset)) {
        $charset = $_W['charset'];
    }
    if (strtolower($charset) == 'gbk') {
        $charset = 'gbk';
    } else {
        $charset = 'utf8';
    }
    if (function_exists('mb_strlen')) {
        return mb_strlen($string, $charset);
    } else {
        $n      = $noc      = 0;
        $strlen = strlen($string);

        if ($charset == 'utf8') {

            while ($n < $strlen) {
                $t = ord($string[$n]);
                if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $n++;
                    $noc++;
                } elseif (194 <= $t && $t <= 223) {
                    $n += 2;
                    $noc++;
                } elseif (224 <= $t && $t <= 239) {
                    $n += 3;
                    $noc++;
                } elseif (240 <= $t && $t <= 247) {
                    $n += 4;
                    $noc++;
                } elseif (248 <= $t && $t <= 251) {
                    $n += 5;
                    $noc++;
                } elseif ($t == 252 || $t == 253) {
                    $n += 6;
                    $noc++;
                } else {
                    $n++;
                }
            }

        } else {

            while ($n < $strlen) {
                $t = ord($string[$n]);
                if ($t > 127) {
                    $n += 2;
                    $noc++;
                } else {
                    $n++;
                    $noc++;
                }
            }

        }

        return $noc;
    }
}

function emotion($message = '', $size = '24px')
{
    $emotions = array(
        "/::)", "/::~", "/::B", "/::|", "/:8-)", "/::<", "/::$", "/::X", "/::Z", "/::'(",
        "/::-|", "/::@", "/::P", "/::D", "/::O", "/::(", "/::+", "/:--b", "/::Q", "/::T",
        "/:,@P", "/:,@-D", "/::d", "/:,@o", "/::g", "/:|-)", "/::!", "/::L", "/::>", "/::,@",
        "/:,@f", "/::-S", "/:?", "/:,@x", "/:,@@", "/::8", "/:,@!", "/:!!!", "/:xx", "/:bye",
        "/:wipe", "/:dig", "/:handclap", "/:&-(", "/:B-)", "/:<@", "/:@>", "/::-O", "/:>-|",
        "/:P-(", "/::'|", "/:X-)", "/::*", "/:@x", "/:8*", "/:pd", "/:<W>", "/:beer", "/:basketb",
        "/:oo", "/:coffee", "/:eat", "/:pig", "/:rose", "/:fade", "/:showlove", "/:heart",
        "/:break", "/:cake", "/:li", "/:bome", "/:kn", "/:footb", "/:ladybug", "/:shit", "/:moon",
        "/:sun", "/:gift", "/:hug", "/:strong", "/:weak", "/:share", "/:v", "/:@)", "/:jj", "/:@@",
        "/:bad", "/:lvu", "/:no", "/:ok", "/:love", "/:<L>", "/:jump", "/:shake", "/:<O>", "/:circle",
        "/:kotow", "/:turn", "/:skip", "/:oY", "/:#-0", "/:hiphot", "/:kiss", "/:<&", "/:&>",
    );
    foreach ($emotions as $index => $emotion) {
        $message = str_replace($emotion, '<img style="width:' . $size . ';vertical-align:middle;" src="http://res.mail.qq.com/zh_CN/images/mo/DEFAULT2/' . $index . '.gif" />', $message);
    }
    return $message;
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;
    $key         = md5($key != '' ? $key : $GLOBALS['_W']['config']['setting']['authkey']);
    $keya        = md5(substr($key, 0, 16));
    $keyb        = md5(substr($key, 16, 16));
    $keyc        = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey   = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string        = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box    = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j       = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp     = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a       = ($a + 1) % 256;
        $j       = ($j + $box[$a]) % 256;
        $tmp     = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

function friendly_size($size)
{
    if ($size >= 1073741824) {
        $size = round($size / 1073741824 * 100) / 100 . ' GB';
    } elseif ($size >= 1048576) {
        $size = round($size / 1048576 * 100) / 100 . ' MB';
    } elseif ($size >= 1024) {
        $size = round($size / 1024 * 100) / 100 . ' KB';
    } else {
        $size = $size . ' Bytes';
    }
    return $size;
}

function array2xml($arr, $level = 1)
{
    $s = $level == 1 ? "<xml>" : '';
    foreach ($arr as $tagname => $value) {
        if (is_numeric($tagname)) {
            $tagname = $value['TagName'];
            unset($value['TagName']);
        }
        if (!is_array($value)) {
            $s .= "<{$tagname}>" . (!is_numeric($value) ? '<![CDATA[' : '') . $value . (!is_numeric($value) ? ']]>' : '') . "</{$tagname}>";
        } else {
            $s .= "<{$tagname}>" . array2xml($value, $level + 1) . "</{$tagname}>";
        }
    }
    $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);

    return $level == 1 ? $s . "</xml>" : $s;
}

function object2array($array)
{
    if (is_object($array)) {
        $array = (array) $array;
    }
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            $array[$key] = object2array($value);
        }
    }
    return $array;
}

function scriptname()
{
    global $_W;
    $_W['script_name'] = basename($_SERVER['SCRIPT_FILENAME']);
    if (basename($_SERVER['SCRIPT_NAME']) === $_W['script_name']) {
        $_W['script_name'] = $_SERVER['SCRIPT_NAME'];
    } else {
        if (basename($_SERVER['PHP_SELF']) === $_W['script_name']) {
            $_W['script_name'] = $_SERVER['PHP_SELF'];
        } else {
            if (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $_W['script_name']) {
                $_W['script_name'] = $_SERVER['ORIG_SCRIPT_NAME'];
            } else {
                if (($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false) {
                    $_W['script_name'] = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $_W['script_name'];
                } else {
                    if (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
                        $_W['script_name'] = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
                    } else {
                        $_W['script_name'] = 'unknown';
                    }
                }
            }
        }
    }
    return $_W['script_name'];
}

function utf8_bytes($cp)
{
    if ($cp > 0x10000) {
        return chr(0xF0 | (($cp & 0x1C0000) >> 18)) .
        chr(0x80 | (($cp & 0x3F000) >> 12)) .
        chr(0x80 | (($cp & 0xFC0) >> 6)) .
        chr(0x80 | ($cp & 0x3F));
    } else if ($cp > 0x800) {
        return chr(0xE0 | (($cp & 0xF000) >> 12)) .
        chr(0x80 | (($cp & 0xFC0) >> 6)) .
        chr(0x80 | ($cp & 0x3F));
    } else if ($cp > 0x80) {
        return chr(0xC0 | (($cp & 0x7C0) >> 6)) .
        chr(0x80 | ($cp & 0x3F));
    } else {
        return chr($cp);
    }
}

function currency_format($currency, $decimals = 2)
{
    $currency = floatval($currency);
    if (empty($currency)) {
        return '0.00';
    }
    $currency = number_format($currency, $decimals);
    $currency = str_replace(',', '', $currency);
    return $currency;
}

function weight_format($weight, $decimals = 3)
{
    if (empty($weight) || !is_numeric($weight)) {
        return '0.000';
    }
    $weight = number_format($weight, $decimals);
    $weight = str_replace(',', '', $weight);
    return $weight;
}

function mobile_mask($mobile)
{
    return substr($mobile, 0, 3) . '****' . substr($mobile, 7);
}

function mall_debug($value, $label = '')
{
    if (DEVELOPMENT && ((CURRENT_IP && CURRENT_IP == CLIENT_IP) || CURRENT_IP == '')) {
        $label = $label ? $label : gettype($value);
        echo "$label -> <br><pre>";
        print_r($value);
        echo "</pre>";
    }
}

function mall_log($message, $data = '')
{
    if ($data) {
        pdo_insert('core_text', array('content' => iserializer($data)));
        $text_id = pdo_insertid();
    }
    $log = array(
        'errno'      => 0,
        'message'    => $message,
        'text_id'    => intval($text_id),
        'createtime' => TIMESTAMP,
        'ip'         => CLIENT_IP,
    );
    pdo_insert('core_error_log', $log);
}

function mall_sql($sql, $params)
{
    if (DEVELOPMENT) {
        if ($params) {
            foreach ($params as $key => $value) {
                $value = '\'' . ($value) . '\'';
                $sql   = str_replace($key, $value, $sql);
            }
        }
        echo '<pre>' . PHP_EOL . $sql . '</pre>' . PHP_EOL;
    }
}

function api_log($message, $data = '')
{
    if (DEVELOPMENT && ((CURRENT_IP && CURRENT_IP == CLIENT_IP) || CURRENT_IP == '')) {
        if ($data) {
            $message .= ' -> ';
            if (is_resource($data)) {
                $message .= '资源文件';
            } elseif (gettype($data) == 'object' || is_array($data)) {
                $message .= iserializer($data);
            } else {
                $message .= $data;
            }
        }
        $filename = IA_ROOT . '/data/logs/api-log-' . date('Ymd', TIMESTAMP) . '.' . $_GET['platform'] . '.txt';
        if (!file_exists($filename)) {
            load()->func('file');
            mkdirs(dirname($filename));
        }
        file_put_contents($filename, $message . PHP_EOL . PHP_EOL, FILE_APPEND);
    }
}

function aes_pkcs7_decode($encrypt_data, $key, $iv = false)
{
    require_once IA_ROOT . '/framework/library/pkcs7/pkcs7Encoder.php';
    $encrypt_data = base64_decode($encrypt_data);
    if (!empty($iv)) {
        $iv = base64_decode($iv);
    }
    $pc     = new Prpcrypt($key);
    $result = $pc->decrypt($encrypt_data, $iv);
    if ($result[0] != 0) {
        return error($result[0], '解密失败');
    }
    return $result[1];
}

function isimplexml_load_string($string, $class_name = 'SimpleXMLElement', $options = 0, $ns = '', $is_prefix = false)
{
    libxml_disable_entity_loader(true);
    if (preg_match('/(\<\!DOCTYPE|\<\!ENTITY)/i', $string)) {
        return false;
    }
    return simplexml_load_string($string, $class_name, $options, $ns, $is_prefix);
}

function pwd_hash($password, $salt)
{
    return md5("{$password}-{$salt}-{$GLOBALS['_W']['config']['setting']['authkey']}");
}

function access_denied($reason = '')
{
    global $_W;
    $message = 'Access Denied';
    if ($reason) {
        $message = "{$message} : {$reason}";
    }
    if ($_W['isajax']) {
        message(error(1, $message));
    }
    exit($message);
}

function ajax_only()
{
    global $_W;
    if (empty($_W['isajax'])) {
        access_denied('ajax only');
    }
}

function post_only()
{
    global $_W;
    if (empty($_W['ispost'])) {
        access_denied('post only');
    }
}

function ajax_post_only()
{
    global $_W;
    if (empty($_W['isajax']) || empty($_W['ispost'])) {
        access_denied('ajax && post only');
    }
}

function itrim(&$data)
{
    if (is_array($data)) {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                itrim($value);
            } else {
                $value = trim($value);
            }
        }
        ;
    } else {
        $data = trim($data);
    }
    return $data;
}

function is_mobile($mobile)
{
    return preg_match(REGULAR_MOBILE, $mobile);
}

function is_password($password)
{
    return preg_match(REGULAR_PASSWORD, $password);
}

function subtext($text, $length)
{
    if (mb_strlen($text, 'utf8') > $length) {
        return mb_substr($text, 0, $length, 'utf8') . '...';
    }

    return $text;
}

/**
 * 字符串截取，支持中文和其他编码
 * @param string $str
 * @param int $start
 * @param int $length
 * @param string $charset
 * @param boolean $suffix
 * @return string
 */
function w_substr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{
    $suffix_str = $suffix ? '…' : '';
    if (function_exists('mb_substr')) {
        return mb_substr($str, $start, $length, $charset) . $suffix_str;
    } elseif (function_exists('iconv_substr')) {
        return iconv_substr($str, $start, $length, $charset) . $suffix_str;
    } else {
        $pattern           = array();
        $pattern['utf-8']  = '/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/';
        $pattern['gb2312'] = '/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/';
        $pattern['gbk']    = '/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/';
        $pattern['big5']   = '/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/';
        preg_match_all($pattern[$charset], $str, $matches);
        $slice = implode("", array_slice($matches[0], $start, $length));
        return $slice . $suffix_str;
    }
}

//截断
function icutstr($sourcestr, $cutlength)
{
    $returnstr  = '';
    $i          = 0;
    $n          = 0;
    $str_length = strlen($sourcestr); //字符串的字节数
    while (($n < $cutlength) and ($i <= $str_length)) {
        $temp_str = substr($sourcestr, $i, 1);
        $ascnum   = Ord($temp_str); //得到字符串中第$i位字符的ascii码
        if ($ascnum >= 224) //如果ASCII位高与224，
        {
            $returnstr = $returnstr . substr($sourcestr, $i, 3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
            $i         = $i + 3; //实际Byte计为3
            $n++; //字串长度计1
        } elseif ($ascnum >= 192) //如果ASCII位高与192，
        {
            $returnstr = $returnstr . substr($sourcestr, $i, 2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
            $i         = $i + 2; //实际Byte计为2
            $n++; //字串长度计1
        } elseif ($ascnum >= 65 && $ascnum <= 90) //如果是大写字母，
        {
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i         = $i + 1; //实际的Byte数仍计1个
            $n++; //但考虑整体美观，大写字母计成一个高位字符
        } else //其他情况下，包括小写字母和半角标点符号，
        {
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i         = $i + 1; //实际的Byte数计1个
            $n         = $n + 0.5; //小写字母和半角标点等与半个高位字符宽…
        }
    }
    if ($str_length > $cutlength) {
        $returnstr = $returnstr . '…'; //超过长度时在尾处加上省略号
    }
    return $returnstr;
};

function uid_openid($uid, $array = 'openid')
{

    $result = pdo_fetch("SELECT * FROM " . tablename('wechat_fan') . " WHERE `uid`=:uid", array(":uid" => $uid));
    if (empty($result)) {
        return false;
    } elseif ($array == 'openid') {
        return $result['openid'];
    } else {
        return $result;
    }
}

function user_info($uid)
{
    $mc_member  = pdo_fetch("SELECT * FROM " . tablename('mc_member') . " WHERE `uid`=:uid", array(":uid" => $uid));
    $wechat_fan = pdo_fetch("SELECT * FROM " . tablename('wechat_fan') . " WHERE `uid`=:uid", array(":uid" => $uid));
    if (empty($mc_member['realname'])) {
        $mc_member['realname'] = $wechat_fan['realname'];
    }
    if (empty($mc_member['nickname'])) {
        $mc_member['nickname'] = $wechat_fan['nickname'];
    }
    if (empty($mc_member['avatar'])) {
        $mc_member['avatar'] = $wechat_fan['avatar'];
    }

    $mc_member['avatar'] = tomedia($mc_member['avatar']);

    if (empty($mc_member)) {
        return false;
    } else {
        return $mc_member;
    }
}

/*查找多维数组值*/
function array_search_values($needle, $haystack, $a = 0, $nodes_temp = array())
{
    global $nodes_values;
    $a++;
    foreach ($haystack as $key1 => $value1) {
        $nodes_temp[$a] = $key1;
        if (is_array($value1)) {
            array_search_values($needle, $value1, $a, $nodes_temp);
        } else if ($value1 === $needle) {
            $nodes_values[] = $nodes_temp;
        }
    }
    $result = $nodes_values;
    unset($nodes_values);
    return $result;
}

/*搜索多维数组的键名*/
function array_search_keys($needle, $haystack)
{
    global $nodes_keys;
    foreach ($haystack as $key1 => $value1) {
        if ($key1 === $needle) {
            $nodes_keys[] = $value1;
        }
        if (is_array($value1)) {
            array_search_keys($needle, $value1);
        }
    }
    $result = $nodes_keys;
    unset($nodes_keys);
    return $result;
}

/*二维数组搜索*/
function value_in_array($dir, $arr)
{
    foreach ($arr as $key => $val) {
        if (in_array($dir, $val)) {
            return $key;
        }
    }
    return false;
}

//过滤HTML危险标签javascript
function remove_javascript($text)
{
    // 过滤攻击代码
    while (preg_match('/(<[^><]+)(ondblclick|onclick|onload|onerror|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|action|background|codebase|dynsrc|lowsrc)([^><]*)/i', $text, $mat)) {
        $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
    }
    while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat)) {
        $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
    }
    return $text;
}

//HTML危险标签过滤
function remove_html($text, $type = 'html')
{
    if ($type == 'all') {
        $text = nl2br($text);
        $text = real_strip_tags($text);
        $text = addslashes($text);
        $text = trim($text);
    } else {
        // 无标签格式
        $text_tags = '';
        //只保留链接
        $link_tags = '<a>';
        //只保留图片
        $image_tags = '<img>';
        //只存在字体样式
        $font_tags = '<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';
        //标题摘要基本格式
        $base_tags = $font_tags . '<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike>';
        //兼容Form格式
        $form_tags = $base_tags . '<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>';
        //内容等允许HTML的格式
        $html_tags = $base_tags . '<ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><span><object><embed><param>';
        //专题等全HTML格式
        $all_tags = $form_tags . $html_tags . '<!DOCTYPE><meta><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe><div>';
        //过滤标签
        $text = real_strip_tags($text, ${$type . '_tags'});
        // 过滤攻击代码
        while (preg_match('/(<[^><]+)(ondblclick|onclick|onload|onerror|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|action|background|codebase|dynsrc|lowsrc)([^><]*)/i', $text, $mat)) {
            $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
        }
        while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat)) {
            $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
        }
    }
    return $text;
}
function real_strip_tags($str, $allowable_tags = "")
{
    $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
    return strip_tags($str, $allowable_tags);
}

//时间格式化
function format_date($time)
{
    if (time() - $time > 0) {
        $t = time() - $time;
        $f = array(
            '31536000' => '年',
            '2592000'  => '个月',
            '604800'   => '星期',
            '86400'    => '天',
            '3600'     => '小时',
            '60'       => '分钟',
            '1'        => '秒',
        );
        foreach ($f as $k => $v) {
            if (0 != $c = floor($t / (int) $k)) {
                return $c . $v . '前';
            }
        }
    } else {
        $t = $time - time();
        $f = array(
            '31536000' => '年',
            '2592000'  => '个月',
            '604800'   => '星期',
            '86400'    => '天',
            '3600'     => '小时',
            '60'       => '分钟',
            '1'        => '秒',
        );
        foreach ($f as $k => $v) {
            if (0 != $c = floor($t / (int) $k)) {
                return $c . $v . '后';
            }
        }
    }
}

function sec2time($time)
{
    if (is_numeric($time)) {
        $value = array(
            "days"    => 0, "hours"   => 0,
            "minutes" => 0, "seconds" => 0,
        );
        if ($time >= 86400) {
            $value["days"] = floor($time / 86400);
            $time          = ($time % 86400);
        }
        if ($time >= 3600) {
            $value["hours"] = floor($time / 3600);
            $time           = ($time % 3600);
        }
        if ($time >= 60) {
            $value["minutes"] = floor($time / 60);
            $time             = ($time % 60);
        }
        $value["seconds"] = floor($time);
        if (!empty($value["days"])) {
            $day = $value["days"];
        }
        if (!empty($value["hours"])) {
            $hour = $value["hours"];
        }
        if (!empty($value["minutes"])) {
            $minute = $value["minutes"];
        }
        $t = $day . "天" . " " . $hour . "小时" . $minute . "分" . $value["seconds"] . "秒";
        return $t;

    } else {
        return (bool) false;
    }
}

/* $str 原始中文字符串
 * $encoding 原始字符串的编码，默认GBK
 * $prefix 编码后的前缀，默认"&#"
 * $postfix 编码后的后缀，默认";"
 */
function unicode_encode($str, $encoding = 'GBK', $prefix = '&#', $postfix = ';')
{
    $str    = iconv($encoding, 'UCS-2', $str);
    $arrstr = str_split($str, 2);
    $unistr = '';
    for ($i = 0, $len = count($arrstr); $i < $len; $i++) {
        $dec = hexdec(bin2hex($arrstr[$i]));
        $unistr .= $prefix . $dec . $postfix;
    }
    return $unistr;
}

/**
 * $str Unicode编码后的字符串
 * $decoding 原始字符串的编码，默认GBK
 * $prefix 编码字符串的前缀，默认"&#"
 * $postfix 编码字符串的后缀，默认";"
 */
function unicode_decode($unistr, $encoding = 'GBK', $prefix = '&#', $postfix = ';')
{
    $arruni = explode($prefix, $unistr);
    $unistr = '';
    for ($i = 1, $len = count($arruni); $i < $len; $i++) {
        if (strlen($postfix) > 0) {
            $arruni[$i] = substr($arruni[$i], 0, strlen($arruni[$i]) - strlen($postfix));
        }
        $temp = intval($arruni[$i]);
        $unistr .= ($temp < 256) ? chr(0) . chr($temp) : chr($temp / 256) . chr($temp % 256);
    }
    return iconv('UCS-2', $encoding, $unistr);
}

/**
 * 求两个已知经纬度之间的距离,单位为米
 *
 * @param lng1 $ ,lng2 经度
 * @param lat1 $ ,lat2 纬度
 * @return float 距离，单位米
 */
function idistance($lng1, $lat1, $lng2, $lat2)
{
    // 将角度转为狐度
    $radLat1 = deg2rad((double) $lat1); //deg2rad()函数将角度转换为弧度
    $radLat2 = deg2rad((double) $lat2);
    $radLng1 = deg2rad((double) $lng1);
    $radLng2 = deg2rad((double) $lng2);
    $a       = $radLat1 - $radLat2;
    $b       = $radLng1 - $radLng2;
    $s       = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.138 * 1000;
    return $s;
}

function sizecount($size)
{
    if ($size >= 1073741824) {
        $size = round($size / 1073741824 * 100) / 100 . ' GB';
    } elseif ($size >= 1048576) {
        $size = round($size / 1048576 * 100) / 100 . ' MB';
    } elseif ($size >= 1024) {
        $size = round($size / 1024 * 100) / 100 . ' KB';
    } else {
        $size = $size . ' Bytes';
    }
    return $size;
}

function bytecount($str)
{
    if (strtolower($str[strlen($str) - 1]) == 'b') {
        $str = substr($str, 0, -1);
    }
    if (strtolower($str[strlen($str) - 1]) == 'k') {
        return floatval($str) * 1024;
    }
    if (strtolower($str[strlen($str) - 1]) == 'm') {
        return floatval($str) * 1048576;
    }
    if (strtolower($str[strlen($str) - 1]) == 'g') {
        return floatval($str) * 1073741824;
    }
}

function icontent_images($content = "")
{
    $content  = ihtml_entity_decode($content);
    $pregRule = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
    $content  = preg_replace_callback($pregRule, function ($matchs) {
        return '<img src="' . ifetch_src($matchs[1]) . '" style="max-width:100%;">';
    }, $content);
    return $content;
}

function ifetch_src($url)
{
    global $_W;

    if ($_W['setting']['remote']['type'] != '0') {
        $imgUrl = $_W['setting']['copyright']['ossUrl'];
    } else {
        $imgUrl = ATTACHMENT_ROOT;
    }
    if (stripos($url, $imgUrl) !== false) {
        return $url;
    }
    load()->func('file');
    load()->model('attachment');
    load()->func('communication');

    $prefix = substr($url, 0, 2);
    if ($prefix == '//') {
        $url = 'https:' . $url;
    }
    $pathinfo   = pathinfo($url);
    $extension  = !empty($pathinfo['extension']) ? $pathinfo['extension'] : 'jpg';
    $originname = $pathinfo['basename'];

    load()->func('communication');
    $resp = ihttp_get($url);
    $type = 'image';

    if (!is_error($resp)) {
        if ($resp['code'] == '404') {
            return false;
        }
        $filename = file_random_name(ATTACHMENT_ROOT . '/' . $_W['setting']['upload'][$type]['folder'], $extension);
        $pathname = $_W['setting']['upload'][$type]['folder'] . $filename;
        $fullname = ATTACHMENT_ROOT . '/' . $pathname;
        if (mkdirs(ATTACHMENT_ROOT . '/' . $_W['setting']['upload'][$type]['folder']) && file_put_contents($fullname, $resp['content']) == false) {
            return false;
        }
    } else {
        return false;
    }

    if (!empty($_W['setting']['upload']['image']['resize']['enable']) && intval($_W['setting']['upload']['image']['resize']['width']) > 0) {
        file_image_thumb($fullname, $fullname, intval($_W['setting']['upload']['image']['resize']['width']));
    }
    $thumb_status = file_image_thumb($fullname, $fullname . '.thumb.jpg', 350);
    if (is_error($thumb_status)) {
        return false;
    }
    $sizeinfo = getimagesize($fullname);

    $filesize = filesize($fullname);
    if (!empty($_W['setting']['remote']['type'])) {
        if ($type == 'image') {
            $remotestatus = file_remote_upload($pathname, true, $type) && file_remote_upload($pathname . '.thumb.jpg', true, $type);
        } else {
            $remotestatus = file_remote_upload($pathname, true, $type);
        }
        if (is_error($remotestatus)) {

            return false;

        }

        file_delete($pathname);

        if ($type == 'image') {

            file_delete($pathname . '.thumb.jpg');

        }
    }

    $insert = array(
        'uid'        => $_W['uid'],
        'byte'       => $filesize,
        'width'      => $sizeinfo[0],
        'height'     => $sizeinfo[1],
        'filename'   => $originname,
        'attachment' => $pathname,
        'type'       => 'image',
        'createtime' => TIMESTAMP,
    );
    pdo_insert('core_attachment', $insert);
    $insert['id'] = pdo_insertid();

    $result                      = array();
    $result['attachment']        = $insert;
    $result['attachment']['url'] = tomedia($insert['attachment']);

    return $result['attachment']['url'];
}

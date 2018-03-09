<?php

defined('IN_IA') or exit('Access Denied');
load()->model('setting');
load()->model('attachment');

$dos = array('display', 'global', 'attachment', 'remote', 'buckets', 'oss', 'cos', 'qiniu', 'ftp');
$do  = in_array($do, $dos) ? $do : 'display';

$post_max_size       = ini_get('post_max_size');
$post_max_size       = $post_max_size > 0 ? bytecount($post_max_size) / 1024 : 0;
$upload_max_filesize = bytecount(ini_get('upload_max_filesize'));

if ($do == 'display') {
    if (empty($_W['setting']['upload'])) {
        $upload = $_W['config']['upload'];
    } else {
        $upload = $_W['setting']['upload'];
    }
    if (empty($upload['image']['thumb'])) {
        $upload['image']['thumb'] = 1;
    } else {
        $upload['image']['thumb'] = 0;
    }
    $upload['image']['width'] = intval($upload['image']['width']);
    if (empty($upload['image']['width'])) {
        $upload['image']['width'] = 800;
    }

    $remote = $_W['setting']['remote'];

    $return = array(
        'upload'              => $upload,
        'post_max_size'       => $post_max_size,
        'upload_max_filesize' => $upload_max_filesize,
        'remote'              => $remote,
    );
    message(1, '获取数据成功', $return);
}

if ($do == 'global') {
    $harmtype = array('asp', 'php', 'jsp', 'js', 'css', 'php3', 'php4', 'php5', 'ashx', 'aspx', 'exe', 'cgi');
    $upload   = $_GPC['upload'];
    if (!empty($upload['image']['thumb'])) {
        $upload['image']['thumb'] = 1;
    } else {
        $upload['image']['thumb'] = 0;
    }
    $upload['image']['width'] = intval(trim($upload['image']['width']));
    if (!empty($upload['image']['thumb']) && empty($upload['image']['width'])) {
        message(0, '请设置图片缩略宽度.', null);
    }
    $upload['image']['limit'] = max(0, min(intval(trim($upload['image']['limit'])), $post_max_size));
    if (empty($upload['image']['limit'])) {
        message(0, '请设置图片上传支持的文件大小, 单位 KB.', null);
    }
    if (empty($upload['image']['extentions'])) {
        message(0, '请添加支持的图片附件后缀类型', null);
    }
    if (!empty($upload['image']['extentions'])) {
        foreach ($upload['image']['extentions'] as $key => &$row) {
            $row = trim($row);
            if (in_array($row, $harmtype)) {
                unset($upload['image']['extentions'][$key]);
                continue;
            }
        }
    }
    if (!is_array($upload['image']['extentions']) || count($upload['image']['extentions']) < 1) {
        message(0, '请添加支持的图片附件后缀类型', null);
    }
    $upload['audio']['limit'] = max(0, min(intval(trim($upload['audio']['limit'])), $post_max_size));
    if (empty($upload['image']['limit'])) {
        message(0, '请设置音频视频上传支持的文件大小, 单位 KB.', null);
    }
    if (!empty($upload['audio']['extentions'])) {
        foreach ($upload['audio']['extentions'] as $key => &$row) {
            $row = trim($row);
            if (in_array($row, $harmtype)) {
                unset($upload['audio']['extentions'][$key]);
                continue;
            }
        }
    }
    if (!is_array($upload['audio']['extentions']) || count($upload['audio']['extentions']) < 1) {
        message(0, '请添加支持的音频视频附件后缀类型', null);
    }
    setting_save($upload, 'upload');
    message(1, '更新设置成功！', null);
}

if ($do == 'remote') {
    $remote = array(
        'type'   => intval($_GPC['type']),
        'ftp'    => array(
            'ssl'      => intval($_GPC['ftp']['ssl']),
            'host'     => $_GPC['ftp']['host'],
            'port'     => $_GPC['ftp']['port'],
            'username' => $_GPC['ftp']['username'],
            'password' => strexists($_GPC['ftp']['password'], '*') ? $_W['setting']['remote']['ftp']['password'] : $_GPC['ftp']['password'],
            'pasv'     => intval($_GPC['ftp']['pasv']),
            'dir'      => $_GPC['ftp']['dir'],
            'url'      => $_GPC['ftp']['url'],
            'overtime' => intval($_GPC['ftp']['overtime']),
        ),
        'alioss' => array(
            'key'                 => $_GPC['alioss']['key'],
            'secret'              => strexists($_GPC['alioss']['secret'], '*') ? $_W['setting']['remote']['alioss']['secret'] : $_GPC['alioss']['secret'],
            'bucket'              => $_GPC['alioss']['bucket'],
            'outBucket'           => $_GPC['alioss']['outBucket'],
            'pipelineId'          => $_GPC['alioss']['pipelineId'],
            'transcodeTemplateId' => $_GPC['alioss']['transcodeTemplateId'],
            'url'                 => $_GPC['alioss']['url'],
        ),
        'qiniu'  => array(
            'accesskey'    => trim($_GPC['qiniu']['accesskey']),
            'secretkey'    => strexists($_GPC['qiniu']['secretkey'], '*') ? $_W['setting']['remote']['qiniu']['secretkey'] : trim($_GPC['qiniu']['secretkey']),
            'bucket'       => trim($_GPC['qiniu']['bucket']),
            'pipelineName' => trim($_GPC['qiniu']['pipelineName']),
            'url'          => trim($_GPC['qiniu']['url']),
        ),
        'cos'    => array(
            'appid'     => trim($_GPC['cos']['appid']),
            'secretid'  => trim($_GPC['cos']['secretid']),
            'secretkey' => strexists(trim($_GPC['cos']['secretkey']), '*') ? $_W['setting']['remote']['cos']['secretkey'] : trim($_GPC['cos']['secretkey']),
            'bucket'    => trim($_GPC['cos']['bucket']),
            'local'     => trim($_GPC['cos']['local']),
            'url'       => trim($_GPC['cos']['url']),
        ),
    );
    if ($remote['type'] == ATTACH_OSS) {
        if (trim($remote['alioss']['key']) == '') {
            message(0, '阿里云OSS-Access Key ID不能为空', null);
        }
        if (trim($remote['alioss']['secret']) == '') {
            message(0, '阿里云OSS-Access Key Secret不能为空', null);
        }
        $buckets = attachment_alioss_buctkets($remote['alioss']['key'], $remote['alioss']['secret']);
        if (is_error($buckets)) {
            message(0, 'OSS-Access Key ID 或 OSS-Access Key Secret错误，请重新填写', null);
        }
        list($remote['alioss']['bucket'], $remote['alioss']['url']) = explode('@@', $_GPC['alioss']['bucket']);
        if (empty($buckets[$remote['alioss']['bucket']])) {
            message(0, 'Bucket不存在或是已经被删除', null);
        }
        $remote['alioss']['url']    = 'http://' . $remote['alioss']['bucket'] . '.' . $buckets[$remote['alioss']['bucket']]['location'] . '.aliyuncs.com';
        $remote['alioss']['ossurl'] = $buckets[$remote['alioss']['bucket']]['location'] . '.aliyuncs.com';
        if (!empty($_GPC['alioss']['url'])) {
            $url = trim($_GPC['alioss']['url'], '/');
            if (!strexists($url, 'http://') && !strexists($url, 'https://')) {
                $url = 'http://' . $url;
            }
            $remote['alioss']['url'] = $url;
        }
    } elseif ($remote['type'] == ATTACH_FTP) {
        if (empty($remote['ftp']['host'])) {
            message(0, 'FTP服务器地址为必填项.', null);
        }
        if (empty($remote['ftp']['username'])) {
            message(0, 'FTP帐号为必填项.', null);
        }
        if (empty($remote['ftp']['password'])) {
            message(0, 'FTP密码为必填项.', null);
        }
    } elseif ($remote['type'] == ATTACH_QINIU) {
        if (empty($remote['qiniu']['accesskey'])) {
            message(0, '请填写Accesskey', null);
        }
        if (empty($remote['qiniu']['secretkey'])) {
            message(0, '请填写secretkey', null);
        }
        if (empty($remote['qiniu']['bucket'])) {
            message(0, '请填写bucket', null);
        }
        if (empty($remote['qiniu']['url'])) {
            message(0, '请填写url', null);
        } else {
            $remote['qiniu']['url'] = strexists($remote['qiniu']['url'], 'http') ? trim($remote['qiniu']['url'], '/') : 'http://' . trim($remote['qiniu']['url'], '/');
        }
        $auth = attachment_qiniu_auth($remote['qiniu']['accesskey'], $remote['qiniu']['secretkey'], $remote['qiniu']['bucket']);
        if (is_error($auth)) {
            $message = $auth['message']['error'] == 'bad token' ? 'Accesskey或Secretkey填写错误， 请检查后重新提交' : 'bucket填写错误或是bucket所对应的存储区域选择错误，请检查后重新提交';
            message(0, $message, null);
        }
    } elseif ($remote['type'] == ATTACH_COS) {
        if (empty($remote['cos']['appid'])) {
            message(0, '请填写APPID', null);
        }
        if (empty($remote['cos']['secretid'])) {
            message(0, '请填写SECRETID', null);
        }
        if (empty($remote['cos']['secretkey'])) {
            message(0, '请填写SECRETKEY', null);
        }
        if (empty($remote['cos']['bucket'])) {
            message(0, '请填写BUCKET', null);
        }
        if (empty($remote['cos']['url'])) {
            $remote['cos']['url'] = 'http://' . $remote['cos']['bucket'] . '-' . $remote['cos']['appid'] . '.cos.myqcloud.com';
        } else {
            if (strexists($remote['cos']['url'], '.cos.myqcloud.com') && !strexists($url, '//' . $remote['cos']['bucket'] . '-')) {
                $remote['cos']['url'] = 'http://' . $remote['cos']['bucket'] . '-' . $remote['cos']['appid'] . '.cos.myqcloud.com';
            }
            $remote['cos']['url'] = strexists($remote['cos']['url'], 'http') ? trim($remote['cos']['url'], '/') : 'http://' . trim($remote['cos']['url'], '/');
        }
        $auth = attachment_cos_auth($remote['cos']['bucket'], $remote['cos']['appid'], $remote['cos']['secretid'], $remote['cos']['secretkey'], $remote['cos']['local']);

        if (is_error($auth)) {
            message(0, $auth['message'], null);
        }
    }
    setting_save($remote, 'remote');
    message(1, '远程附件配置信息更新成功！', null);
}

if ($do == 'buckets') {
    $key     = $_GPC['key'];
    $secret  = $_GPC['secret'];
    $buckets = attachment_alioss_buctkets($key, $secret);
    if (is_error($buckets)) {
        message(error(-1, ''), '', 'ajax');
    }
    $bucket_datacenter = attachment_alioss_datacenters();
    $bucket            = array();
    foreach ($buckets as $key => $value) {
        $value['loca_name'] = $key . '@@' . $bucket_datacenter[$value['location']];
        $bucket[]           = $value;
    }
    message(1, '获取阿里云OSS的buckets成功', $bucket);
}

if ($do == 'ftp') {
    require IA_ROOT . '/framework/library/ftp/ftp.php';
    $ftp_config = array(
        'hostname' => trim($_GPC['host']),
        'username' => trim($_GPC['username']),
        'password' => strexists($_GPC['password'], '*') ? $_W['setting']['remote']['ftp']['password'] : trim($_GPC['password']),
        'port'     => intval($_GPC['port']),
        'ssl'      => trim($_GPC['ssl']),
        'passive'  => trim($_GPC['pasv']),
        'timeout'  => intval($_GPC['overtime']),
        'rootdir'  => trim($_GPC['dir']),
    );
    $url      = trim($_GPC['url']);
    $filename = 'home.jpg';
    $ftp      = new Ftp($ftp_config);
    if (true === $ftp->connect()) {
        if ($ftp->upload(ATTACHMENT_ROOT . '/images/global/' . $filename, $filename)) {
            load()->func('communication');
            $response = ihttp_get($url . '/' . $filename);
            if (is_error($response)) {
                message(error(-1, '配置失败，FTP远程访问url错误'), '', 'ajax');
            }
            if (intval($response['code']) != 200) {
                message(error(-1, '配置失败，FTP远程访问url错误'), '', 'ajax');
            }
            $image = getimagesizefromstring($response['content']);
            if (!empty($image) && strexists($image['mime'], 'image')) {
                message(error(0, '配置成功'), '', 'ajax');
            } else {
                message(error(-1, '配置失败，FTP远程访问url错误'), '', 'ajax');
            }
        } else {
            message(error(-1, '上传图片失败，请检查配置'), '', 'ajax');
        }
    } else {
        message(error(-1, 'FTP服务器连接失败，请检查配置'), '', 'ajax');
    }
}

if ($do == 'oss') {
    load()->model('attachment');
    $key                = $_GPC['key'];
    $secret             = strexists($_GPC['secret'], '*') ? $_W['setting']['remote']['alioss']['secret'] : $_GPC['secret'];
    $bucket             = $_GPC['bucket'];
    $buckets            = attachment_alioss_buctkets($key, $secret);
    list($bucket, $url) = explode('@@', $_GPC['bucket']);
    $result             = attachment_newalioss_auth($key, $secret, $bucket, $url);
    if (is_error($result)) {
        message(error(-1, 'OSS-Access Key ID 或 OSS-Access Key Secret错误，请重新填写'), '', 'ajax');
    }
    $ossurl = $buckets[$bucket]['location'] . '.aliyuncs.com';
    if (!empty($_GPC['url'])) {
        if (!strexists($_GPC['url'], 'http://') && !strexists($_GPC['url'], 'https://')) {
            $url = 'http://' . trim($_GPC['url']);
        } else {
            $url = trim($_GPC['url']);
        }
        $url = trim($url, '/') . '/';
    } else {
        $url = 'http://' . $bucket . '.' . $buckets[$bucket]['location'] . '.aliyuncs.com/';
    }
    load()->func('communication');
    $filename = 'home.jpg';
    $response = ihttp_request($url . '/' . $filename, array(), array('CURLOPT_REFERER' => $_SERVER['SERVER_NAME']));
    if (is_error($response)) {
        message(error(-1, '配置失败，阿里云访问url错误'), '', 'ajax');
    }
    if (intval($response['code']) != 200) {
        message(error(-1, '配置失败，阿里云访问url错误,请保证bucket为公共读取的'), '', 'ajax');
    }
    $image = getimagesizefromstring($response['content']);
    if (!empty($image) && strexists($image['mime'], 'image')) {
        message(error(0, '配置成功'), '', 'ajax');
    } else {
        message(error(-1, '配置失败，阿里云访问url错误'), '', 'ajax');
    }
}

if ($do == 'qiniu') {
    load()->model('attachment');
    $_GPC['secretkey'] = strexists($_GPC['secretkey'], '*') ? $_W['setting']['remote']['qiniu']['secretkey'] : $_GPC['secretkey'];
    $auth              = attachment_qiniu_auth(trim($_GPC['accesskey']), trim($_GPC['secretkey']), trim($_GPC['bucket']));
    if (is_error($auth)) {
        message(error(-1, '配置失败，请检查配置。注：请检查存储区域是否选择的是和bucket对应<br/>的区域'), '', 'ajax');
    }
    load()->func('communication');
    $url      = $_GPC['url'];
    $url      = strexists($url, 'http') ? trim($url, '/') : 'http://' . trim($url, '/');
    $filename = 'home.jpg';
    $response = ihttp_request($url . '/' . $filename, array(), array('CURLOPT_REFERER' => $_SERVER['SERVER_NAME']));
    if (is_error($response)) {
        message(error(-1, '配置失败，七牛访问url错误'), '', 'ajax');
    }
    if (intval($response['code']) != 200) {
        message(error(-1, '配置失败，七牛访问url错误,请保证bucket为公共读取的'), '', 'ajax');
    }
    $image = getimagesizefromstring($response['content']);
    if (!empty($image) && strexists($image['mime'], 'image')) {
        message(error(0, '配置成功'), '', 'ajax');
    } else {
        message(error(-1, '配置失败，七牛访问url错误'), '', 'ajax');
    }
}

if ($do == 'cos') {
    load()->model('attachment');
    $url = $_GPC['url'];
    if (empty($url)) {
        $url = 'http://' . $_GPC['bucket'] . '-' . $_GPC['appid'] . '.cos.myqcloud.com';
    }
    $bucket            = trim($_GPC['bucket']);
    $_GPC['secretkey'] = strexists($_GPC['secretkey'], '*') ? $_W['setting']['remote']['cos']['secretkey'] : $_GPC['secretkey'];
    if (!strexists($url, '//' . $bucket . '-') && strexists($url, '.cos.myqcloud.com')) {
        $url = 'http://' . $bucket . '-' . trim($_GPC['appid']) . '.cos.myqcloud.com';
    }
    $auth = attachment_cos_auth(trim($_GPC['bucket']), trim($_GPC['appid']), trim($_GPC['secretid']), trim($_GPC['secretkey']), $_GPC['local']);

    if (is_error($auth)) {
        message(error(-1, '配置失败，请检查配置'), '', 'ajax');
    }
    load()->func('communication');
    $url      = strexists($url, 'http') ? trim($url, '/') : 'http://' . trim($url, '/');
    $filename = 'home.jpg';
    $response = ihttp_request($url . '/' . $filename, array(), array('CURLOPT_REFERER' => $_SERVER['SERVER_NAME']));
    if (is_error($response)) {
        message(error(-1, '配置失败，腾讯cos访问url错误'), '', 'ajax');
    }
    if (intval($response['code']) != 200) {
        message(error(-1, '配置失败，腾讯cos访问url错误,请保证bucket为公共读取的'), '', 'ajax');
    }
    $image = getimagesizefromstring($response['content']);
    if (!empty($image) && strexists($image['mime'], 'image')) {
        message(error(0, '配置成功'), '', 'ajax');
    } else {
        message(error(-1, '配置失败，腾讯cos访问url错误'), '', 'ajax');
    }
}

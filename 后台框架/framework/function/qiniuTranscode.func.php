<?php

defined('IN_IA') or exit('Access Denied');
require_once IA_ROOT . '/framework/library/qiniu/autoload.php';
use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;

function qiniuTranscode($filename)
{
    global $_W;
    $auth   = new Qiniu\Auth($_W['setting']['remote']['qiniu']['accesskey'], $_W['setting']['remote']['qiniu']['secretkey']);
    $config = new Qiniu\Config();
    //要转码的文件所在的空间和文件名。
    $key      = $filename;
    $filename = substr($filename, 0, strrpos($filename, '.'));
    //转码是使用的队列名称。 https://portal.qiniu.com/mps/pipeline
    $pipeline = $_W['setting']['remote']['qiniu']['pipelineName'];
    $force    = false;
    //转码完成后通知到你的业务服务器。
    $notifyUrl = '';
    //$config->useHTTPS=true;
    $pfop = new PersistentFop($auth, $config);
    //要进行转码的转码操作。 http://developer.qiniu.com/docs/v6/api/reference/fop/av/avthumb.html
    $fops            = "avthumb/mp3/ab/128k/ar/44100/acodec/libmp3lame|saveas/" . \Qiniu\base64_urlSafeEncode($_W['setting']['remote']['qiniu']['bucket'] . ":" . $filename . ".mp3");
    list($id, $err1) = $pfop->execute($_W['setting']['remote']['qiniu']['bucket'], $key, $fops, $pipeline, $notifyUrl, $force);
    if ($err1 != null) {
        //var_dump($err);
        return error(1, '远程附件上传失败，请检查配置并重新上传');
    }
    //查询转码的进度和状态
    list($ret, $err2) = $pfop->status($id);
    if ($err2 != null) {
        //var_dump($err2);
        return error(1, '远程附件上传失败，请检查配置并重新上传');
    } else {
        return error(0, $filename . '.mp3');
    }
}

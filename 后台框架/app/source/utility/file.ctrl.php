<?php

defined('IN_IA') or exit('Access Denied');

load()->func('file');

load()->model('attachment');

$dos = array('upload', 'fetch', 'browser', 'delete', 'local', 'videofetch');

if (!in_array($do, $dos)) {

    exit('Access Denied');

}

$types = array('image', 'audio', 'video');

$type = in_array($_GET['type'], $types) ? $_GET['type'] : 'image';

if ($type == 'image') {

    $attachment_type = ATTACHMENT_TYPE_IMAGE;

} elseif ($type == 'audio') {

    $attachment_type = ATTACHMENT_TYPE_AUDIO;

} elseif ($type == 'video') {

    $attachment_type = ATTACHMENT_TYPE_VIDEO;

}

$setting = $_W['setting']['upload'][$type];

$filename   = '';
$pathname   = '';
$fullname   = '';
$originname = '';
$extension  = '';

$url = trim($_GPC['url']);

$width = trim($_GPC['width']);

$height = trim($_GPC['height']);

if ($do == 'fetch') {

    $pathinfo = pathinfo($url);

    $extension = !empty($pathinfo['extension']) ? $pathinfo['extension'] : 'jpg';

    $originname = $pathinfo['basename'];

    load()->func('communication');

    $resp = ihttp_get($url);

    if (!is_error($resp)) {

        if ($resp['code'] == '404') {
            $return = array(
                'status'  => 0,
                'data'    => null,
                'message' => '提取失败: 未找到该资源文件.',
            );
            exit(json_encode($return));

        }

        $filename = file_random_name(ATTACHMENT_ROOT . '/' . $_W['setting']['upload'][$type]['folder'], $extension);

        $pathname = $_W['setting']['upload'][$type]['folder'] . $filename;

        $fullname = ATTACHMENT_ROOT . '/' . $pathname;

        if (mkdirs(ATTACHMENT_ROOT . '/' . $_W['setting']['upload'][$type]['folder']) && file_put_contents($fullname, $resp['content']) == false) {

            $return = array(
                'ststus'  => 0,
                'data'    => null,
                'message' => '提取失败',
            );
            exit(json_encode($return));

        }

    } else {
        $return = array(
            'ststus'  => 0,
            'data'    => null,
            'message' => '提取资源时, 访问失败.',
        );
        exit(json_encode($return));

    }

}

if ($do == 'upload' || $do == 'fetch' || $do == 'videofetch') {

    if ($do == 'upload') {

        if (empty($_FILES['file']['tmp_name'])) {

            $binaryfile = file_get_contents('php://input', 'r');

            if (!empty($binaryfile)) {

                mkdirs(ATTACHMENT_ROOT . '/temp');

                $tempfilename = random(5);

                $tempfile = ATTACHMENT_ROOT . '/temp/' . $tempfilename;

                if (file_put_contents($tempfile, $binaryfile)) {

                    $imagesize = @getimagesize($tempfile);

                    $imagesize = explode('/', $imagesize['mime']);

                    $_FILES['file'] = array(

                        'name'     => $tempfilename . '.' . $imagesize[1],

                        'tmp_name' => $tempfile,

                        'error'    => 0,

                    );

                }

            }

        }

        if (!empty($_FILES['file']['name'])) {

            if ($_FILES['file']['error'] != 0) {

                $return = array(
                    'ststus'  => 0,
                    'data'    => null,
                    'message' => '上传失败，请重试！',
                );
                exit(json_encode($return));

            }

            $originname = $_FILES['file']['name'];

            $file = file_upload($_FILES['file'], $type);

            if (is_error($file)) {

                $return = array(
                    'ststus'  => 0,
                    'data'    => null,
                    'message' => $file['message'],
                );
                exit(json_encode($return));

            }

            $filename = $file['filename'];

            $pathname = $file['path'];

            $fullname = ATTACHMENT_ROOT . '/' . $pathname;

        } else {

            $return = array(
                'ststus'  => 0,
                'data'    => null,
                'message' => '请选择要上传的附件！',
            );
            exit(json_encode($return));

        }

    }

    $sizeinfo = array($width, $height);

    if ($type == 'image') {

        if (!empty($_W['setting']['upload']['image']['resize']['enable']) && intval($_W['setting']['upload']['image']['resize']['width']) > 0) {

            file_image_thumb($fullname, $fullname, intval($_W['setting']['upload']['image']['resize']['width']));

        }

        $thumb_status = file_image_thumb($fullname, $fullname . '.thumb.jpg', 350);

        if (is_error($thumb_status)) {

            $return = array(
                'ststus'  => 0,
                'data'    => null,
                'message' => $thumb_status['message'],
            );
            exit(json_encode($return));

        }

        $sizeinfo = getimagesize($fullname);

    }

    $filesize = filesize($fullname);

    if (!empty($_W['setting']['remote']['type'])) {
        if ($type == 'image') {
            $remotestatus = file_remote_upload($pathname, true, $type) && file_remote_upload($pathname . '.thumb.jpg', true, $type);
        } else {
            $remotestatus = file_remote_upload($pathname, true, $type);
        }
        if (is_error($remotestatus)) {

            $return = array(
                'status'  => 0,
                'data'    => null,
                'message' => $remotestatus['message'],
            );
            exit(json_encode($return));

        }

        file_delete($pathname);

        if ($type == 'image') {

            file_delete($pathname . '.thumb.jpg');

        } elseif ($type == 'audio') {
            $pathname = $remotestatus['message'];
        }

    }

    if ($do == 'videofetch') {

        $originname = $url;

        $pathname = $url;

    }

    $insert = array(

        'uid'        => $_W['uid'],

        'byte'       => $filesize,

        'width'      => $sizeinfo[0],

        'height'     => $sizeinfo[1],

        'filename'   => $originname,

        'attachment' => $pathname,

        'type'       => $attachment_type,

        'createtime' => TIMESTAMP,

    );

    pdo_insert('core_attachment', $insert);

    $insert['id'] = pdo_insertid();

    $result = array();

    $result['attachment'] = $insert;

    $result['attachment']['url'] = tomedia($insert['attachment']);

    $return = array(
        'status'  => 1,
        'data'    => $result['attachment'],
        'message' => '上传成功',
    );
    exit(json_encode($return));
    //message(error(0, $result), '', 'ajax');

}
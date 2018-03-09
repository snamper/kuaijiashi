<?php

defined('IN_IA') or exit('Access Denied');

define('REGULAR_EMAIL', '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/i');
define('REGULAR_MOBILE', '/1\d{10}/');
define('REGULAR_USERNAME', '/^[\x{4e00}-\x{9fa5}a-z\d_\.]{3,15}$/iu');
define('REGULAR_PASSWORD', '/^\w{6,15}$/iu');

define('TEMPLATE_DISPLAY', 0);

define('TEMPLATE_FETCH', 1);

define('TEMPLATE_INCLUDEPATH', 2);

if (!file_exists(IA_ROOT . '/admin/resource/components')) {
    define('UPGRADE_COMPONENT', true);
} else {
    define('UPGRADE_COMPONENT', false);
}

define('CURRENT_IP', '');

define('ON', 1);
define('OFF', 2);

define('YES', 1);
define('NO', 2);

define('HIDE', 1);
define('SHOW', 2);

define('UPLOAD_TYPE_COMMON', 1);

define('UPLOAD_TYPE_REMOTE', 2);

define('UPLOAD_TYPE_ALIOSS', 3);

define('ATTACH_FTP', 1);
define('ATTACH_OSS', 2);
define('ATTACH_QINIU', 3);
define('ATTACH_COS', 4);

define('ATTACHMENT_TYPE_IMAGE', 1);

define('ATTACHMENT_TYPE_AUDIO', 2);

define('ATTACHMENT_TYPE_VIDEO', 3);

define('PLATFORM_WECHAT', 1);

define('PLATFORM_WEIBO', 2);

define('COOKIE_UNIONID', '__unionid');
define('COOKIE_UID', '__uid');
define('COOKIE_OPENID', '__openid');
define('COOKIE_CLIENT_OPENID', '__client_openid');
define('COOKIE_APP_OPENID', '__app_openid');
define('COOKIE_MINA_OPENID', '__mina_openid');
define('COOKIE_FORWARD', '__forward');

define('COOKIE_SESSION', '__session');
define('COOKIE_VERIFY_CODE', '__verify_code');

define('DATE', 'Y-m-d');
define('TIME', 'H:i:s');
define('DATETIME', 'Y-m-d H:i:s');

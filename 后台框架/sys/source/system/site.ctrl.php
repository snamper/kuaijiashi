<?php

defined('IN_IA') or exit('Access Denied');
$do       = empty($do) ? 'display' : $do;
$settings = $_W['setting']['copyright'];
if (empty($settings) || !is_array($settings)) {
    $settings = array();
}
if (!empty($settings)) {
    $settings['logo']         = tomedia($settings['logo']);
    $settings['logo_xs']      = tomedia($settings['logo_xs']);
    $settings['qrcode']       = tomedia($settings['qrcode']);
    $settings['protocol']     = ihtml_entity_decode($settings['protocol']);
    $settings['registerInfo'] = ihtml_entity_decode($settings['registerInfo']);
}
if ($do == 'display') {
    $return = array(
        'settings' => $settings,
        'config'   => $_W['config'],
    );
    message(1, '获取站点设置成功', $return);
}

if ($do == 'save') {
    if ($_W['setting']['remote']['type'] != '0') {
        $imgUrl = $_W['setting']['copyright']['ossUrl'];
    } else {
        $imgUrl = ATTACHMENT_ROOT;
    }
    $data = array(
        'status'       => $settings['status'],
        'reason'       => $settings['reason'],
        'sitename'     => $_GPC['sitename'],
        'logo'         => str_ireplace($imgUrl, '', $_GPC['logo']),
        'logo_xs'      => str_ireplace($imgUrl, '', $_GPC['logo_xs']),
        'qrcode'       => str_ireplace($imgUrl, '', $_GPC['qrcode']),
        'keywords'     => $_GPC['keywords'],
        'description'  => $_GPC['description'],
        'footer'       => $_GPC['footer'],
        'pledge'       => $_GPC['pledge'],
        'rental'       => $_GPC['rental'],
        'email'        => $_GPC['email'],
        'hotline'      => $_GPC['hotline'],
        'protocol'     => $_GPC['protocol'],
        'creditInfo'   => $_GPC['creditInfo'],
        'giveInfo'     => $_GPC['giveInfo'],
        'registerInfo' => $_GPC['registerInfo'],
        'pledgeInfo'   => $_GPC['pledgeInfo'],
        'ossUrl'       => $_GPC['ossUrl'],
        'version'      => $_GPC['version'],
    );

    cache_build_setting();
    cache_clean();

    setting_save($data, 'copyright');

    message(1, '更新设置成功！', null);

}

<?php
defined('IN_IA') or exit('Access Denied');

load()->func('communication');
if ($do == 'coordinate_amap') {
    $city            = $_GPC['city'];
    $data['key']     = 'd8e935f30a38182fa5dbf7d67d0df597';
    $data['address'] = $city;
    $post_url        = "http://restapi.amap.com/v3/geocode/geo?parameters";
    $response        = ihttp_request($post_url, $data);
    if (is_error($response)) {
        $return = array(
            'data'    => null,
            'message' => '获取坐标失败',
            'status'  => '0',
        );
        exit(json_encode($return));
    } else {
        $result     = @json_decode($response['content'], true);
        $coordinate = explode(",", $result['geocodes'][0]['location']);
        $return     = array(
            'data'    => $coordinate,
            'message' => '获取坐标成功',
            'status'  => '1',
        );
        exit(json_encode($return));
    }
}
if ($do == 'city_amap') {
    $lat              = $_GPC['lat'];
    $lng              = $_GPC['lng'];
    $data['key']      = 'd8e935f30a38182fa5dbf7d67d0df597';
    $data['location'] = round($lng, 6) . ',' . round($lat, 6);
    $post_url         = "http://restapi.amap.com/v3/geocode/regeo?parameters";
    $response         = ihttp_request($post_url, $data);
    if (is_error($response)) {
        $return = array(
            'data'    => null,
            'message' => '获取城市失败',
            'status'  => '0',
        );
        exit(json_encode($return));
    } else {
        $result = @json_decode($response['content'], true);
        $city   = '';
        if (!empty($result['regeocodes'][0]['addressComponent']['city'])) {
            $city = str_ireplace('市', '', $result['regeocodes'][0]['addressComponent']['city']);
        } else if ($result['regeocodes'][0]['addressComponent']['district']) {
            $city = str_ireplace('市', '', $result['regeocodes'][0]['addressComponent']['district']);
        }
        $return = array(
            'data'    => $city,
            'message' => '获取城市成功',
            'status'  => '1',
        );
        exit(json_encode($return));
    }
}

if ($do == 'coordinate') {
    $city            = $_GPC['city'];
    $data['output']  = 'json';
    $data['ak']      = 'jzGTDQuw9IAVIgklpeliZ942';
    $data['address'] = $city;
    $post_url        = "http://api.map.baidu.com/geocoder/v2/";
    $response        = ihttp_request($post_url, $data);
    if (is_error($response)) {
        $return = array(
            'data'    => null,
            'message' => '获取坐标失败',
            'status'  => '0',
        );
        exit(json_encode($return));
    } else {
        $result = @json_decode($response['content'], true);
        $return = array(
            'data'    => $result['result']['location'],
            'message' => '获取坐标成功',
            'status'  => '1',
        );
        exit(json_encode($return));
    }
}

if ($do == 'city') {
    $lat               = $_GPC['lat'];
    $lng               = $_GPC['lng'];
    $data['output']    = 'json';
    $data['ak']        = 'jzGTDQuw9IAVIgklpeliZ942';
    $data['coordtype'] = $_GPC['coordtype'];
    $data['location']  = round($lat, 6) . ',' . round($lng, 6);
    $post_url          = "http://api.map.baidu.com/geocoder/v2/";
    $response          = ihttp_request($post_url, $data);
    if (is_error($response)) {
        $return = array(
            'data'    => null,
            'message' => '获取城市失败',
            'status'  => '0',
        );
        exit(json_encode($return));
    } else {
        $result = @json_decode($response['content'], true);
        $city   = '';
        if (!empty($result['result']['addressComponent']['city'])) {
            $city = str_ireplace('市', '', $result['result']['addressComponent']['city']);
        } else if ($result['result']['addressComponent']['district']) {
            $city = str_ireplace('市', '', $result['result']['addressComponent']['district']);
        }

        $location = $result['result']['location'];

        $return = array(
            'data'    => array(
                'city'     => $city,
                'location' => $location,
            ),
            'message' => '获取城市成功',
            'status'  => '1',
        );
        exit(json_encode($return));
    }
}

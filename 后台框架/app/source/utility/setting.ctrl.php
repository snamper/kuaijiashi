<?php

defined('IN_IA') or exit('Access Denied');

	$homeset_data = pdo_fetchall("SELECT * FROM ".tablename('homeset'));

	$homeset = array();
	foreach ($homeset_data as $k => $v) {
		$homeset[$v['key']] = $v['value'];
		if($v['key'] == 'homesliders'){
			$homesliders = iunserializer($v['value']);
		}
		if($v['key'] == 'homenav'){
			$homenav = iunserializer($v['value']);
		}
		if($v['key'] == 'eventsliders'){
			$eventsliders = iunserializer($v['value']);
		}	
		if($v['key'] == 'kefu'){
			$kefu = iunserializer($v['value']);
		}		
	}
	
	$return = array(
		'data'=>array(
			'cate'=>$homeset[''],
			'eventsliders'=>$eventsliders,
			'homenav'=>$homenav,
			'homesliders'=>$homesliders,
			'kefu'=>$kefu,
			'items'=>$homeset['items'],
			'qrcode'=>$homeset['qrcode'],
			'hottel'=>$homeset['hottel'],
			'kfqrcode'=>$homeset['kfqrcode'],
			'cash'=>array(
				'charge'=>$homeset['charge'],
				'max'=>$homeset['max'],
				'min'=>$homeset['min'],
			),
			'site'=>$_W['setting']['copyright']
		),
		'dialog'=>'',
		'message'=>'',
		'status'=>1	
	);
	exit(json_encode($return));
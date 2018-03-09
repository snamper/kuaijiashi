<?php defined('IN_IA') or exit('Access Denied');?><!DOCTYPE html>
<html lang="zh-cn">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>
<?php  if($_W['page']['title']) { ?>
	<?php  echo $_W['page']['title'];?>
<?php  } ?>
<?php  if($_W['setting']['copyright']['sitename']) { ?>
	- <?php  echo $_W['setting']['copyright']['sitename'];?>
<?php  } ?>
	</title>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<link rel="stylesheet" href="./resource/components/pace/themes/blue/pace-theme-minimal.css" /> 
	<script>paceOptions = {elements: true};</script>
	<script src="./resource/components/pace/pace.min.js"></script>
	<link href="./resource/css/bootstrap.min.css" rel="stylesheet">
	<link href="./resource/css/font-awesome.min.css" rel="stylesheet">
	<link href="./resource/css/common.css" rel="stylesheet">
	<script>
		var require = {
			urlArgs: "v=<?php  echo time();?>"
		};
	</script>
	<script src="./resource/js/require.js"></script>
	<script src="./resource/js/app/config.js?v=<?php  echo time();?>"></script>
	<script src="./resource/js/app/util.js?v=<?php  echo time();?>"></script>
	<script src="./resource/js/lib/jquery-1.11.1.min.js"></script>
	<script src="./resource/js/lib/bootstrap.min.js?v=<?php  echo time();?>"></script>
	<!--[if lt IE 9]>
		<script src="./resource/js/html5shiv.min.js"></script>
		<script src="./resource/js/respond.min.js"></script>
	<![endif]-->
	<script type="text/javascript">
	if(navigator.appName == 'Microsoft Internet Explorer'){
		if(navigator.userAgent.indexOf("MSIE 5.0")>0 || navigator.userAgent.indexOf("MSIE 6.0")>0 || navigator.userAgent.indexOf("MSIE 7.0")>0) {
			alert('您使用的 IE 浏览器版本过低, 推荐使用 Chrome 浏览器或 IE8 及以上版本浏览器.');
		}
	}
	
	window.sysinfo = {
<?php  if(!empty($_W['uid'])) { ?>
		"uid": "<?php  echo $_W['uid'];?>",
<?php  } ?>
		"siteroot": "<?php  echo $_W['siteroot'];?>",
		"siteurl": "<?php  echo $_W['siteurl'];?>",
		"attachurl": "<?php  echo $_W['attachurl'];?>",
		"cookie" : "{'pre': '<?php  echo $_W['config']['cookie']['pre'];?>'}"
	};
	</script>
</head>
<body>

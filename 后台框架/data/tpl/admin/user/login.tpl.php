<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header-base', TEMPLATE_INCLUDEPATH)) : (include template('common/header-base', TEMPLATE_INCLUDEPATH));?>
<style>
	@media screen and (max-width:767px){.login .panel.panel-default{width:90%; min-width:300px;}}
	@media screen and (min-width:768px){.login .panel.panel-default{width:70%;}}
	@media screen and (min-width:1200px){.login .panel.panel-default{width:50%;}}
	.login .logo,.register .logo{width:100%; text-align:center; margin-bottom:2em;}
	.login .logo img,.register .logo img{display:block;margin-left:auto;margin-right:auto;max-width:100px;max-height:100px;}		
</style>
<div class="login">
	<div class="logo">
		<a href="./?refresh"><img src="<?php  echo tomedia($_W['setting']['copyright']['logo']);?>"/></a>
	</div>
	<div class="clearfix" style="margin-bottom:5em;">
		<div class="panel panel-default container">
			<div class="panel-body">
				<form action="" method="post" role="form" onsubmit="return formcheck();">
					<div class="form-group input-group">
						<div class="input-group-addon"><i class="fa fa-user"></i> </div>
						<input name="username" type="text" class="form-control input-lg" placeholder="请输入用户名登录">
					</div>
					<div class="form-group input-group">
						<div class="input-group-addon"><i class="fa fa-unlock-alt"></i> </div>
						<input name="password" type="password" class="form-control input-lg" placeholder="请输入登录密码">
					</div>
					<div class="form-group input-group">
						<div class="input-group-addon"><i class="fa fa-ticket"></i></div>
						<input name="verify" type="text" style="width: 100%;" class="form-control input-lg" placeholder="请输入验证码">
						<div class="input-group-addon" style="width: 50%; text-align: left;" >
							<a href="javascript:;" id="toggle" style="text-decoration: none">
								<img id="imgverify" style="height:32px;" title="点击图片更换验证码"/> 看不清？换一张
							</a>
						</div>
					</div>
					<div class="form-group">
						<label class="checkbox-inline input-lg">
							<input type="checkbox" value="true" name="rember"> 记住用户名
						</label>
						<div class="pull-right">
							<?php  if(!$_W['siteclose']) { ?><a href="<?php  echo url('user/register');?>" class="btn btn-link btn-lg">注册</a><?php  } ?>
							<input type="submit" name="submit" value="登录" class="btn btn-primary btn-lg" />
							<input name="token" value="<?php  echo $_W['token'];?>" type="hidden" />
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="center-block footer" role="footer">
		<div class="text-center">
			<?php  if(IMS_FAMILY == 'x' && $_W['setting']['copyright']['footerright']) { ?>
				<?php  echo $_W['setting']['copyright']['footerright'];?>
			<?php  } else { ?>
				<a href="http://mall.we7.cc/">关于微擎 MALL</a> - <a href="http://bbs.we7.cc/">微擎 MALL 帮助</a>
			<?php  } ?>
			<?php  if(IMS_FAMILY == 'x' && $_W['setting']['copyright']['statcode']) { ?>
				&nbsp; &nbsp;<?php  echo $_W['setting']['copyright']['statcode'];?>
			<?php  } ?>
		</div>
		<div class="text-center">
			<?php  if(IMS_FAMILY == 'x' && $_W['setting']['copyright']['footerleft']) { ?>
				<?php  echo $_W['setting']['copyright']['footerleft'];?>
			<?php  } else { ?>
				Powered by <a href="http://mall.we7.cc/"><b>微擎 MALL</b></a> v<?php echo IMS_VERSION;?> &copy; 2014 <a href="http://mall.we7.cc/">mall.we7.cc</a>
			<?php  } ?>
		</div>
	</div>
</div>
<script>

function formcheck() {
	if($('#remember:checked').length == 1) {
		cookie.set('remember-username', $(':text[name="username"]').val());
	} else {
		cookie.del('remember-username');
	}
	return true;
}

require(['jquery'],function($){
	$(function(){
		$('#imgverify').prop('src', '<?php  echo url('utility/code')?>r='+Math.round(new Date().getTime()));
		$('#toggle').click(function() {
			$('#imgverify').prop('src', '<?php  echo url('utility/code')?>r='+Math.round(new Date().getTime()));
			return false;
		});
		
		<?php  if(!empty($_W['setting']['copyright']['verifycode'])) { ?>
			$('#form1').submit(function() {
				var verify = $(':text[name="verify"]').val();
				if (verify == '') {
					alert('请填写验证码');
					return false;
				}
			});
		<?php  } ?>
		var h = document.documentElement.clientHeight;
		$(".login").css('min-height',h);
	});
});
</script>
</body>
</html>

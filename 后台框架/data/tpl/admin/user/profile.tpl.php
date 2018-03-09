<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>

<ul class="nav nav-tabs">
	<li <?php  if($do == 'profile') { ?>class="active"<?php  } ?>><a href="<?php  echo url('user/profile/profile');?>">账号信息</a></li>
	<li <?php  if($do == 'base') { ?>class="active"<?php  } ?>><a href="<?php  echo url('user/profile/base');?>">基本信息</a></li>
</ul>
<?php  if($do == 'profile') { ?>
	<div class="panel panel-default">
		<div class="panel-heading">管理员信息修改</div>
		<div class="panel-body">
			<form action="" method="post" class="form-horizontal form" onsubmit="return formcheck(this)">
				<div class="form-group">
					<label class="col-md-2 control-label">管理员帐号</label>
					<div class="col-md-9">
							<input type="text" name="name" class="form-control" value="<?php  echo $_W['username'];?>" readonly />
							<div class="help-block">只能用'0-9'、'a-z'、'A-Z'、'.'、'@'、'_'、'-'、'!'以内范围的字符</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">管理员密码</label>
					<div class="col-md-9">
							<input type="password" name="pw" class="form-control" value="" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label" style="color:red">新密码</label>
					<div class="col-md-9">
							<input type="password" name="pw2" class="form-control" value="" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label" style="color:red">确认密码</label>
					<div class="col-md-9">
							<input type="password" name="pw3" class="form-control" value="" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label"></label>
					<div class="col-md-9">
						<input name="submit" type="submit" value="保存" class="btn btn-primary min-width" />
						<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
					</div>
				</div>
			</form>
		</div>
	</div>
	<script type="text/javascript">
	function formcheck(form) {
		if (!form['name'].value) {
			util.message('请填写管理员帐号！');
			form['name'].focus();
			return false;
		}
		if (!form['pw'].value) {
			util.message('请填写管理员密码！');
			form['pw'].focus();
			return false;
		}
		if (!form['pw2'].value) {
			util.message('请填写新密码！');
			form['pw2'].focus();
			return false;
		}
		if (form['pw'].value == form['pw2'].value) {
			util.message('新密码与原密码一致，请检查！');
			form['pw'].focus();
			//return false;
		}
		if (form['pw2'].value.length < 6 ) {
			util.message('管理员密码不得小于6个字符！');
			form['pw2'].focus();
			return false;
		}
		if (form['pw2'].value != form['pw3'].value) {
			util.message('两次输入的新密码不一致，请重新输入！');
			form['pw2'].focus();
			return false;
		}
	}
	</script>
<?php  } else { ?>
<div class="panel panel-default">
	<?php  if($extendfields) { ?>
	<div class="panel-heading">基本资料</div>
	<div class="panel-body">
		<form action="" class="form-horizontal form" method="post" enctype="multipart/form-data">
		<?php  if(is_array($extendfields)) { foreach($extendfields as $item) { ?>
			<?php  if($item['field']=='birthyear') { ?>
			<div class="form-group">
				<label class="col-md-2 control-label"><?php  echo $item['title'];?>：<?php  if($item['required'] == 2) { ?><span style="color:red">*</span><?php  } ?></label>
				<div class="col-md-10">
					<?php  echo tpl_fans_form($item['field'],$profile['birth']);?>
				</div>
			</div>
			<?php  } else if($item['field']=='resideprovince') { ?>
			<div class="form-group">
				<label class="col-md-2 control-label"><?php  echo $item['title'];?>：<?php  if($item['required'] == 2) { ?><span style="color:red">*</span><?php  } ?></label>
				<div class="col-md-10">
					<?php  echo tpl_fans_form($item['field'],$profile['reside']);?>
				</div>
			</div>
			<?php  } else { ?>
			<div class="form-group">
				<label class="col-md-2 control-label"><?php  echo $item['title'];?>：<?php  if($item['required'] == 2) { ?><span style="color:red">*</span><?php  } ?></label>
				<div class="col-md-10">
					<?php  echo tpl_fans_form($item['field'], $profile[$item['field']]);?>
				</div>
			</div>
			<?php  } ?>
		<?php  } } ?>
			<div class="form-group">
				<label class="col-md-2 control-label"></label>
				<div class="col-md-9">
					<button type="submit" class="btn btn-primary span3 min-width" name="submit" value="保存">保存</button>
					<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
				</div>
			</div>
		</form>
	</div>
	<?php  } ?>
</div>

<?php  } ?>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>

<div class="panel panel-default">
	<div class="panel-heading">更新缓存</div>
	<div class="panel-body">
		<form action="" method="post" class="form-horizontal" role="form">
			<div class="form-group">
				<label class="col-md-2 control-label">缓存类型</label>
				<div class="col-sm-10">
					<label for="type_data" class="checkbox-inline">
						<input type="checkbox" name="type[]" value="data" id="type_data" checked="checked" /> 数据缓存
					</label>
					<label for="type_template" class="checkbox-inline">
						<input type="checkbox" name="type[]" value="template" id="type_template" checked="checked" /> 模板缓存
					</label>
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-offset-2 col-md-10">
					<input name="submit" type="submit" value="保存" class="btn btn-primary min-width" />
					<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
				</div>
			</div>
		</form>
	</div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
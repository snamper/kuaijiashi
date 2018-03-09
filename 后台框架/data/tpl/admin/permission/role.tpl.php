<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<ul class="nav nav-tabs">
	<li <?php  if($do == 'display') { ?>class="active"<?php  } ?>><a href="<?php  echo url('permission/role/display');?>">角色管理</a></li>
	<li <?php  if($do == 'post' && empty($id)) { ?>class="active"<?php  } ?>><a href="<?php  echo url('permission/role/post');?>">添加角色</a></li>
	<?php  if($do == 'post' && !empty($id)) { ?><li class="active"><a href="<?php  echo url('permission/role/post');?>">编辑角色</a></li><?php  } ?>
</ul>
<?php  if($do == 'display') { ?>
	<div class="panel panel-default">
		<div class="panel-body table-responsive">
			<table class="table table-hover">
				<thead>
					<tr>
						<th style="width:100px;">角色ID</th>
						<th style="width:150px;">角色名称</th>
						<th style="width:150px;">角色备注</th>
						<th style="width:150px;">开启状态</th>
						<th style="width:300px;">操 作</th>
					</tr>
				</thead>
				<tbody>
				<?php  if(is_array($roles)) { foreach($roles as $role) { ?>
				<tr>
					<td><?php  echo $role['id'];?></td>
					<td><?php  echo $role['name'];?></td>
					<td><?php  echo $role['remark'];?></td>
					<td>
						<input class="role" role-id="<?php  echo $role['id'];?>" type="checkbox" name="status[<?php  echo $role['id'];?>]" value="<?php  echo $role['status'];?>" <?php  if($role['status'] == 2) { ?>checked="checked"<?php  } ?> />
				
					</td>
					<td>
						<a href="<?php  echo url('permission/role', array('do' => 'post','id' => $role['id']))?>" title="编辑">编辑</a>
						- 
						<a href="javascript:;" class="js-delete-role" role-id="<?php  echo $role['id'];?>" title="删除">删除</a>
						
					</td>
				</tr>
				<?php  } } ?>
				</tbody>
			</table>
		</div>
	</div>
	<script>
		require(['jquery.ui', 'bootstrap.switch'], function($) {
			$(function() {
				// 启用状态
				$('.role:checkbox').bootstrapSwitch({onText: '启用', offText: '禁用'});
				$('.role:checkbox').on('switchChange.bootstrapSwitch', function(event, state){
					var role_id = $(this).attr('role-id');
					var status = state ? 2 : 1;
					$.post("<?php  echo url('permission/role/status')?>", {id : role_id, status : status}, function (data){
						if (!data.errno) {
							util.tips(data.message, 2000);
						};
					}, 'json');
				});
				$('.js-delete-role').off('click');
				$('.js-delete-role').click(function(e) {
					e.stopPropagation();
					$this = $(this);
					var role_id = $(this).attr('role-id');
					util.nailConfirm(this, function(state) {
						if(!state) return;
						$.post("<?php  echo url('permission/role/delete')?>", {id : role_id}, function(data){
							console.log(data);
							if(!data.errno){
								$this.parent().parent().remove();
							};
							util.tips(data.message, 2000);
						}, 'json');
					});
				});
			});
		});
	</script>

<?php  } else if($do == 'post') { ?>

<div class="clearfix">
	<form method="post" action="" onsubmit="return chkfrm();" name="frm" class="form-horizontal">
		<div class="panel panel-default" id="step1">
			<div class="panel-heading">
				角色添加
			</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-md-2 control-label"><span class="text-danger">*</span> 角色名称：</label>
					<div class="col-md-9">
						<input class="form-control" type="text" name="name" value="<?php  echo $role['name']?>" id="name" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label"><span class="text-danger">*</span> 角色描述：</label>
					<div class="col-md-9">
						<input class="form-control" type="text" name="remark" value="<?php  echo $role['remark']?>" id="remark" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label"><span class="text-danger">*</span> 开启关闭：</label>
					<div class="col-md-9">
						<label class="radio-inline"><input type="radio" name="status" value="2" <?php  if($role['status'] == 2) { ?>checked="checked"<?php  } else if(empty( $role['status'])) { ?>checked="checked"<?php  } ?> />开启</label>
						<label class="radio-inline"><input type="radio" name="status" value="1" <?php  if($role['status'] == 1) { ?>checked="checked"<?php  } ?> />关闭</label>
					</div>
				</div>
			</div>
		</div>
		<style>
			.select-role tr:first-child td:first-child{border-top:none;}
			label{font-weight:normal;}
		</style>
		<div class="panel panel-default select-role">
			<div class="panel-heading"><label class="checkbox-inline"><input class="js-check-all" type="checkbox" value="" name="" />勾选拥有的权限</label></div>
			<div class="panel-body table-responsive">
				<table class="table table-hover">
					<tbody>
					<?php  if(is_array($nodes)) { foreach($nodes as $node) { ?>
					<tr>
						<td>
							<div class="checkbox">
								<label><input type="checkbox" name="node_ids[]" value="<?php  echo $node['id'];?>" gid="<?php  echo $node['id'];?>" class="js-grandfather js-gid-<?php  echo $node['id'];?>" <?php  if(in_array($node['id'], $role['node_ids'])) { ?>checked="checked"<?php  } ?>/><?php  echo $node['name'];?></label>
							</div>
						</td>
					</tr>
					<?php  if(is_array($node['children'])) { foreach($node['children'] as $child) { ?>
					<tr>
						<td class="text-left">
							<div style="padding-left:50px;background:url('./resource/images/bg_repno.gif') no-repeat -245px -545px;">
								<div class="checkbox">
									<label><input type="checkbox" name="node_ids[]" value="<?php  echo $child['id'];?>" gid="<?php  echo $node['id'];?>" fid="<?php  echo $child['id'];?>" class="js-father js-gid-<?php  echo $node['id'];?> js-fid-<?php  echo $child['id'];?>" <?php  if(in_array($child['id'], $role['node_ids'])) { ?>checked="checked"<?php  } ?>/><?php  echo $child['name'];?></label>
								</div>
							</div>
						</td>
					</tr>
					<?php  if(!empty($child['children'])) { ?>
					<tr>
						<td class="text-left line-feed">
							<div style="padding-left:50px;margin-left:50px; background:url('./resource/images/bg_repno.gif') no-repeat -245px -545px;">
								<?php  if(is_array($child['children'])) { foreach($child['children'] as $grandson) { ?>
								<div class="checkbox-inline">
									<label><input type="checkbox" name="node_ids[]" value="<?php  echo $grandson['id'];?>" gid="<?php  echo $node['id'];?>" fid="<?php  echo $child['id'];?>" cid="<?php  echo $grandson['id'];?>" class="js-child js-gid-<?php  echo $node['id'];?> js-fid-<?php  echo $child['id'];?>" <?php  if(in_array($grandson['id'], $role['node_ids'])) { ?>checked="checked"<?php  } ?> /><?php  echo $grandson['name'];?></label>
								</div>
								<?php  } } ?>
							</div>
						</td>
					</tr>
					<?php  } ?>
					<?php  } } ?>
					<?php  } } ?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="col-md-offset-1 col-md-10">
			<input type="hidden" name="id" value="<?php  echo $role['id'];?>">
			<input name="submit" id="submit" type="submit" value="保存" class="btn btn-primary min-width">
			<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
		</div>
	</form>
</div>

<script type="text/javascript">
	$('.js-check-all').click(function() {
		var checked = this.checked;

		$(this).parent().parent().next().find('input:checkbox').each(function() {
			this.checked = checked;
		});
	});
	$('.js-grandfather').click(function() {
		var checked = this.checked;
		var gid = $(this).attr('gid');
		$('.js-gid-'+gid).each(function(){
			this.checked = checked;
		});
	});

	$('.js-father').click(function() {
		var checked = this.checked;
		var gid = $(this).attr('gid');
		var fid = $(this).attr('fid');
		
		$('.js-child.js-fid-'+fid).each(function(){
			this.checked = checked;
		});
		
		checked = $('.js-father.js-fid-'+fid+':checked, .js-child.js-gid-'+gid+':checked').length > 0;
		$('.js-grandfather.js-gid-'+gid).each(function(){
			this.checked = checked;
		});
	});

	$('.js-child').click(function() {
		var fid = $(this).attr('fid');
		var gid = $(this).attr('gid');
		
		var fatherchecked = $('.js-child.js-fid-'+fid+':checked').length > 0;
		$('.js-father.js-fid-'+fid).each(function(){
			this.checked = fatherchecked;
		});
		
		var grandfatherchecked = $('.js-father.js-gid-'+gid+':checked, .js-child.js-gid-'+gid+':checked').length > 0;
		$('.js-grandfather.js-gid-'+gid).each(function(){
			this.checked = grandfatherchecked;
		});
	});
	function chkfrm(){
		if($('#name').val()==''){
			util.message('角色名不能为空！');
			$('#name').focus();
			return false;
		}
	}
</script>
<?php  } ?>

<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
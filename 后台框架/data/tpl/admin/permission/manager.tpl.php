<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<ul class="nav nav-tabs">
	<li <?php  if($do == 'display') { ?>class="active"<?php  } ?>><a href="<?php  echo url('permission/manager/display');?>">管理列表</a></li>
	<li <?php  if($do == 'post' && empty($id)) { ?>class="active"<?php  } ?>><a href="<?php  echo url('permission/manager/post');?>">添加管理员</a></li>
	<?php  if($do == 'post' && !empty($id)) { ?><li class="active"><a href="<?php  echo url('permission/manager/post', array('id'=>$id));?>">编辑管理员</a></li><?php  } ?>
</ul>
<?php  if($do == 'display') { ?>
<div class="panel panel-default">
	<div class="panel-body table-responsive">
		<table class="table table-hover">
			<thead>
			<tr>
				<th style="width:80px;">ID</th>
				<th style="width:120px;">用户名</th>
				<th style="width:120px;">角色</th>
				<th style="width:150px;">上次登陆时间</th>
				<th style="width:150px;">上次登陆IP</th>
				<th class="text-center" style="width:150px;">状态</th>
				<th style="width:150px;">操作</th>
			</tr>
			</thead>
			<tbody>
			<?php  if(is_array($users)) { foreach($users as $user) { ?>
			<tr>
				<td><?php  echo $user['uid'];?></td>
				<td><?php  echo $user['username'];?></td>
				<td><?php  echo $user['role'];?></td>
				<td><?php  echo date('Y-m-d H:i:s',$user['lastvisit']);?></td>
				<td><?php  echo $user['lastip'];?></td>
				<td class="text-center" >
					<?php  if(empty($user['isfounder'])) { ?>
					<input class="user" user-id="<?php  echo $user['uid'];?>" type="checkbox" name="status[<?php  echo $user['uid'];?>]" value="<?php  echo $user['status'];?>" <?php  if($user['status'] == 2) { ?>checked="checked"<?php  } ?> />
					<?php  } ?>
				</td>
				<td>
					<?php  if($_W['user']['role_id'] == 1 || empty($user['isfounder'])) { ?>
					<a href="<?php  echo url('permission/manager/post', array('id' => $user['uid']));?>" title="编辑">编辑</a>
					<?php  } else { ?>
					<span class="text-muted">编辑</span>
					<?php  } ?>
					<?php  if($user['uid']!=1 || $user['uid'] != $_W['uid']) { ?>
					- <a href="javascript:;" class="js-delete-user" user-id="<?php  echo $user['uid'];?>" title="删除">删除</a>
					<?php  } ?>
				</td>
			</tr>
			<?php  } } ?>
			</tbody>
		</table>
	</div>
</div>
<script>
	require(['jquery.ui', 'bootstrap.switch'], function($, $) {
		$(function() {
			// 启用状态
			$('.user').bootstrapSwitch({onText: '启用', offText: '禁用'});
			$('.user').on('switchChange.bootstrapSwitch', function(event, state){
				var user_id = $(this).attr('user-id');
				var status = state ? 2 : 1;
				$.post("<?php  echo url('permission/manager/status')?>", {id : user_id, status : status}, function (data){
					util.tips(data.message, 2000);
				}, 'json');
			});
			
			$('.js-delete-user').click(function(e) {
				e.stopPropagation();
				$this = $(this);
				var user_id = $(this).attr('user-id');
				util.nailConfirm(this, function(state) {
					if(!state) return;
					$.post("<?php  echo url('permission/manager/delete')?>", {ajax : true, id : user_id}, function(data){
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
	<form method="post" action="" name="checkform" class="form-horizontal">
		<div class="panel panel-default" id="step1">
			<div class="panel-heading"><?php  if(empty($_GPC['id'])) { ?>添加用户<?php  } else { ?>编辑用户<?php  } ?></div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-md-2 control-label"><span class="text-danger">*</span> 用户(手机号)：</label>
					<div class="col-md-9">
						<input class="form-control" type="text" name="user[username]" value="<?php  if(!empty($_GPC['id'])) { ?><?php  echo $user['username'];?><?php  } ?>" <?php  if(!empty($_GPC['id'])) { ?>disabled="disabled"<?php  } ?> id='managername' />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label"><span class="text-danger">*</span> 密码：</label>
					<div class="col-md-9">
						<input class="form-control" type="password" value="" name="user[password]" id="managerpwd" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label"><span class="text-danger">*</span> 确认密码：</label>
					<div class="col-md-9">
						<input class="form-control" type="password" value="" name="repassword" id="managerpwd2" />
					</div>
				</div>
				<?php  if($user['role_id'] != 1) { ?>
				<div class="form-group">
					<label class="col-md-2 control-label">备注：</label>
					<div class="col-md-9">
						<input class="form-control" type="text" value="<?php  echo $user['remark'];?>" name="user[remark]" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">开启关闭：</label>
					<div class="col-md-9">
						<label class="radio-inline"><input type="radio" name="user[status]" value="2" <?php  if($user['status']==2) { ?>checked="checked"<?php  } ?> />开启</label>
						<label class="radio-inline"><input type="radio" name="user[status]" value="1" <?php  if($user['status']!=2) { ?>checked="checked"<?php  } ?>/>关闭</label>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">所属角色：</label>
					<div class="col-md-9">
					<?php  if(is_array($roles)) { foreach($roles as $role) { ?>
						<label class="radio-inline"><input type="radio" name="user[role_id]" value="<?php  echo $role['id'];?>" <?php  if($user['role_id'] == $role['id'] || empty($user['role_id'])) { ?>checked="checked"<?php  } ?>/><?php  echo $role['name'];?></label>
					<?php  } } ?>
					</div>
				</div>
				<?php  } ?>
				<div class="form-group">
					<label class="col-md-2 control-label"></label>
					<div class="col-md-9">
						<input type="hidden" name='user[uid]' value="<?php  echo $user['uid'];?>">
						<input name="submit" id="submit" type="submit" value="保存" class="btn btn-primary min-width">
						<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
					</div>
				</div>
			</div>
		</div>

		<?php  if(is_array($roles)) { foreach($roles as $role) { ?>
		<div class="panel panel-default js-role-<?php  echo $role['id'];?> js-role" <?php  if($user['role_id'] != $role['id']) { ?>style="display: none;"<?php  } ?>>
			<div class="panel-body table-responsive">
			<table class="table table-hover table-bordered " >
				<thead>
					<th style="width:20%; text-align:center;">模块</th>
					<th style="width:20%;">应用</th>
					<th>功能点列表</th>
				</thead>
				<tbody>
				<?php  if(is_array($role['all'])) { foreach($role['all'] as $key => $grandfather_node) { ?>
					<?php  if(!empty($grandfather_node['children'])) { ?>
						<?php  if(is_array($grandfather_node['children'])) { foreach($grandfather_node['children'] as $j => $father_node) { ?>
						<tr>
							<?php  if($j == 0) { ?>
							<td class="text-center" rowspan='<?php  echo count($grandfather_node['children'])?>'><?php  echo $grandfather_node['name'];?></td>
							<?php  } ?>
							<td>
								<?php  echo $father_node['name'];?>
							</td>
							<td class="line-feed">
								<?php  if(!empty($father_node['children'])) { ?>
									<?php  if(is_array($father_node['children'])) { foreach($father_node['children'] as $k => $child_node) { ?>
									<?php  echo $child_node['name'];?>、
									<?php  } } ?>
								<?php  } ?>
							</td>
						</tr>
						<?php  } } ?>
					<?php  } else { ?>
						<tr>
							<td class="text-center"><?php  echo $grandfather_node['name'];?></td>
							<td></td>
							<td></td>
						</tr>
					<?php  } ?>
				<?php  } } ?>
				</tbody>
				<tbody style="border-top: 1px solid #ddd;">
					<tr></tr>
				</tbody>
			</table>
		</div>
		</div>
		<?php  } } ?>
		
	</form>
</div>
<script type="text/javascript">
$(function(){
	var user = <?php  if(!empty($user)) { ?>true<?php  } else { ?>false<?php  } ?>;
	$('input[name="user[role_id]"]').each(function(){
		$('.js-role').hide();
		$('.js-role-'+this.value).show();
	});
	$('input[name="user[role_id]"]').click(function(){
		$('.js-role').hide();
		$('.js-role-'+this.value).show();
	});
	$('[name="checkform"]').submit(function() {
		if($('#managername').val()==''){
			util.tips('管理员手机不能为空！');
			$('#managername').focus();
			return false;
		}
		var re = /^1\d{10}$/;
		if (!user) {
			if (!re.test($('#managername').val())) {
				util.tips('填写手机号有误');
				return false;
			}
		}
		if (!user) {
			if($('#managerpwd').val()==''){
				util.tips('密码名不能为空！');
				$('#managerpwd').focus();
				return false;
			}
			if($('#managerpwd').val()!= $('#managerpwd2').val()){
				util.tips('两次输入的密码不一样！');
				return false;
			}
		}
	});
});
</script>
<?php  } ?>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>

<ul class="nav nav-tabs">
	<li <?php  if($do == 'display') { ?>class="active"<?php  } ?>><a href="<?php  echo url('mc/member/display');?>">会员列表</a></li>
	<?php  if($do == 'edit') { ?><li class="active"><a href="<?php  echo url('mc/member/edit', array('uid' => $uid));?>">编辑会员资料</a></li><?php  } ?>
</ul>

<?php  if($do=='display') { ?>
<div class="panel panel-info">
	<div class="panel-heading">筛选</div>
	<div class="panel-body">
		<form action="./index.php" method="get" class="form-horizontal" role="form">
		<input type="hidden" name="c" value="mc">
		<input type="hidden" name="a" value="member">
		<input type="hidden" name="do" value="display">
			<div class="form-group">
				<label class="col-md-2 control-label">手机号码</label>
				<div class="col-md-8">
					<input type="text" class="form-control" name="mobile" class="" value="<?php  echo $_GPC['mobile'];?>" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">邮箱</label>
				<div class="col-md-8">
					<input type="text" class="form-control" name="email" value="<?php  echo $_GPC['email'];?>" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">昵称/姓名</label>
				<div class="col-md-8">
					<input type="text" class="form-control" name="username" value="<?php  echo $_GPC['username'];?>" />
				</div>
				<div class="pull-right col-md-2">
					<button class="btn btn-default"><i class="fa fa-search"></i> 搜索</button>
				</div>
			</div>
		</form>
	</div>
</div>

<form method="post" class="form-horizontal" id="form1">
	<div class="panel panel-default ">
		<div class="table-responsive panel-body">
			<table class="table table-hover">
				<input type="hidden" name="do" value="delete" />
				<thead class="navbar-inner">
					<tr>
						<th style="width:80px;" class="hidden">
							<input type="checkbox" id="checkall" onclick="var ck = this.checked;$(':checkbox').each(function(){this.checked = ck});">
							<label for="checkall">选择</label>
						</th>
						<th style="width:80px;">Uid</th>
						<th style="width:120px;">手机</th>
						<th style="width:120px;">昵称</th>
						<th style="width:120px;">真实姓名</th>
						<th style="width: 120px">用户类型</th>
						<th style="width:130px;">注册时间</th>
						<th></th>
						<th style="width:120px;">操作</th>
					</tr>
				</thead>
				<tbody>
				<?php  if(is_array($members)) { foreach($members as $member) { ?>
					<tr>
						<td class="hidden"><input type="checkbox" name="uid[]" value="<?php  echo $member['uid'];?>"></td>
						<td><?php  echo $member['uid'];?></td>
						<td><?php  if($member['mobile']) { ?><?php  echo $member['mobile'];?><?php  } else { ?>-<?php  } ?></td>
						<td><?php  if($member['nickname']) { ?><?php  echo $member['nickname'];?><?php  } else { ?>-<?php  } ?></td>
						<td><?php  if($member['realname']) { ?><?php  echo $member['realname'];?><?php  } else { ?>-<?php  } ?></td>
						<td><?php  if($member['role'] == '1') { ?>管理员<?php  } else if($member['role'] == '2') { ?>普通用户<?php  } else { ?>-<?php  } ?></td>
						<td><?php  echo date('Y-m-d H:i',$member['createtime'])?></td>
						<td></td>
						<td>
							<a href="<?php  echo url('mc/member/edit',array('uid' => $member['uid']))?>">
								编辑
							</a>
							-
							<a href="<?php  echo url('mc/creditmanage/display',array('keyword' => $member['uid'], 'type'=>1))?>" target="_blank">
								充值
							</a>
						</td>
					</tr>
				<?php  } } ?>
				<tr>
					<td class="hidden">
						<input type="checkbox" id="checkall" onclick="var ck = this.checked;$(':checkbox').each(function(){this.checked = ck});">
						<label for="checkall">选择</label>
					</td>
					<td colspan="8">
						<input name="token" type="hidden" value="<?php  echo $_W['token'];?>" />
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
	<?php  echo $pager;?>
</form>

<script>
	require(['jquery'], function($){
		$('#form1').submit(function(){
			if($(":checkbox[name='uid[]']:checked").size() > 0){
				return confirm('删除后不可恢复，您确定删除吗？');
			}
			util.message('请选择要删除的会员', '', 'error');
			return false;
		});
	});
</script>

<?php  } ?>

<?php  if($do == 'edit') { ?>
<div class="main">
	<form action="" class="form-horizontal form" method="post" enctype="multipart/form-data">
		<div class="panel panel-default">
			<div class="panel-heading">修改密码</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-md-2 control-label">新密码</label>
					<div class="col-md-9">
						<input type="password" class="form-control" name="newpwd" value="" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">重复新密码</label>
					<div class="col-md-9">
						<input type="password" class="form-control" name="rnewpwd" value="" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label"></label>
					<div class="col-md-9">
						<a href="javascript:;" id="updatepwd" class="btn btn-primary">修改密码</a>
						<span class="label"></span>
					</div>
				</div>
			</div>
		</div>

		<div class="panel panel-default">
			<input type="hidden" name="uid" value="<?php  echo $uid;?>" />
			<input type="hidden" name="fanid" value="<?php  echo $_GPC['fanid'];?>" />
			<input type="hidden" name="email_effective" value="<?php  echo $profile['email_effective'];?>" />
			<div class="panel-heading">
				基本资料
			</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-md-2 control-label">头像</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('avatar', $member['avatar']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">昵称</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('nickname',$member['nickname']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">真实姓名</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('realname',$member['realname']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">性别</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('gender',$profile['gender']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">角色</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('role',$member['role']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">生日</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('birth',array('year' => $profile['birthyear'],'month' => $profile['birthmonth'],'day' => $profile['birthday']));?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">户籍</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('reside', array('province' => $profile['resideprovince'],'city' => $profile['residecity'],'district' => $profile['residedist']));?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">详细地址</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('address',$profile['address']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">手机</label>
					<div class="col-md-9">
						<input type="text" readonly="readonly" value="<?php  echo $member['mobile'];?>" class="form-control">
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">QQ</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('qq',$profile['qq']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">Email</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('email',$member['email']);?>
					</div>
				</div>
			</div>
		</div>


		<div class="panel panel-default">
			<div class="panel-heading">联系方式</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-md-2 control-label">固定电话</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('telephone',$profile['telephone']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">MSN</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('msn',$profile['msn']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">阿里旺旺</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('taobao',$profile['taobao']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">支付宝帐号</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('alipay',$profile['alipay']);?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">教育情况</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-md-2 control-label">学号</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('studentid',$profile['studentid']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">班级</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('grade',$profile['grade']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">毕业学校</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('graduateschool',$profile['graduateschool']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">学历</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('education',$profile['education']);?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">工作情况</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-md-2 control-label">公司</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('company',$profile['company']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">职业</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('occupation',$profile['occupation']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">职位</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('position',$profile['position']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">年收入</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('revenue',$profile['revenue']);?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">个人情况</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-md-2 control-label">星座</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('constellation',$profile['constellation']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">生肖</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('zodiac',$profile['zodiac']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">国籍</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('nationality',$profile['nationality']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">身高</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('height',$profile['height']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">体重</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('weight',$profile['weight']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">血型</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('bloodtype',$profile['bloodtype']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">身份证号</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('idcard',$profile['idcard']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">邮编</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('zipcode',$profile['zipcode']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">个人主页</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('site',$profile['site']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">情感状态</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('affectivestatus',$profile['affectivestatus']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">交友目的</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('lookingfor',$profile['lookingfor']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">自我介绍</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('bio',$profile['bio']);?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-2 control-label">兴趣爱好</label>
					<div class="col-md-9">
						<?php  echo tpl_fans_form('interest',$profile['interest']);?>
					</div>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-12">
				<button type="submit" class="btn btn-primary min-width" name="submit" value="保存">保存</button>
				<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
			</div>
		</div>
	</form>
</div>

<script type="text/javascript">
	require(['jquery'], function($){
		$('#updatepwd').click(function() {
			var newpwd = $("input[name='newpwd']").val();
			var rnewpwd = $("input[name='rnewpwd']").val();
			if (newpwd.length < 6) {
				util.tips('密码不得少于6个字符', 3000);
				return false;
			}
			if (newpwd != rnewpwd) {
				util.tips('两次输入的密码不一致', 3000);
				return false;
			}
			var uid = <?php  echo $uid;?>;
			$.post(location.href, { uid : uid, password : newpwd}, function(data){
				var $updatepwd = $('#updatepwd').next();
				if (data.errno) {
					util.tips(data.message, 3000);
					$updatepwd.removeClass().addClass('label label-danger').text('密码修改失败');
				} else {
					$updatepwd.removeClass().addClass('label label-success').text('密码修改成功');
				};
				$("input[name='newpwd']").val('');
				$("input[name='rnewpwd']").val('');
				setTimeout(function(){
					$updatepwd.empty();
				}, 2000);
			});

		});
	});
</script>

<?php  } ?>

<?php  if($do == 'add') { ?>
<form action="" class="form-horizontal form" method="post">
	<div class="panel panel-default">
		<div class="panel-heading">添加用户</div>
		<div class="panel-body">
			<div class="form-group">
				<label class="col-md-2 control-label">用户名</label>
				<div class="col-md-9">
					<input type="text" class="form-control" name="member[username]" value="" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">密码</label>
				<div class="col-md-9">
					<input type="password" class="form-control" name="member[password]" value="" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">重复密码</label>
				<div class="col-md-9">
					<input type="password" class="form-control" name="member[repassword]" value="" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">手机</label>
				<div class="col-md-9">
					<input type="text" class="form-control" name="member[mobile]" value="" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">邮箱</label>
				<div class="col-md-9">
					<input type="text" class="form-control" name="member[email]" value="" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">昵称</label>
				<div class="col-md-9">
					<input type="text" class="form-control" name="member[nickname]" value="" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">真实姓名</label>
				<div class="col-md-9">
					<input type="text" class="form-control" name="member[realname]" value="" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label"></label>
				<div class="col-md-9">
					<input type="submit" name="submit" class="btn btn-primary" value="添加用户" />
					<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
				</div>
			</div>
		</div>
	</div>
</form>
<?php  } ?>

<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>

<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>

<ul class="nav nav-tabs">
	<li <?php  if($do == 'display') { ?>class="active"<?php  } ?>><a href="<?php  echo url('news/category', array('do' => 'display'))?>">管理分类</a></li>
	<li <?php  if($do == 'post' && empty($id)) { ?>class="active"<?php  } ?>><a href="<?php  echo url('news/category', array('do' => 'post'))?>">添加分类</a></li>
    <?php  if($do == 'post' && !empty($id)) { ?><li class="active"><a href="#">
    编辑分类
    </a></li><?php  } ?>
</ul>

<?php  if($do == 'post') { ?>

<div class="main">

    <form action="" method="post" class="form-horizontal form" enctype="multipart/form-data" id="form1">
        <div class="panel panel-default">
            <div class="panel-heading">分类详细设置</div>
            <div class="panel-body">

				<?php  if(!empty($parentid)) { ?>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">上级分类</label>
					<div class="col-sm-9">
						<input type="text" name="displayorder" class="form-control" value="<?php  echo $parent['name'];?>" disabled />
					</div>
				</div>
				<?php  } ?>
				<div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">排序</label>
                    <div class="col-sm-9">
                        <input type="text" name="displayorder" class="form-control" value="<?php  echo $category['displayorder'];?>" />
                    </div>
				</div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">分类名称</label>
                    <div class="col-sm-9">
                        <input type="text" name="typename" class="form-control" value="<?php  echo $category['name'];?>" />
                    </div>
				</div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">分类描述</label>
                    <div class="col-sm-9">
                        <input type="text" name="description" class="form-control" value="<?php  echo $category['description'];?>" />
                    </div>
				</div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">分类图标</label>
                    <div class="col-sm-9">
                        <?php  echo tpl_form_field_image('icon', $category['icon'])?>
						<span class="help-block">建议尺寸120*120</span>
                    </div>
                </div>
		</div>
		</div>
		<div class="form-group col-sm-12">
			<input type="submit" name="submit" value="提交" class="btn btn-primary col-lg-1" />
			<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
		</div>
    </form>
</div>
<script type="text/javascript" src="../web/resource/components/colorpicker/spectrum.js"></script>
<link type="text/css" rel="stylesheet" href="../web/resource/components/colorpicker/spectrum.css" />
<script type="text/javascript">
<!--
	$(function(){
		colorpicker();
	});
//-->
</script>
<?php  } else if($do == 'display') { ?>

<div class="main">
    <div class="category">
        <form action="" method="post" onsubmit="return formcheck(this)">
			<div class="panel panel-default">
				<div class="panel-body table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th style="width:30px;"></th>
								<th style="width:80px;">排序</th>
								<th style="width:60px;text-align:center;">图片</th>
								<th>分类名称</th>
								<th style="width:300px;">分类描述</th>
								<th style="width:180px;">操作</th>
							</tr>
						</thead>
						<tbody>
			<?php  if(is_array($category)) { foreach($category as $row) { ?>
				<tr>
					<td><?php  if(count($children[$row['id']]) > 0) { ?><a href="javascript:;"><i class="fa fa-chevron-down"></i></a><?php  } ?></td>
					<td><input type="text" class="form-control" name="displayorder[<?php  echo $row['id'];?>]" value="<?php  echo $row['displayorder'];?>"></td>
					<td style="text-align:center;"><img src="<?php  echo tomedia($row['icon'])?>" height="30" class="imgtip" bigimg="<?php  echo tomedia($row['icon'])?>"></td>
					<td>
						<div class="type-parent">
						<?php  echo $row['name'];?>&nbsp;&nbsp;
						<?php  if(empty($row['parentid'])) { ?>
							<a href="<?php  echo url('news/category', array('parentid' => $row['id'], 'do' => 'post'))?>" class="btn btn-default btn-success"><i class="fa fa-plus-circle"></i> 添加子分类</a>
						<?php  } ?>
						</div>
					</td>
                    <td><div class="type-parent"><?php  echo $row['description'];?></div></td>
					<td><a href="<?php  echo url('news/category', array('do' => 'post', 'id' => $row['id']))?>" class="btn btn-default"><i class="fa fa-edit"></i> 编辑</a>&nbsp;&nbsp;<a href="<?php  echo url('news/category', array('do' => 'delete', 'id' => $row['id']))?>" class="btn btn-default" onclick="return confirm('确认删除此分类吗？');return false;"><i class="fa fa-remove"></i> 删除</a></td>
				</tr>
				<?php  if(is_array($children[$row['id']])) { foreach($children[$row['id']] as $item) { ?>
					<tr>
						<td></td>
						<td><input type="text" class="form-control" name="displayorder[<?php  echo $item['id'];?>]" value="<?php  echo $item['displayorder'];?>"></td>
						<td style="text-align:center;"><img src="<?php  echo tomedia($item['icon'])?>" height="30" class="imgtip" bigimg="<?php  echo tomedia($item['icon'])?>"></td>
						<td><div class="type-child">&nbsp;&nbsp;├──&nbsp;<?php  echo $item['name'];?></div></td>
						<td><div class="type-parent"><?php  echo $item['description'];?></div></td>
						<td><a href="<?php  echo url('news/category', array('do' => 'post', 'id' => $item['id'], 'parentid' => $item['parentid']))?>" class="btn btn-default" title="编辑"><i class="fa fa-edit"></i> 编辑</a>&nbsp;&nbsp;<a href="<?php  echo url('news/category', array('do' => 'delete', 'id' => $item['id']))?>" class="btn btn-default" onclick="return confirm('确认删除此分类吗？');return false;" title="删除"><i class="fa fa-remove"></i> 删除</a></td>
					</tr>
				<?php  } } ?>
			<?php  } } ?>
			</tbody>
					</table>
				</div>
           </div>
		<div class="form-group col-sm-12">
						<a href="<?php  echo url('news/category', array('do' => 'post'))?>" class="btn btn-default btn-primary"><i class="fa fa-plus-circle"></i> 添加新分类</a>
						<span class="pull-right"><input name="submit" type="submit" class="btn btn-primary" value="更新分类排序"></span>
						<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
		</div>
        </form>
    </div>
</div>
<?php  } ?>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
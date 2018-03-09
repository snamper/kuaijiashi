<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>

<ul class="nav nav-tabs">
	<li <?php  if($_GPC['do'] == 'display') { ?>class="active"<?php  } ?>><a href="<?php  echo url('system/navigation', array('do'=>'display', 'rid' => $rid))?>">导航</a></li>
	<li <?php  if($_GPC['do'] == 'post') { ?>class="active"<?php  } ?>><a href="<?php  echo url('system/navigation', array('rid' => $rid, 'do' => 'post' ))?>">添加导航</a></li>
</ul>
 <?php  if($operation == 'post') { ?>   
<div class="main">
	<form action="" method="post" class="form-horizontal form"	enctype="multipart/form-data">
		<div class="panel panel-default">
			<div class="panel-heading">
				导航设置
			</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">排序</label>
					<div class="col-sm-9 col-xs-12">
						<input type="text" name="displayorder" class="form-control" value="<?php  echo $navigation['displayorder'];?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span style="color:red">*</span>导航标题</label>
					<div class="col-sm-9 col-xs-12">
						<input type="text" name="title" class="form-control" value="<?php  echo $navigation['title'];?>" />
					</div>
				</div>

				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">导航图片</label>
					<div class="col-sm-9 col-xs-12">
						<?php  echo tpl_form_field_image('thumb', $navigation['thumb'])?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span style="color:red">*</span>导航链接</label>
					<div class="col-sm-9 col-xs-12">
					<div class="input-group ">
						<input type="text" name="link" id="link" class="form-control" value="<?php  echo $navigation['link'];?>" />
						<span class="input-group-btn">
				<button class="btn btn-default btn_sellink" type="button" >选择话题</button>
			</span>
			</div>
			</div>
				</div>
				 <div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">是否显示</label>
					<div class="col-sm-9 col-xs-12">
						<label class='radio-inline'>
							<input type='radio' name='status' value='1' <?php  if($navigation['status']==1) { ?>checked<?php  } ?> /> 是
						</label>
						<label class='radio-inline'>
							<input type='radio' name='status' value='0' <?php  if($navigation['status']==0) { ?>checked<?php  } ?> /> 否
						</label>
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


<div class="modal fade" id="module-info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog" style="width:800px;top:100px;">
			<div class="modal-content">
				<form action="./index.php?c=extension&a=module&do=info&" method="post" enctype="multipart/form-data" class="form-horizontal form" id="form-info">
					<input type="hidden" name="m" value=""/>
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h4>选择话题链接</h4>
					</div>
					<div class="modal-body">
                                        <div class="row">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="keyword" value="" id="keyword" placeholder="请输入话题名称关键字">
                                                <span class="input-group-btn"><button type="button" class="btn btn-default" onclick="search_members();">搜索</button></span>
                                            </div>
                                        </div>
                                        <div id="module-menus" style="padding-top:5px;"><div style="max-height:500px;overflow:auto;min-width:750px;">
			<table class="table table-hover" style="min-width:750px;">
				<tbody id="search_list">  
				</tbody>
			</table>
				    </div></div>
				 </div>
					
				</form>
			</div>
		</div>
</div>


<script type="text/javascript">
function getRows(data){
	var html='';
	for(i=0;i<data.length;i++){
		html+='<tr>';
		html+='<td><img src="'+data[i].topic_icon+'" style="width:30px;height:30px;padding1px;border:1px solid #ccc"> '+data[i].topic_name+'</td>';
		html+='<td></td>';
		html+='<td></td>';
		html+='<td style="width:80px;"><a href="javascript:;" link='+data[i].link+' onclick="select_member(this)">选择</a></td>';
	    html+='</tr>';
	}
	return html;
}
function search_members(){
	var keyword=$("#keyword").val();
	$("#search_list").empty();
	
	$.post(location.href,{keyword:keyword},function(result){
		var html=getRows(result.data);
		$("#search_list").append(html);
	});
}

function select_member(obj){
   $("#link").val($(obj).attr('link'));
   $('#module-info').modal('hide');
}


$(function(){
	$(".btn_sellink").click(function(){
		$('#module-info').modal('show');
	});
});

</script>
<?php  } else if($operation == 'display') { ?>
<div class="main">
	
		<div class="panel panel-default">
			
			<form method="post" class="form-horizontal" id="formfans">
			<input type="hidden" name="op" value="del" />
			<div style="position:relative">
				<div class="panel-body table-responsive">
					<table class="table table-hover" style="position:relative">
					<thead class="navbar-inner">
						<tr>
							<th>ID</th>
							<th>显示顺序</th>					
							<th>标题</th>
							<th>预览</th>
							<th>连接</th>
							<th>状态</th>
							<th >操作</th>
						</tr>
					</thead>
					<tbody>
						<?php  if(is_array($list)) { foreach($list as $navigation) { ?>
							<tr>
								<td><?php  echo $navigation['id'];?></td>
								<td><?php  echo $navigation['displayorder'];?></td>
								<td><?php  echo $navigation['title'];?></td>
								<td><img src="<?php  echo tomedia($navigation['thumb'])?>" width="50"></td>
								<td><?php  echo $navigation['link'];?></td>
								<td><?php  if($navigation['status']) { ?>显示<?php  } else { ?>隐藏<?php  } ?></td>
								<td style="text-align:left;">
									<a href="<?php  echo url('system/navigation', array('do' => 'post', 'id' => $navigation['id'], 'rid' => $rid))?>" data-toggle="tooltip" data-placement="top"  class="btn btn-default btn-sm manage"><i class="fa fa-edit"></i>修改</a>
									<a onclick="return confirm('确认要删除吗？删除将不能恢复！');" href="<?php  echo url('system/navigation', array('do' => 'delete', 'id' => $navigation['id'],'rid' => $rid))?>" data-toggle="tooltip" data-placement="top"  class="btn btn-default btn-sm manage"><i class="fa fa-del"></i>删除</a> 
								</td>
							</tr>
						<?php  } } ?>
						
					</tbody>
					</table>
				</div>
			</div>
			</form>
			<?php  echo $pager;?>
		</div>
    </div>
<?php  } ?>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
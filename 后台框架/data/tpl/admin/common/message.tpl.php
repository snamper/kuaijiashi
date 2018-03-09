<?php defined('IN_IA') or exit('Access Denied');?><?php  define('IN_MESSAGE', true)?>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header-base', TEMPLATE_INCLUDEPATH)) : (include template('common/header-base', TEMPLATE_INCLUDEPATH));?>
<div class="jumbotron clearfix alert alert-<?php  echo $label;?>">
	<div class="icon">
		<i class="fa fa-3x fa-<?php  if($label=='success') { ?>check-circle<?php  } ?><?php  if($label=='danger') { ?>times-circle<?php  } ?><?php  if($label=='info') { ?>info-circle<?php  } ?><?php  if($label=='warning') { ?>exclamation-triangle<?php  } ?>"></i>
	</div>
	<div class="msg">
		<?php  if($type == 'sql') { ?>
			<h3 style="margin-top: 6px;">MYSQL 错误：</h3>
			<div style="word-break:break-all; width: 100%;">
				<?php  echo $msg;?>
			</div>
		<?php  } else { ?>
			<p>
				<?php  if(is_array($msg)) { ?>
					<?php  if(isset($msg['message'])) { ?>
						<?php  echo $msg['message'];?>
					<?php  } ?>
				<?php  } else { ?>
					<?php  echo $msg;?>
				<?php  } ?>
				<?php  if($redirect) { ?>
				<span><a href="<?php  echo $redirect;?>">继续</a></span>
				<script type="text/javascript">
					setTimeout(function () {
						location.href = "<?php  echo $redirect;?>";
					}, 3000);
				</script>
				<?php  } else { ?>
				<span>[<a href="javascript:history.go(-1);">返回</a>] &nbsp; [<a href="./?refresh">首页</a>]</span>
				<?php  } ?>
			</p>
		<?php  } ?>
	</div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
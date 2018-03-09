<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header-base', TEMPLATE_INCLUDEPATH)) : (include template('common/header-base', TEMPLATE_INCLUDEPATH));?>

<link href="./resource/css/navbar.css" rel="stylesheet">



<div class="navbar navbar-default navbar-static-top" role="navigation" style="position:static;">

	<div class="container-fluid">

		<ul class="nav navbar-nav">

			<li>

				<img src="<?php  echo tomedia($_W['setting']['copyright']['logo']);?>" style="height:44px;margin:3px;"/>

			</li>

		</ul>		

		<ul class="nav navbar-nav navbar-right">

			<?php  if(!empty($_W['role']['menus'])) { ?>

		<?php  if(is_array($_W['role']['menus'])) { foreach($_W['role']['menus'] as $top_menu) { ?>

			<?php  if($top_menu['output']) { ?>

			<li class="ext-type <?php  if($top_menu['active']) { ?>active<?php  } ?>"><a href="<?php  echo $_W['siteroot'];?>admin/index.php?<?php  echo $top_menu['url'];?>"><?php  echo $top_menu['title'];?></a></li>

							<?php  } ?>

		<?php  } } ?>

	<?php  } ?>

			<li class="dropdown">

				<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" style="display:block; max-width:185px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; "><i class="fa fa-user"></i>管理员 <b class="caret"></b></a>

				<ul class="dropdown-menu">

					<li><a href="<?php  echo url('system/updatecache');?>"><i class="fa fa-sign-out fa-fw"></i> 更新缓存</a></li>

					<li><a href="<?php  echo url('user/logout');?>"><i class="fa fa-sign-out fa-fw"></i> 退出系统</a></li>

				</ul>

			</li>

		</ul>

	</div>

</div>



<div class="container-fluid">

	<?php  if(defined('IN_MESSAGE')) { ?>

	<div class="jumbotron clearfix alert alert-<?php  echo $label;?>">

		<div class="row">

			<div class="col-xs-12 col-sm-3 col-lg-2">

				<i class="fa fa-5x fa-<?php  if($label=='success') { ?>check-circle<?php  } ?><?php  if($label=='danger') { ?>times-circle<?php  } ?><?php  if($label=='info') { ?>info-circle<?php  } ?><?php  if($label=='warning') { ?>exclamation-triangle<?php  } ?>"></i>

			</div>

			<div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">

				<?php  if(is_array($msg)) { ?>

					<h2>MYSQL 错误：</h2>

					<p><?php  echo cutstr($msg['sql'], 300, 1);?></p>

					<p><b><?php  echo $msg['error']['0'];?> <?php  echo $msg['error']['1'];?>：</b><?php  echo $msg['error']['2'];?></p>

				<?php  } else { ?>

				<h2><?php  echo $caption;?></h2>

				<p><?php  echo $msg;?></p>

				<?php  } ?>

				<?php  if($redirect) { ?>

				<p><a href="<?php  echo $redirect;?>">如果你的浏览器没有自动跳转，请点击此链接</a></p>

				<script type="text/javascript">

					setTimeout(function () {

						location.href = "<?php  echo $redirect;?>";

					}, 3000);

				</script>

				<?php  } else { ?>

					<p>[<a href="javascript:history.go(-1);">点击这里返回上一页</a>] &nbsp; [<a href="./?refresh">首页</a>]</p>

				<?php  } ?>

			</div>

	<?php  } else { ?>

	<div class="row">

		<div class="col-xs-12 col-sm-3 col-lg-2" id="mainsidebar">

            <div class="page-sidebar" id="sidebar">

                <!-- Page Sidebar Header-->

                <div class="sidebar-header-wrapper">

                    <input type="text" class="searchinput" />

                    <i class="searchicon fa fa-search"></i>

                    <div class="searchhelper">搜索预留，未实现</div>

                </div>

                <!-- /Page Sidebar Header -->

                <!-- Sidebar Menu -->

                <ul class="nav sidebar-menu">

					<?php  if(!empty($_W['role']['menus'])) { ?>

					<?php  if(is_array($_W['role']['menus'])) { foreach($_W['role']['menus'] as $top_menu) { ?>

						<?php  if($top_menu['active']) { ?>

						<?php  if(is_array($top_menu['children'])) { foreach($top_menu['children'] as $group_menu) { ?>

							<?php  if($group_menu['output']) { ?>

							    <li class="open">

									<a href="javascript:;" class="menu-dropdown" style="padding:0px;background:#fff;">

										<span class="menu-text"><i class="<?php  echo $group_menu['icon'];?>" style="margin-right:10px;"></i><?php  echo $group_menu['title'];?></span>

									</a>

							<ul class="submenu">

								<?php  if(is_array($group_menu['children'])) { foreach($group_menu['children'] as $menu) { ?>

								<?php  if($menu['output']) { ?>

									<li class="<?php  if($menu['active']) { ?>active<?php  } ?>" onclick="window.location.href = '<?php  echo $_W['siteroot'];?>admin/index.php?<?php  echo $menu['url'];?>';" style="cursor:pointer;">										

										

											<i class="<?php  echo $menu['icon'];?>"></i>

											<span class="menu-text">

											<?php  echo $menu['title'];?>

												<a href="<?php  echo $_W['siteroot'];?>admin/index.php?<?php  echo $menu['extend_url'];?>" title="<?php  echo $menu['extend_title'];?>">

												<?php  if($menu['extend_icon'] && $menu['extend_url']) { ?>										

													<i class="<?php  echo $menu['extend_icon'];?>" style="float:right;line-height:38px;min-width:15px;"></i>

												<?php  } ?>											

												</a>

										</span>	

											

											

									</li>

								<?php  } ?>									

								<?php  } } ?>								

							</ul>

							<?php  } ?>

						<?php  } } ?>

						<?php  } ?>

					<?php  } } ?>

					<?php  } ?>				        </ul>

                                </li>

                            </ul>

                        </li>

                    </in>

                </ul>

                <!-- /Sidebar Menu -->

            </div>			

			<script>

				function InitiateSideMenu() {

					$(".sidebar-toggler").on("click", function() {

						return $("#sidebar").toggleClass("hide"), $(".sidebar-toggler").toggleClass("active"), !1

					});

					var n = $("#sidebar").hasClass("menu-compact");

					$("#sidebar-collapse").on("click", function() {

						$("#sidebar").is(":visible") || $("#sidebar").toggleClass("hide");

						$("#sidebar").toggleClass("menu-compact");

						$(".sidebar-collapse").toggleClass("active");

						n = $("#sidebar").hasClass("menu-compact");

						n && $(".open > .submenu").removeClass("open")

					});

					$(".sidebar-menu").on("click", function(t) {

						var i = $(t.target).closest("a"),

							u, r, f;

						if (i && i.length != 0) {

							if (!i.hasClass("menu-dropdown")) return n && i.get(0).parentNode.parentNode == this && (u = i.find(".menu-text").get(0), t.target != u && !$.contains(u, t.target)) ? !1 : void 0;

							if (r = i.next().get(0), !$(r).is(":visible")) {

								if (f = $(r.parentNode).closest("ul"), n && f.hasClass("sidebar-menu")) return;

								f.find("> .open > .submenu").each(function() {

									this == r || $(this.parentNode).hasClass("active") || $(this).slideUp(200).parent().removeClass("open")

								})

							}

							return n && $(r.parentNode.parentNode).hasClass("sidebar-menu") ? !1 : ($(r).slideToggle(200).parent().toggleClass("open"), !1)

						}

					})

				}



				function addClass(n, t) {

					var i = n.className;

					i && (i += " ");

					n.className = i + t

				}



				function removeClass(n, t) {

					var i = " " + n.className + " ";

					n.className = i.replace(" " + t, "").replace(/^\s+/g, "").replace(/\s+$/g, "")

				}



				function hasClass(n, t) {

					var i = " " + n.className + " ",

						r = " " + t + " ";

					return i.indexOf(r) != -1

				}



				InitiateSideMenu();			

			</script>

		</div>

		<div class="col-xs-12 col-sm-9 col-lg-10">

		<?php  } ?>
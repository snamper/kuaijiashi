<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<div class="clearfix">
    <form action="" method="post" class="form-horizontal" role="form" enctype="multipart/form-data" id="form1">
        <div class="panel panel-default">
            <div class="panel-heading">
                站点信息
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">站点名称</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <input type="text" name="sitename" class="form-control" value="<?php  echo $settings['sitename'];?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">LOGO</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <?php  echo tpl_form_field_image('logo', $settings['logo']);?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">LOGO_XS</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <?php  echo tpl_form_field_image('logo_xs', $settings['logo_xs']);?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">二维码</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <?php  echo tpl_form_field_image('qrcode', $settings['qrcode']);?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">网站关键字</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <textarea name="keywords" class="form-control" cols="70"><?php  echo $settings['keywords'];?></textarea>
                        <p class="help-block">网站关键字。</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">网站描述</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <textarea name="description" class="form-control" cols="70"><?php  echo $settings['description'];?></textarea>
                        <p class="help-block">网站描述。</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">版权说明</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <textarea name="footer" class="form-control" cols="70"><?php  echo $settings['footer'];?></textarea>
                        <p class="help-block">网站底部版权说明。</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">其他设置</div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">版本</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <input type="text" name="version" class="form-control" value="<?php  echo $settings['version'];?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">ossUrl</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <input type="text" name="ossUrl" class="form-control" value="<?php  echo $settings['ossUrl'];?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">押金</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <input type="text" name="pledge" class="form-control" value="<?php  echo $settings['pledge'];?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">联系邮箱</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <input type="text" name="email" class="form-control" value="<?php  echo $settings['email'];?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">客服热线</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <input type="text" name="hotline" class="form-control" value="<?php  echo $settings['hotline'];?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">说明</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <?php  echo tpl_ueditor('protocol', $settings['protocol']);?>
                        <p class="help-block">联系我们中的说明。</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">积分说明</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <?php  echo tpl_ueditor('creditInfo', $settings['creditInfo']);?>
                        <p class="help-block">积分说明。</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">充值赠送说明</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <?php  echo tpl_ueditor('giveInfo', $settings['giveInfo']);?>
                        <p class="help-block">充值赠送说明。</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">用户协议</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <?php  echo tpl_ueditor('registerInfo', $settings['registerInfo']);?>
                        <p class="help-block">用户协议。</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 control-label">押金说明</label>
                    <div class="col-xs-12 col-sm-10 col-md-9">
                        <?php  echo tpl_ueditor('pledgeInfo', $settings['pledgeInfo']);?>
                        <p class="help-block">押金说明。</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-md-offset-2 col-lg-offset-1 col-xs-12 col-sm-10 col-md-10 col-lg-11">
                <input name="submit" type="submit" value="保存" class="btn btn-primary min-width" />
                <input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
            </div>
        </div>
    </form>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>

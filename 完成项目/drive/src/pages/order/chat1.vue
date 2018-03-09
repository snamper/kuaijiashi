
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>订单咨询</title>
	<meta id="wxKeywords" name="Keywords" content="">
	<meta id="wxDescription" name="Description" content="" />
	<meta name="apple-touch-fullscreen" content="YES" />
	<meta name="format-detection" content="telephone=no" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<meta http-equiv="Expires" content="-1" />
	<meta http-equiv="pragram" content="no-cache" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />


	<link rel="stylesheet" type="text/css" href="./Public/js/mobiscroll/css/mobiscroll.css"/>
	<link rel="stylesheet" type="text/css" href="./Public/js/mobiscroll/css/mobiscroll_date.css"/>

	<link rel="stylesheet" href="./Public/etui/css/etui.css?v=m.1.0.15">
	<link rel="stylesheet" href="./Public/css/p_dialog.css?v=m.1.0.15">

	<script type="text/javascript" src="./Public/js/jquery.min.js"></script>
	<script type="text/javascript" src="./Public/js/swfobject.js"></script>
	<script type="text/javascript" src="./Public/js/web_socket.js"></script>
	<script type="text/javascript" src="./Public/js/layer_mobile/layer.js"></script>
	<script type="text/javascript" src="./Public/js/qiniu.js"></script>
	<script type="text/javascript" src="./Public/js/jquery.SuperSlide.2.1.1.js"></script>
	<script src="./Public/js/mobiscroll/js/mobiscroll_date.js" charset="gb2312"></script>
	<script src="./Public/js/mobiscroll/js/mobiscroll.js"></script>
	<script type="text/javascript" src="./Public/js/mobile.chat.js?v=m.1.0.15"></script>

</head>
<body>
<div class="ui-page-group">
	<!-- 单个page ,第一个.page默认被展示-->
	<div class="ui-page ui-page-current">
		<div class="group_title ui-cell">
			<div class="list_btn open-list-btn"><i class="ui-icon icon-liebiao"></i></div>
			<div class="ui-cell-primary" id="title-text-span"></div>
		</div>
		<!--页面内容开始-->
					<div class="ui-content" id="dialog-box-1020884" style="bottom:63px;">

				<div id="dialog-content">
					<!-- 消息内容 -->
				</div>

			</div>
				<!--页面内容结束-->
		<!--底部聊天栏开始-->
		<div class="bottom-board" style="bottom: 0px;" id="send-box">

			<!--语音图标-->
			<div class="bottom-add" id="bottom-func-btn">
				<div class="ui-icon icon-bangbangce-zhankai"></div>
			</div>

			<!--键盘图标-->
			<div class="bottom-keyboard">
				<div class="ui-icon icon-bangbangce-jianpan"></div>
			</div>

			<!--文本输入框-->
			<div class="bottom-text-box">
				<div class="bottom-text-div"></div>
				<textarea class="bottom-text" id="send-msg-content" placeholder="请输入咨询内容..."></textarea>
			</div>

			<!--语音输入按钮-->
			<div class="bottom-voice-btn" id="bottom-voice-btn">按住 说话</div>

			<!--加号按钮-->
			<!--<div class="bottom-add">
				<div class="ui-icon icon-bangbangce-shuohua"></div>
				<div class="ui-icon icon-bangbangce-zhankai"></div>
			</div>-->

			<!--发送按钮-->
			<div class="bottom-sub" id="send-btn">发送</div>

		</div>
		<!--底部聊天栏结束-->
		<!--倒计时开始-->
					<div class="countdown-box" id="count-down-1020884" style="bottom: 80px;">
				<!--时间icon-->
				<div class="ui-icon icon-shijian count-down-click" style="display: block;"></div>
				<div class="countdown-tips count-down-click" style="display: none;">此订单将在
					<span class="countdown-hours">00</span>:
					<span class="countdown-minute">00</span>:
					<span class="countdown-second">00</span>后自动关闭
				</div>
			</div>
				<!--倒计时结束-->

		<!--底部功能窗口开始-->
		<div class="bottom-function clearfix" style="bottom: 0px;opacity: 0;" id="bottom-func-box">
			<div class="function-list send-voice" id="send-voice-btn">
				<div class="ui-icon icon-bangbangce-shuohua"></div>
				<div class="function-word">语音</div>
			</div>
			<div class="function-list" id="upload-img-btn">
				<div class="ui-icon icon-bangbangce-tupian"></div>
				<div class="function-word">图片</div>
			</div>
						<div class="function-list finish-server" id="close-order-btn">
				<div class="ui-icon icon-bangbangce-jieshu"></div>
				<div class="function-word">申请关闭</div>
			</div>
			<!--快捷回复内容-->
			
		</div>
		<!--底部弄能窗口结束-->

		<!--用户端结束服务开始-->
		<div class="finish-server-user" id="finish-server-box" style="display:none;">
			<div class="finish-server-user-tips">
				<span class="ui-icon icon-jieda"></span><span>你的咨询已完成</span>
			</div>
			<div class="ui-row user-bottom-btn">
				<a href="javascript:void(0);" class="ui-col-33 user-appraise">
					立即评价				</a>
				<a href="javascript:void(0);" class="ui-col-33 user-reward">我要打赏</a>
				<a href="javascript:void(0);" class="ui-col-33 master-index">大师主页</a>
			</div>
		</div>
		<!--用户端结束服务结束-->

		<!--大师端结束服务开始-->
		<div class="finish-server-master" id="finish-server-master" style="display:none;">
			<span class="ui-icon icon-jieda"></span>
			<span>你的服务已完成</span>
			<div class="apply-refund">申请退款</div>
		</div>
		<!--大师端结束服务结束-->

		<!--图片放大窗口-->
		<div class="chat-img-pop" style="display: none;">
			<div id="slideBox" class="pop-big-img slideBox">
				<div class="bd"><ul id="image-view"></ul></div>
				<div class="hd">
					<ul></ul>
				</div>
			</div>
		</div>
		<!--图片放大窗口-->

		<!--左侧列表蓝开始-->
		<div class="lt_list" id="left-list-order">
			<div class="mask left-mask-click"></div>
			<div class="list_item">
				<div class="ui-cell list-title">
					<div class="ui-cell-primary">所有咨询</div>
					<div id="backto-dialog"><i class="ui-icon icon-fanhui"></i>返回当前咨询</div>
				</div>
				<!--循环此处列表-->
									<div class="ui-cell lt_list_item lt_active" data-id="1020884">
						<div class="lt_img"><img src="http://of86393ci.bkt.clouddn.com/Common/lmqpwiys/lkzssifu/RpAR5hmyeFXZ?imageView2/2/w/100"/></div>
						<div class="ui-cell-primary" style="width: 60%;">
							<div class="master_info">
								马俊-升学求学								<span class="stauts_r">进行中</span>
							</div>
							<div class="ui-nowrap text_de">请问在吗</div>
						</div>
					</div>
							</div>
		</div>
		<!--左侧列表栏结束-->

					<!--裂变红包开始-->
			<div class="rebPacket" style="display: none;" id="reb-pachet-btn">
				<img src="http://of86393ci.bkt.clouddn.com/Common/lmqpwiys/tzlhpwob/NBCrNiGWWe8k"/>
			</div>
			<!--列表红包结束-->
			<!--裂变红包弹窗开始-->
			<div class="packetmodel" style="display: none;" id="pachet-model">
				<div class="packetmask"></div>
				<div class="packetcontent"><img src="http://of86393ci.bkt.clouddn.com/Common/lmqpwiys/tzlhpwob/wDYMC6WTSNeD"/></div>
			</div>
			<!--裂变红包弹窗结束-->

			<!--小福袋-->
			<div class="packet_f"  style="display: none;">
				<img src="http://of86393ci.bkt.clouddn.com/Common/fmywqtxp/ixoqwqml/R6GTPDnnzmEk"/>
				<div class="money"></div>
			</div>
			<!--小福袋-->

			<!--福袋-->
			<div class="lucky" style="display: none;">
				<div class="luckymask"></div>
				<div class="luckycontent">
					<div class="lucky_box">
						<img src="http://of86393ci.bkt.clouddn.com/Common/fmywqtxp/ixoqwqml/AE3feP2enfGC"/>
						<div class="lucky_tx">
							恭喜您获得双12福袋<br />
							<span></span>
						</div>
					</div>
					<div class="lucky_btn"><img src="http://of86393ci.bkt.clouddn.com/Common/fmywqtxp/ixoqwqml/yJZ7mp5ZGK7D"/></div>
					<div class="lucky_btn_tx">不需要，直接领取<i class="ui-icon icon-arrow-circle-o-right"></i></div>
				</div>
			</div>
			<!--福袋-->

			<!--邀请好友弹窗-->
			<div class="sharemodel" style="display: none;">
				<div class="sharemask"></div>
				<div class="sharecontent">
					<img src="http://m.bangbangce.com/Template/Wechat/Yidake/Assets/Images/Public/Recommend_m.png" alt="">
				</div>
			</div>
			<!--邀请好友弹窗-->

			<!--领取福袋弹窗-->
			<div class="rec_lucky" style="display: none;">
				<div class="rec_luckymask"></div>
				<div class="rec_luckycontent">
					<div class="rec_lucky_box">
						<img src="http://of86393ci.bkt.clouddn.com/Common/fmywqtxp/ixoqwqml/AE3feP2enfGC"/>
						<div class="rec_lucky_tx">
							双12福袋已成功翻倍<br />
							<span></span>
							<div>已存入你的账户</div>
						</div>
					</div>
					<div class="close_box"><i class="ui-icon icon-close"></i></div>
				</div>
			</div>
			<!--领取福袋弹窗-->
				
		<!--语音录制弹窗开始-->
		<div class="voice-pop" style="display: none;">
			<div class="voice-pop-box">
				<div class="record-icon ui-icon icon-oayuyin"></div>
				<div class="voice-pop-tips">手指上滑，取消发送</div>
			</div>
		</div>
		<!--语音录制弹窗结束-->
	</div>
	<script src="./Public/js/moxie.min.js"></script>
	<script src="./Public/js/plupload.dev.js"></script>
	<script src="./Public/js/zh_CN.js"></script>
	<script>
		document.domain = "bangbangce.com";
		var userid = '286550',
			orderid = '1020884',
			orderInfo = {"1020884":{"id":"1020884","title":"\u5347\u5b66\u6c42\u5b66","status":2,"expireTime":"1515770849","lastMsg":"\u8bf7\u95ee\u5728\u5417","columnid":"11","dataid":"286550","adminid":"45163","orderno":"A18011023271617479","comment":"2","addtime":"1515598049","isOld":2,"isFill":1,"userRead":2,"masterRead":1,"ordertitle":"\u9a6c\u4fca-\u5347\u5b66\u6c42\u5b66","openid":"ollqgsxA1hZ2UlX5NIDh8mLbxhKE","userNick":"Changqing","userHeadpic":"headpic_b6ed410115b310e3","masterNick":"\u9a6c\u4fca","masterHeadpic":"Common\/lmqpwiys\/lkzssifu\/RpAR5hmyeFXZ","isRefund":0}},
			role = '2',
			qiniuDomain = 'http://of86393ci.bkt.clouddn.com/',
			token = 'i69aUGPJOEYvpGjDVsv5kTjSHH42nZ_3iQd6uuLv:Pbr4z4IfLkD1w5Uiu4RyTHiMgnM=:eyJzY29wZSI6ImJhbmd3b2NlIiwiZGVhZGxpbmUiOjE1MTU2MDE4MTl9',
			nowTime = '1515598219',
			restUrl = 'http://www.bangbangce.com/rest.php/',
			parentUrl = 'http://m.bangbangce.com/',
			websocketAddr='wss:chat.bangbangce.com';
				</script>

</div>
</body>
</html>
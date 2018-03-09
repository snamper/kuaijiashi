<template>
    <div>
        <v-header title="打赏">
            <div slot="left" class="item" flex="main:center cross:center" v-on:click="$router.go(-1)">
                <svg class="svg" aria-hidden="true">
                    <use xlink:href="#icon-back"></use>
                </svg>
            </div>
            <div slot="right" class="item" flex="main:center cross:center" v-on:click="$router.push('/home/index')">
                <svg class="svg" aria-hidden="true">
                    <use xlink:href="#icon-home"></use>
                </svg>
            </div>
        </v-header>
        <v-content bottomHeight="0">
            <div class="ui-page-group">
                <!-- 单个page ,第一个.page默认被展示-->
                <div class="ui-page ui-page-current">
                    <!--页面内容开始-->
                    <div class="ui-content">
                        <!--用户信息开始-->
                        <div class="ui-cells reward-title">
                            <!--头像-->
                            <input type="hidden" name="userid" value="57054" id="userid">
                            <!--<div>
                        <img src="http://of86393ci.bkt.clouddn.com//System/Member_0.jpg"/>
                    </div>-->
                            <div style="background-size: cover;" :style="[{'background': 'url(' + coach.avatar + ') no-repeat'}]"></div>
                            <!--昵称-->
                            <div>{{coach.realname}}</div>
                            <!--感言-->
                            <div>"感谢你的真诚帮助，我要支付一定的金额作为心意"</div>
                        </div>
                        <!--用户信息结束-->
                        <!--打赏内容开始-->
                        <div class="ui-cells">
                            <div class="ui-cell">打赏金额</div>
                            <div class="reward-prize">
                                <span @click="toPay(5)">￥5</span>
                                <span @click="toPay(10)">￥10</span>
                                <span @click="toPay(20)">￥20</span>
                                <span @click="toPay(50)">￥50</span>
                                <div class="ui-row custom-prize">
                                    <div class="ui-col-50">自定义金额</div>
                                    <div class="ui-search-input ui-col-50">
                                        <label class="ui-icon iconfont icon-renminbi" for="prize"></label>
                                        <input type="number" v-model="money">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="rewar-box">
                                <div class="ui-row">
                                    <a class="ui-col-100 active" href="javascript:" @click="toPay(money)">确定支付</a>
                                </div>
                                <div class="ui-row rewar-bottom">
                                    <div class="ui-col-50">
                                        <span class="color-text">{{coach.rewardCount}}</span>人打赏了老师
                                    </div>
                                    <div class="ui-col-50">
                                        打赏总金额<span class="color-text">￥{{coach.rewardSum ? coach.rewardSum : 0}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--打赏内容结束-->
                    </div>
                    <!--页面内容结束-->
                </div>
            </div>
        </v-content>
    </div>
</template>
<script>
var coachRewardCss = require('!style-loader/useable!css-loader!../../assets/css/coach/reward.css');
let coachRewardVue;
import util from 'util'
import is from 'is'
import wx from 'weixin-js-sdk'
export default {
    computed: {
        userInfo() {
            return this.$store.state.app.userInfo || {
                profile: []
            };
        }
    },
    data() {
        return {
            id: this.$route.params.id,
            coach: [],
            money: 0,
            client_type: 'app',
            payType: 'wechat',
        }
    },
    created: function() {
        coachRewardVue = this;
    },
    methods: {
        init() {
            util.post('api.php?entry=app&c=coach&a=reward&do=display', {
                id: coachRewardVue.id
            }, (rs) => {
                if (rs.status == 1) {
                    coachRewardVue.coach = rs.data;
                    if (util.isWechat()) {
                        let params = {
                            debug: false,
                            url: window.location.href.split('#')[0],
                        };
                        util.getJsConfig(params, (err, obj) => {
                            if (err) {
                                return util.toast(err);
                            }

                            console.log('jsconfig ', obj);

                            wx.config(obj);

                            wx.ready(() => {
                                console.log('wx.ready');
                            });

                            wx.error(function(res) {
                                console.log('wx err', res);
                                //可以更新签名
                            });
                        });
                    }
                }
            });
        },
        toPay: function(money) {
            if (!is.empty(coachRewardVue.userInfo.uid)) {
                coachRewardVue.money = money;
                if (coachRewardVue.payType == 'wechat') {
                    if (!util.isCordova()) {
                        coachRewardVue.client_type = 'wechat';
                    }
                    coachRewardVue.wechatpay();
                } else {
                    coachRewardVue.alipay();
                }
            } else {
                coachRewardVue.$store.commit('set_login', true);
            }
        },
        wechatpay: function() {
            coachRewardVue.$Confirm({
                title: '是否优先使用余额支付?',
                content: '<p style="text-align:center;">余额为' + coachRewardVue.userInfo.credit1 + '元</p>',
                confirm: function() {
                    coachRewardVue.wechatpayCall(1);
                },
                cancel: function() {
                    coachRewardVue.wechatpayCall(0);
                },
                btn1: "否",
                btn2: "是"
            });
        },
        wechatpayCall(useCredit1) {
            util.post('api.php?entry=app&c=coach&a=reward&do=getParams', {
                to_uid: coachRewardVue.coach.uid,
                pid: coachRewardVue.coach.id,
                type: coachRewardVue.client_type,
                useCredit1: useCredit1,
                money: coachRewardVue.money
            }, (rs) => {
                if (rs.status == 1) {
                    if (rs.data == 'credit1') {
                        setTimeout(function() {
                            //更新打赏金额
                            coachRewardVue.redbagBoxShow = false;
                            coachRewardVue.otherRedmoneyBoxShow = false;
                            util.updateUserInfo();
                            coachRewardVue.init();
                        }, 1000);
                        return false;
                    }
                    if (coachRewardVue.client_type == 'app') {
                        var params = {
                            appid: rs.data.appid,
                            partnerid: rs.data.partnerid, // mch_id
                            prepayid: rs.data.prepayid, // prepay id
                            package: rs.data.package, // package
                            noncestr: rs.data.noncestr, // nonce
                            timestamp: rs.data.timestamp, // timestamp
                            sign: rs.data.sign, // signed string
                        };
                        Wechat.sendPaymentRequest(params, function() {
                            util.toast('支付成功');
                            setTimeout(function() {
                                //更新打赏金额
                                util.updateUserInfo();
                                coachRewardVue.init();
                            }, 1000);
                            return false;
                        }, function(reason) {
                            util.toast("Failed: " + reason);
                        });
                    } else if (coachRewardVue.client_type == 'wechat') {
                        coachRewardVue.callpay(rs.data);
                    }
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('获取数据失败')
            });
        },
        alipay: function() {
            util.toast('暂未提供支付宝付款方式');
        },
        callpay(A) {
            if (typeof WeixinJSBridge == "undefined") {
                if (document.addEventListener) {
                    document.addEventListener("WeixinJSBridgeReady", coachRewardVue.jsApiCall, false)
                } else {
                    if (document.attachEvent) {
                        document.attachEvent("WeixinJSBridgeReady", coachRewardVue.jsApiCall);
                        document.attachEvent("onWeixinJSBridgeReady", coachRewardVue.jsApiCall)
                    }
                }
            } else {
                coachRewardVue.jsApiCall(A)
            }
        },
        jsApiCall(A) {
            WeixinJSBridge.invoke("getBrandWCPayRequest", A, function(B) {
                if (B.err_msg) {
                    if (B.err_msg == "get_brand_wcpay_request:ok") {
                        //支付成功
                        util.toast('支付成功');
                        setTimeout(function() {
                            coachRewardVue.redbagBoxShow = false;
                            coachRewardVue.otherRedmoneyBoxShow = false;
                            util.updateUserInfo();
                            coachRewardVue.init();
                        }, 1000);
                        return false;
                    } else {
                        if (B.err_msg == "get_brand_wcpay_request:cancel") {
                            util.toast('您取消了支付');
                        } else {
                            util.toast(B.err_desc);
                        }
                    }
                } else {
                    util.toast(B.errMsg);
                }
            })
        },
    },
    mounted: function() {
        this.$nextTick(function() {
            coachRewardCss.use();
            coachRewardVue.init();
        });
    },
    destroyed: function() {
        coachRewardCss.unuse();
    }
}
</script>

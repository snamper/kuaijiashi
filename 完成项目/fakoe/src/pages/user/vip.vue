<template>
    <div>
        <v-header title="我的VIP">
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
            <div class="page">
                <div class="user_vip_head center">
                    <div class="user_vip_author">
                        <img :src="userInfo.avatar ? userInfo.avatar : loadImg()" width="34" height="34" class="head-round">
                        <div class="name">{{userInfo.nickname}}</div>
                        <p class="px12" style="opacity: .6;">有效期至：{{userInfo.is_vip == 1 ? userInfo.viptime : '您还不是vip哦'}}</p>
                    </div>
                </div>
                <!--head end-->
                <div class="user_vip_function">
                    <div class="list-title">选择购买方案</div>
                    <div class="list-box" style="display: block;background-color: #FFF;">
                        <div class="ui-cell select" v-for="(row, index) in vips" :class="index == select ? 'active' : ''" @click="selectVip(index)">
                            <div class="ui-cell-primary">{{row.name}}</div>
                            <div>￥{{row.price}}</div>
                        </div>
                    </div>
                    <div class="ui-cell btn-bottom">
                        <div class="ui-cell-primary">
                            <a style="display: block; width: 100%; margin-left: 0px; color: rgb(255, 255, 255);" href="javascript:" @click="toPay">立即购买</a>
                        </div>
                    </div>
                </div>
            </div>
        </v-content>
    </div>
</template>
<script>
var userVipCss = require('!style-loader/useable!css-loader!../../assets/css/user/vip.css');
let userVipVue;
import util from 'util'
import is from 'is'

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
            vips: [],
            select: 0,
            client_type: 'app',
            payType: 'wechat'
        }
    },
    created: function() {
        userVipVue = this;
    },
    methods: {
        init() {
            util.post('api.php?entry=app&c=user&a=vip&do=display', {}, (rs) => {
                if (rs.status == 1) {
                    userVipVue.vips = rs.data;
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
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            })
        },
        loadImg() {
            return require('../../assets/images/avatar.png');
        },
        selectVip(index) {
            userVipVue.select = index;
        },
        toPay: function() {
            if (userVipVue.payType == 'wechat') {
                if (!util.isCordova()) {
                    userVipVue.client_type = 'wechat';
                }
                userVipVue.wechatpay();
            } else {
                userVipVue.alipay();
            }
        },
        wechatpay: function() {
            userVipVue.$Confirm({
                title: '是否优先使用余额支付?',
                content: '<p style="text-align:center;">余额为' + userVipVue.userInfo.credit1 + '元</p>',
                confirm: function() {
                    userVipVue.wechatpayCall(1);
                },
                cancel: function() {
                    userVipVue.wechatpayCall(0);
                },
                btn1: "否",
                btn2: "是"
            });
        },
        wechatpayCall(useCredit1) {
            util.post('api.php?entry=app&c=user&a=vip&do=getParams', {
                money: userVipVue.vips[userVipVue.select].price,
                type: userVipVue.client_type,
                useCredit1: useCredit1,
            }, (rs) => {
                if (rs.status == 1) {
                    if (rs.data == 'credit1') {
                        util.toast(rs.message);
                        setTimeout(function() {
                            userVipVue.init();
                            util.updateUserInfo();
                        }, 1000);
                        return false;
                    }
                    if (userVipVue.client_type == 'app') {
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
                                userVipVue.init();
                                util.updateUserInfo();
                            }, 1000);
                            return false;
                        }, function(reason) {
                            util.toast("Failed: " + reason);
                        });
                    } else if (userVipVue.client_type == 'wechat') {
                        userVipVue.callpay(rs.data);
                    }
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('获取数据失败')
            })
        },
        alipay: function() {
            util.toast('暂未提供支付宝付款方式');
        },
        callpay(A) {
            if (typeof WeixinJSBridge == "undefined") {
                if (document.addEventListener) {
                    document.addEventListener("WeixinJSBridgeReady", userVipVue.jsApiCall, false)
                } else {
                    if (document.attachEvent) {
                        document.attachEvent("WeixinJSBridgeReady", userVipVue.jsApiCall);
                        document.attachEvent("onWeixinJSBridgeReady", userVipVue.jsApiCall)
                    }
                }
            } else {
                userVipVue.jsApiCall(A)
            }
        },
        jsApiCall(A) {
            WeixinJSBridge.invoke("getBrandWCPayRequest", A, function(B) {
                if (B.err_msg) {
                    if (B.err_msg == "get_brand_wcpay_request:ok") {
                        //支付成功
                        util.toast('支付成功');
                        setTimeout(function() {
                            userVipVue.init();
                            util.updateUserInfo();
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
            userVipCss.use();
            userVipVue.init();
        });
    },
    destroyed: function() {
        userVipCss.unuse();
    }
}
</script>

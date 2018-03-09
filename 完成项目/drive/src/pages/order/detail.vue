<template>
    <div>
        <v-header title="订单详情">
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
                        <!--进度条开始-->
                        <div class="ui-cells">
                            <div class="ui-row track-nav">
                                <div class="ui-col-25"><span :class="detail.status >= 1 ? 'active' : ''">选择教练</span></div>
                                <div class="ui-col-25"><span :class="detail.status >= 1 ? 'active' : ''">确定订单</span></div>
                                <div class="ui-col-25"><span :class="detail.status >= 2 ? 'active' : ''">完成支付</span></div>
                                <div class="ui-col-25"><span :class="detail.status >= 3 ? 'active' : ''">订单完成</span></div>
                            </div>
                            <div class="ui-cell straight">
                                <span :class="detail.status >= 1 ? 'line-active' : ''" style="width: 20%;"></span>
                                <span :class="detail.status >= 1 ? 'line-active' : ''" style="width: 20%;"></span>
                                <span :class="detail.status >= 2 ? 'line-active' : ''" style="width: 20%;"></span>
                                <span :class="detail.status >= 3 ? 'line-active' : ''" style="width: 20%;"></span>
                                <span :class="detail.status >= 4 ? 'line-active' : ''" style="width: 20%;"></span>
                                <span class="ui-cell-primary"></span>
                            </div>
                        </div>
                        <!--进度条结束-->
                        <!--订单信息开始-->
                        <div class="ui-cells">
                            <div class="ui-cell title">订单信息</div>
                            <div class="ui-cell ui-cells-access">
                                <!--大师头像-->
                                <div class="master-img">
                                    <img class="width100" :src="detail.avatar">
                                </div>
                                <!--大师项目价钱-->
                                <div class="master-prize ui-cell-primary">
                                    <div>{{detail.name}}</div>
                                    <div class="color-prize">￥{{detail.total}}</div>
                                </div>
                            </div>
                            <div class="ui-cell ui-cells-access" v-if="detail.status == 2">
                                <span class="ui-cell-primary">手机</span>
                                <span>{{detail.mobile}}</span>
                            </div>
                            <div class="ui-cell ui-cells-access">
                                <span class="ui-cell-primary">订单编号</span>
                                <span>{{detail.ordersn}}</span>
                            </div>
                            <div class="ui-cell ui-cells-access">
                                <span class="ui-cell-primary">下单时间</span>
                                <span>{{detail.createtime}}</span>
                            </div>
                        </div>
                        <!--订单信息结束-->
                        <!--支付信息开始-->
                        <div class="ui-cells">
                            <div class="ui-cell title">支付信息</div>
                            <div class="ui-cell ui-cells-access">
                                <span class="ui-cell-primary">订单金额</span>
                                <span class="color-prize">￥{{detail.total}}</span>
                            </div>
                            <!-- 普通优惠券开始 -->
                            <a class="ui-cell ui-cells-access cell-lianjie" href="javascript:;" @click="selectCoupon">
                                <div class="ui-cell-primary confirm-coupon">
                                    <span>优惠金额</span>
                                </div>
                                <span class="color-prize">{{coupon.name}}￥{{coupon.value}}</span>
                                <svg class="svg" aria-hidden="true">
                                    <use xlink:href="#icon-right"></use>
                                </svg>
                            </a>
                            <!-- 普通优惠券结束 -->
                            <div class="ui-cell ui-cells-access">
                                <span class="ui-cell-primary">实付金额</span>
                                <span class="color-prize">￥{{detail.total - coupon.value}}</span>
                            </div>
                        </div>
                        <!--支付信息结束-->
                    </div>
                    <!--页面内容结束-->
                    <!--底部悬浮支付按钮-->
                    <div class="btn-qdzf">
                        <a href="javascript:;" @click="toPay" v-if="detail.status == 1 && detail.uid == userInfo.uid">确定支付</a>
                        <a href="javascript:;" @click="finish" v-if="detail.status == 2 && detail.uid == userInfo.uid">结束订单</a>
                        <router-link :to="'/order/evaluate/' + detail.id" v-if="(userInfo.role == 1 && detail.status == 3) || (userInfo.role == 2 && detail.status == 4)">评价</router-link>
                    </div>
                </div>
            </div>
        </v-content>
    </div>
</template>
<script>
var orderDetailCss = require('!style-loader/useable!css-loader!../../assets/css/order/detail.css');
let orderDetailVue;
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
            coupon_id: this.$route.params.coupon_id,
            detail: [],
            coupon: {
                id: 0,
                name: '',
                value: 0
            },
            is_use_coupon: false,
            client_type: 'app',
            payType: 'wechat'
        }
    },
    created: function() {
        orderDetailVue = this;
    },
    methods: {
        init() {
            util.post('api.php?entry=app&c=order&a=detail&do=display', {
                id: orderDetailVue.id
            }, (rs) => {
                if (rs.status == 1) {
                    orderDetailVue.detail = rs.data.detail;
                    if (orderDetailVue.detail.status == 1) {
                        orderDetailVue.getCoupon();
                    }
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
            });
        },
        toPay: function() {
            if (orderDetailVue.payType == 'wechat') {
                if (!util.isCordova()) {
                    orderDetailVue.client_type = 'wechat';
                }
                orderDetailVue.wechatpay();
            } else {
                orderDetailVue.alipay();
            }
        },
        wechatpay: function() {
            orderDetailVue.$Confirm({
                title: '是否优先使用余额支付?',
                content: '<p style="text-align:center;">余额为' + orderDetailVue.userInfo.credit1 + '元</p>',
                confirm: function() {
                    orderDetailVue.wechatpayCall(1);
                },
                cancel: function() {
                    orderDetailVue.wechatpayCall(0);
                },
                btn1: "否",
                btn2: "是"
            });
        },
        wechatpayCall(useCredit1) {
            util.post('api.php?entry=app&c=order&a=pay&do=getParams', {
                id: orderDetailVue.id,
                type: orderDetailVue.client_type,
                coupon_id: orderDetailVue.coupon_id,
                useCredit1: useCredit1,
            }, (rs) => {
                if (rs.status == 1) {
                    if (rs.data == 'credit1') {
                        util.toast(rs.message);
                        setTimeout(function() {
                            orderDetailVue.init();
                        }, 1000);
                        return false;
                    }
                    if (orderDetailVue.client_type == 'app') {
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
                                orderDetailVue.init();
                            }, 1000);
                            return false;
                        }, function(reason) {
                            util.toast("Failed: " + reason);
                        });
                    } else if (orderDetailVue.client_type == 'wechat') {
                        orderDetailVue.callpay(rs.data);
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
                    document.addEventListener("WeixinJSBridgeReady", orderDetailVue.jsApiCall, false)
                } else {
                    if (document.attachEvent) {
                        document.attachEvent("WeixinJSBridgeReady", orderDetailVue.jsApiCall);
                        document.attachEvent("onWeixinJSBridgeReady", orderDetailVue.jsApiCall)
                    }
                }
            } else {
                orderDetailVue.jsApiCall(A)
            }
        },
        jsApiCall(A) {
            WeixinJSBridge.invoke("getBrandWCPayRequest", A, function(B) {
                if (B.err_msg) {
                    if (B.err_msg == "get_brand_wcpay_request:ok") {
                        //支付成功
                        util.toast('支付成功');
                        setTimeout(function() {
                            orderDetailVue.init();
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
        getCoupon() {
            util.post('api.php?entry=app&c=order&a=detail&do=getCoupon', {
                coupon_id: orderDetailVue.coupon_id
            }, (rs) => {
                if (rs.status == 1) {
                    orderDetailVue.coupon = rs.data;
                    orderDetailVue.is_use_coupon = true;
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            });
        },
        selectCoupon() {
            if (orderDetailVue.detail.status == 1) {
                orderDetailVue.$router.push('/user/coupon/' + orderDetailVue.id);
            }
        },
        finish() {
            orderDetailVue.$Confirm('确认结束该订单?', function() {
                util.post('api.php?entry=app&c=order&a=detail&do=finish', {
                    id: orderDetailVue.id
                }, (rs) => {
                    if (rs.status == 1) {
                        util.toast(rs.message);
                        orderDetailVue.init();
                    } else {
                        util.toast(rs.message);
                    }
                }, () => {
                    util.toast('数据传输失败,请重试')
                });
            });
        }
    },
    mounted: function() {
        this.$nextTick(function() {
            orderDetailCss.use();
            orderDetailVue.init();
        });
    },
    destroyed: function() {
        orderDetailCss.unuse();
    }
}
</script>

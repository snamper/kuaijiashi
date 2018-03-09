<template>
    <div>
        <v-header title="创建需求">
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
                        <!--第一步-->
                        <div>
                            <div class="info-box">
                                <div class="ui-cell textarea">
                                    <div class="ui-cell-lt">详细需求</div>
                                    <div class="ui-cell-primary">
                                        <textarea class="changeInput" rows="4" v-model="description" placeholder="请填写您的需求"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--第一步结束-->
                        <!--按钮-->
                        <div class="btn" @click="submit">提交需求</div>
                    </div>
                    <!--页面内容结束-->
                </div>
            </div>
        </v-content>
    </div>
</template>
<script>
var biddingCreateCss = require('!style-loader/useable!css-loader!../../assets/css/coach/recruit.css');
let biddingCreateVue;
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
            id: 0,
            cid: this.$route.params.cid,
            description: '',
            client_type: 'app',
            payType: 'wechat'
        }
    },
    created: function() {
        biddingCreateVue = this;
    },
    methods: {
        submit() {
            if (!biddingCreateVue.description) {
                util.toast('请填写具体需求');
                return false;
            }
            util.post('api.php?entry=app&c=bidding&a=create&do=post', {
                cid: biddingCreateVue.cid,
                description: biddingCreateVue.description
            }, (rs) => {
                if (rs.status == 1) {
                    biddingCreateVue.id = rs.data;
                    biddingCreateVue.toPay();
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            })
        },
        toPay: function() {
            if (biddingCreateVue.payType == 'wechat') {
                if (!util.isCordova()) {
                    biddingCreateVue.client_type = 'wechat';
                }
                biddingCreateVue.wechatpay();
            } else {
                biddingCreateVue.alipay();
            }
        },
        wechatpay: function() {
            biddingCreateVue.$Confirm({
                title: '是否优先使用余额支付?',
                content: '<p style="text-align:center;">余额为' + biddingCreateVue.userInfo.credit1 + '元</p>',
                confirm: function() {
                    biddingCreateVue.wechatpayCall(1);
                },
                cancel: function() {
                    biddingCreateVue.wechatpayCall(0);
                },
                btn1: "否",
                btn2: "是"
            });
        },
        wechatpayCall(useCredit1) {
            util.post('api.php?entry=app&c=bidding&a=create&do=getParams', {
                id: biddingCreateVue.id,
                type: biddingCreateVue.client_type,
                useCredit1: useCredit1,
            }, (rs) => {
                if (rs.status == 1) {
                    if (rs.data == 'credit1') {
                        util.toast(rs.message);
                        setTimeout(function() {
                            biddingCreateVue.$router.push('/bidding/list');
                        }, 1000);
                        return false;
                    }
                    if (biddingCreateVue.client_type == 'app') {
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
                                biddingCreateVue.$router.push('/bidding/list');
                            }, 1000);
                            return false;
                        }, function(reason) {
                            util.toast("Failed: " + reason);
                        });
                    } else if (biddingCreateVue.client_type == 'wechat') {
                        biddingCreateVue.callpay(rs.data);
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
                    document.addEventListener("WeixinJSBridgeReady", biddingCreateVue.jsApiCall, false)
                } else {
                    if (document.attachEvent) {
                        document.attachEvent("WeixinJSBridgeReady", biddingCreateVue.jsApiCall);
                        document.attachEvent("onWeixinJSBridgeReady", biddingCreateVue.jsApiCall)
                    }
                }
            } else {
                biddingCreateVue.jsApiCall(A)
            }
        },
        jsApiCall(A) {
            WeixinJSBridge.invoke("getBrandWCPayRequest", A, function(B) {
                if (B.err_msg) {
                    if (B.err_msg == "get_brand_wcpay_request:ok") {
                        //支付成功
                        util.toast('支付成功');
                        setTimeout(function() {
                            biddingCreateVue.$router.push('/bidding/list');
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
            biddingCreateCss.use();
        });
    },
    destroyed: function() {
        biddingCreateCss.unuse();
    }
}
</script>

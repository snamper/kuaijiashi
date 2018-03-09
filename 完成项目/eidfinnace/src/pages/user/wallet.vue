<template>
    <div>
        <v-header title="我的钱包">
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
        <v-content @scroll="scrollEvent">
            <hui-cell-group>
                <hui-pullrefresh :callback="refresh" ref="refreshRef">
                    <hui-infinitescroll :callback="loadMore" ref="loadMoreRef">
                        <div slot="list">
                            <div class="info-panel">
                                <div class="left-panel">
                                    <div>可用余额&nbsp;<span>{{userInfo.credit1}}</span></div>
                                    <div>可用积分&nbsp;<span>{{userInfo.credit2}}</span></div>
                                </div>
                                <div class="right-panel">
                                    <div>
                                        <hui-button type="warning" size="small" style="background:#01AAAF;" @click.native="showRechargePopup = true">充值</hui-button>
                                        <hui-button type="primary" size="small" @click.native="showWithDrawPopup = true" style="margin-left:10px;">提现</hui-button>
                                    </div>
                                </div>
                            </div>
                            <div class="tabs" :class="[(stick ? 'stick' : '')]">
                                <div class="up" :class="type == 'paylog' ? 'cur' : ''" @click="changeTab('paylog')">
                                    微信支付
                                </div>
                                <div class="down" :class="type == 'credit' ? 'cur' : ''" @click="changeTab('credit')">
                                    系统支付
                                </div>
                                <div class="down" :class="type == 'withdraw' ? 'cur' : ''" @click="changeTab('withdraw')">
                                    提现记录
                                </div>
                            </div>
                            <hui-cell-group v-if="records.length">
                                <hui-cell-item v-for="(row, index) in records" :key="row.id">
                                    <div slot="left">
                                        <p class="wordhide">{{row.remark}}</p>
                                        <p>{{row.createtime}}</p>
                                    </div>
                                    <div slot="right" style="color:red;">{{row.type == 1 ? row.money : '-' + row.money}}</div>
                                </hui-cell-item>
                            </hui-cell-group>
                        </div>
                    </hui-infinitescroll>
                </hui-pullrefresh>
            </hui-cell-group>
            <hui-backtop></hui-backtop>
        </v-content>
        <hui-popup position="right" :show.sync="showRechargePopup">
            <v-header title="充值">
                <div slot="left" class="item" flex="main:center cross:center" v-on:click="showRechargePopup = false">
                    <svg class="svg" aria-hidden="true">
                        <use xlink:href="#icon-back"></use>
                    </svg>
                </div>
            </v-header>
            <v-content bottomHeight="0">
                <div class="recharge_content">
                    <div class="recharge_info">
                        <div class="recharge_money">我的余额：{{userInfo.credit1}}元</div>
                        <ul class="recharge_list cb">
                            <li class="recharge_list_item" @click="selectMoney('200')" :class="money == '200' ? 'actived' : ''">
                                <p class="recharge_money">充200元</p>
                                <p class="send_money"></p>
                            </li>
                            <li class="recharge_list_item" @click="selectMoney('100')" :class="money == '100' ? 'actived' : ''">
                                <p class="recharge_money">充100元</p>
                                <p class="send_money"></p>
                            </li>
                            <li class="recharge_list_item" @click="selectMoney('50')" :class="money == '50' ? 'actived' : ''">
                                <p class="recharge_money">充50元</p>
                                <p class="send_money"></p>
                            </li>
                            <li class="recharge_list_item" @click="selectMoney('10')" :class="money == '10' ? 'actived' : ''">
                                <p class="recharge_money">充10元</p>
                                <p class="send_money"></p>
                            </li>
                        </ul>
                    </div>
                    <div class="instantRecharge">
                        <!-- <a class="recharge" href="success.html">立即充值</a> -->
                        <a class="recharge" href="javascript:;" @click="toPay">立即充值</a>
                        <div class="rechargeTips">点击立即充值，即表示您已经同意<span @click="showRegisterInfo">《用户协议》</span></div>
                    </div>
                </div>
            </v-content>
        </hui-popup>
        <hui-popup position="bottom" :show="showWithDrawPopup" @on-sync-show="showWithDrawPopup = false" height="50%">
            <div class="center-middle">
                <hui-cell-group>
                    <hui-cell-item>
                        <span slot="left">提现金额：</span>
                        <input slot="right" type="text" placeholder="请输入提现金额" v-model="money" style="text-align: left!important;">
                    </hui-cell-item>
                    <hui-cell-item>
                        <span slot="left">支付宝账号：</span>
                        <input slot="right" type="text" placeholder="请输入支付宝账号" v-model="zhifubao" style="text-align: left!important;">
                    </hui-cell-item>
                    <hui-cell-item>
                        <span slot="left">微信账号：</span>
                        <input slot="right" type="text" placeholder="请输入微信账号" v-model="wechat" style="text-align: left!important;">
                    </hui-cell-item>
                </hui-cell-group>
                <div class="operate">
                    <button @click="showWithDrawPopup = false">
                        取消
                    </button>
                    <button @click="withdraw" class="highlight">
                        确定
                    </button>
                </div>
            </div>
        </hui-popup>
        <hui-popup position="right" :show.sync="showRegisterInfoPopup">
            <v-header title="用户协议">
                <div slot="left" class="item" flex="main:center cross:center" v-on:click="showRegisterInfoPopup = false">
                    <svg class="svg" aria-hidden="true">
                        <use xlink:href="#icon-back"></use>
                    </svg>
                </div>
            </v-header>
            <v-content bottomHeight="0">
                <div style="padding: 15px;" v-html="registerInfo"></div>
            </v-content>
        </hui-popup>
        <v-footer></v-footer>
    </div>
</template>
<script>
let userWalletCss = require('!style-loader/useable!css-loader!../../assets/css/user/wallet.css');
let userWalletVue;
import is from 'is'
import util from 'util'
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
            page: 1,
            records: [],
            showRechargePopup: false,
            money: 0,
            showRegisterInfoPopup: false,
            registerInfo: '',
            client_type: 'app',
            payType: 'wechat',
            stick: false,
            type: 'paylog',
            showWithDrawPopup: false,
            money: '',
            zhifubao: '',
            wechat: ''
        }
    },
    created: function() {
        userWalletVue = this;
        if (util.isWechat()) {
            let params = {
                debug: false,
                url: window.location.href.split('#')[0]
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
    },
    methods: {
        init: function() {
            userWalletVue.refresh();
        },
        loadRecord: function(successFunc, errorFunc) {
            util.post('api.php?entry=app&c=user&a=wallet&do=display', {
                page: userWalletVue.page,
                type: userWalletVue.type
            }, (rs) => {
                if (rs.status == 1) {
                    if (is.function(successFunc)) {
                        successFunc(rs.data.length);
                    }
                    userWalletVue.records.push(...rs.data);
                    userWalletVue.page = userWalletVue.page + 1;
                } else {
                    if (is.function(errorFunc)) {
                        errorFunc();
                    }
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            });
        },
        changeTab: function(type) {
            userWalletVue.$refs.loadMoreRef.$emit('hui.infinitescroll.reInit');
            userWalletVue.type = type;
            userWalletVue.refresh();
        },
        refresh() {
            Promise.resolve().then(function() {
                userWalletVue.page = 1;
                userWalletVue.records = [];
                userWalletVue.loadMore();
            }).then(function() {
                userWalletVue.$refs.refreshRef.$emit('hui.pullrefresh.finishLoad');
            }).catch(function(err) {
                util.toast(err);
            });

        },
        loadMore() {
            userWalletVue.loadRecord(function(length) {
                console.log(length);
                if (length <= 0) {
                    userWalletVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                    return;
                }
                userWalletVue.$refs.loadMoreRef.$emit('hui.infinitescroll.finishLoad');
            }, function() {
                userWalletVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                return;
            });
        },
        showRegisterInfo: function() {
            util.post('api.php?entry=app&c=normal&a=sysInfo', {}, (rs) => {
                if (rs.status == 1) {
                    userWalletVue.registerInfo = rs.data.registerInfo;
                    userWalletVue.showRegisterInfoPopup = true;
                } else {
                    util.toast(rs.message)
                }
            }, () => {
                util.toast('获取数据失败')
            });
        },
        selectMoney(money) {
            userWalletVue.money = money;
            util.updateUserInfo();
        },
        toPay: function() {
            if (!is.empty(userWalletVue.userInfo.uid)) {
                if (userWalletVue.payType == 'wechat') {
                    if (!util.isCordova()) {
                        userWalletVue.client_type = 'wechat';
                    }
                    userWalletVue.wechatpay();
                } else {
                    userWalletVue.alipay();
                }
            } else {
                userWalletVue.$router.push('account/login');
            }
        },
        wechatpay: function() {
            userWalletVue.wechatpayCall();
        },
        wechatpayCall() {
            util.post('api.php?entry=app&c=user&a=wallet&do=getParams', {
                money: userWalletVue.money,
                type: userWalletVue.client_type
            }, (rs) => {
                if (rs.status == 1) {
                    if (userWalletVue.client_type == 'app') {
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
                                userWalletVue.showRechargePopup = false;
                            }, 3000);
                        }, function(reason) {
                            util.toast("Failed: " + reason);
                        });
                    } else if (userWalletVue.client_type == 'wechat') {
                        userWalletVue.callpay(rs.data);
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
                    document.addEventListener("WeixinJSBridgeReady", userWalletVue.jsApiCall, false)
                } else {
                    if (document.attachEvent) {
                        document.attachEvent("WeixinJSBridgeReady", userWalletVue.jsApiCall);
                        document.attachEvent("onWeixinJSBridgeReady", userWalletVue.jsApiCall)
                    }
                }
            } else {
                userWalletVue.jsApiCall(A)
            }
        },
        jsApiCall(A) {
            WeixinJSBridge.invoke("getBrandWCPayRequest", A, function(B) {
                if (B.err_msg) {
                    if (B.err_msg == "get_brand_wcpay_request:ok") {
                        //支付成功
                        util.toast('支付成功');
                        setTimeout(function() {
                            userWalletVue.showRechargePopup = false;
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
        scrollEvent(data) {
            if (data[1] > 46) { //当当前高度大于导航条到窗口顶部高度
                userWalletVue.stick = true;
            } else {
                //如果回到导航条原位置之前则替换回原来的类名
                userWalletVue.stick = false;
            }
        },
        withdraw() {
            util.post('api.php?entry=app&c=user&a=wallet&do=withdraw', {
                money: userWalletVue.money,
                zhifubao: userWalletVue.zhifubao,
                wechat: userWalletVue.wechat
            }, (rs) => {
                if (rs.status == 1) {
                    util.updateUserInfo();
                    userWalletVue.showWithDrawPopup = false;
                    util.toast(rs.message);
                    userWalletVue.refresh();
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            });
        }
    },
    mounted: function() {
        this.$nextTick(function() {
            userWalletCss.use();
            userWalletVue.init();
        })
    },
    destroyed: function() {
        userWalletCss.unuse();
    }
}
</script>

<template>
    <div>
        <v-header background="#f5f5f5" borderBottom="none">
            <div slot="left" class="item" flex="main:center cross:center" v-on:click="$router.go(-1)">
                <svg class="svg" aria-hidden="true">
                    <use xlink:href="#icon-back"></use>
                </svg>
            </div>
        </v-header>
        <v-content topHeight="0" bottomHeight="0">
            <div class="header">
                <img src="../../assets/images/logo.png">
            </div>
            <div class="reg_form">
                <div id="prosx">
                    <div class="ui-cell">
                        <div class="ui-search-input ui-cell-primary">
                            <label class="ui-icon">
                                <svg class="svg" aria-hidden="true">
                                    <use xlink:href="#icon-mobile"></use>
                                </svg>
                            </label>
                            <input type="tel" v-model="mobile" placeholder="请输入手机号码">
                        </div>
                    </div>
                    <div class="ui-cell">
                        <div class="ui-search-input ui-cell-primary">
                            <label class="ui-icon">
                                <svg class="svg" aria-hidden="true">
                                    <use xlink:href="#icon-lock"></use>
                                </svg>
                            </label>
                            <input type="password" v-model="password" placeholder="请输入密码">
                        </div>
                    </div>
                    <div class="ui-cell">
                        <div class="ui-search-input ui-cell-primary">
                            <label class="ui-icon">
                                <svg class="svg" aria-hidden="true">
                                    <use xlink:href="#icon-lock"></use>
                                </svg>
                            </label>
                            <input type="password" v-model="repassword" placeholder="请确认密码">
                        </div>
                    </div>
                    <div class="ui-cell">
                        <div class="ui-search-input ui-cell-primary">
                            <label class="ui-icon">
                                <svg class="svg" aria-hidden="true">
                                    <use xlink:href="#icon-safe"></use>
                                </svg>
                            </label>
                            <input type="text" v-model="code" placeholder="请输入验证码">
                        </div>
                        <div class="yzm-btn">
                            <hui-sendcode slot="right" v-model="sendcodeStart" @click.native="sendCode" class="hs-btn-default" initStr="获取验证码"></hui-sendcode>
                        </div>
                    </div>
                    <div class="zc-btn">
                        <a href="javascript:" @click="register">注册</a>
                    </div>
                </div>
                <p class="p-text">注册即表示你同意
                    <a href="javascript:;" @click="showRegisterInfo">《用户协议》</a>
                </p>
            </div>
        </v-content>
        <hui-popup position="right" :show.sync="showRegisterInfoPopup">
            <v-header title="用户许可及服务协议">
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
    </div>
</template>
<script>
let accountRegisterCss = require('!style-loader/useable!css-loader!../../assets/css/account/login.css');
let accountRegisterVue;
import is from 'is'
import util from 'util'
import base from 'base'
export default {
    computed: {
        get_userInfo() {
            return this.$store.state.app.userInfo;
        },
        token() {
            return this.$store.state.app.token;
        }
    },
    data() {
        return {
            mobile: '',
            password: '',
            repassword: '',
            code: '',
            sendcodeStart: false,
            showRegisterInfoPopup: false,
            registerInfo: '',
            appkey: this.$route.query.appkey
        }
    },
    created: function() {
        accountRegisterVue = this;
        // 检测会员有没有登录
        if (!is.empty(accountRegisterVue.appkey)) {
            // 如果有token 但是vuex中没有用户登录信息则做登录操作
            accountRegisterVue.loginWithAppkey();
            return;
        }
    },
    methods: {
        sendCode() {
            util.post('api.php?entry=app&c=account&a=login&do=sendCode', {
                mobile: accountRegisterVue.mobile
            }, (rs) => {
                if (rs.status == 1) {
                    util.toast(rs.message)
                    accountRegisterVue.sendcodeStart = true;
                } else {
                    util.toast(rs.message)
                }
            }, () => {
                util.toast('操作失败')
                accountRegisterVue.status = false
            });
        },
        register() {
            if ((accountRegisterVue.password != accountRegisterVue.repassword) || is.empty(accountRegisterVue.password)) {
                util.toast('两次密码不一致');
                return false;
            }
            util.post('api.php?entry=app&c=account&a=login&do=register', {
                mobile: accountRegisterVue.mobile,
                password: accountRegisterVue.password,
                code: accountRegisterVue.code
            }, (rs) => {
                if (rs.status == 1) {
                    accountRegisterVue.$store.commit('set_userInfo', rs.data.userInfo);
                    accountRegisterVue.$store.commit('set_token', rs.data.token);
                    util.toast(rs.message);
                    accountLoginVue.$router.push('/user/index');
                } else {
                    util.toast(rs.message)
                }
            }, () => {
                util.toast('操作失败')
                accountRegisterVue.status = false
            });
        },
        showRegisterInfo: function() {
            util.post('api.php?entry=app&c=normal&a=sysInfo', {}, (rs) => {
                if (rs.status == 1) {
                    accountRegisterVue.registerInfo = rs.data.registerInfo;
                    accountRegisterVue.showRegisterInfoPopup = true;
                } else {
                    util.toast(rs.message)
                }
            }, () => {
                util.toast('获取数据失败')
            });
        }
    },
    mounted: function() {
        this.$nextTick(function() {
            accountRegisterCss.use();
        })
    },
    destroyed() {
        accountRegisterCss.unuse();
    }
}
</script>

<template>
    <div>
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
                    <div class="zc-btn">
                        <a href="javascript:" @click="submit">登陆</a>
                    </div>
                </div>
                <div class="login-type">
                    <ul class="login-type-ul">
                        <li class="login-type-item">
                            <a href="javascript:;" @click="wechatLogin">
                                <svg class="svg" aria-hidden="true">
                                    <use xlink:href="#icon-wechat_fill"></use>
                                </svg>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </v-content>
    </div>
</template>
<script>
let accountLoginCss = require('!style-loader/useable!css-loader!../../assets/css/account/login.css');
let accountLoginVue;
import is from 'is'
import util from 'util'
import base from 'base'
export default {
    computed: {
        get_userInfo() {
            return this.$store.state.app.userInfo;
        },
        login() {
            return this.$store.state.login.login;
        },
        token() {
            return this.$store.state.app.token;
        }
    },
    data() {
        return {
            mobile: '',
            password: '',
            appkey: this.$route.query.appkey
        }
    },
    created: function() {
        accountLoginVue = this;
        // 检测会员有没有登录
        if (!is.empty(accountLoginVue.appkey)) {
            // 如果有token 但是vuex中没有用户登录信息则做登录操作
            accountLoginVue.loginWithAppkey();
            return;
        }
        if (!accountLoginVue.login) {
            accountLoginVue.wechatLogin();
        }
    },
    methods: {
        submit() {
            util.post('api.php?entry=app&c=account&a=login', {
                mobile: accountLoginVue.mobile,
                password: accountLoginVue.password
            }, (rs) => {
                if (rs.status == 1) {
                    accountLoginVue.$store.commit('set_userInfo', rs.data.userInfo);
                    accountLoginVue.$store.commit('set_token', rs.data.token);
                    util.toast(rs.message);
                    accountLoginVue.$router.push('/user/index');
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('操作失败');
            });
        },
        loginWithAppkey() {
            util.post('api.php?entry=app&c=user&a=profile', {
                appkey: accountLoginVue.appkey
            }, (rs) => {
                if (rs.status == 1) {
                    accountLoginVue.$store.commit('set_login', true);
                    accountLoginVue.$store.commit('set_userInfo', rs.data.userInfo);
                    accountLoginVue.$store.commit('set_token', rs.data.token);
                    util.toast('微信授权登录成功');
                    accountLoginVue.$router.push('/user/index');
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('微信授权登录失败');
            });
        },
        wechatLogin() {
            let ua = window.navigator.userAgent.toLowerCase();
            if (ua.match(/MicroMessenger/i) == 'micromessenger') {
                // 跳转到微信授权页面
                window.location.href = base.target + 'api.php?oauth=wechat&callback=' + base.target + "wechat/"
            } else {

                var scope = "snsapi_userInfo",
                    state = "_" + (+new Date());
                Wechat.auth(scope, state, function(response) {
                    util.post('api.php?entry=app&c=account&a=login&do=wechatLogin', {
                        code: response.code
                    }, (rs) => {
                        if (rs.status == 1) {
                            accountLoginVue.$store.commit('set_userInfo', rs.data.userInfo);
                            accountLoginVue.$store.commit('set_token', rs.data.token);
                            util.toast('登录成功');
                            accountLoginVue.$router.push('/user/index');
                        } else {
                            util.toast(rs.message);
                        }
                    }, () => {
                        util.toast('登录失败');
                    });
                }, function(reason) {
                    util.toast("错误: " + reason);
                });
            }
        }
    },
    mounted: function() {
        this.$nextTick(function() {
            accountLoginCss.use();
        })
    },
    destroyed() {
        accountLoginCss.unuse();
    }
}
</script>

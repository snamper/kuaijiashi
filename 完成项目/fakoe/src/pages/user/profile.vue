<template>
    <div>
        <v-header>
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
            <hui-cell-group>
                <hui-cell-item arrow>
                    <span slot="left">头像：</span>
                    <label slot="right" class="item-after" style="padding: 10px 0;" @click="setAvatar">
                        <img alt="" class="userInfo_avatar" :src="userInfo.avatar" style="height: 50px;width:50px;border-radius: 50%;50%;" />
                    </label>
                </hui-cell-item>
                <hui-cell-item arrow>
                    <span slot="left">昵称：</span>
                    <input slot="right" type="text" placeholder="请输入昵称" v-model="userInfo.nickname" style="text-align: right;" />
                </hui-cell-item>
                <hui-cell-item arrow type="label">
                    <span slot="left">性别：</span>
                    <select slot="right">
                        <option value="">请选择性别</option>
                        <option value="1" :selected="userInfo.profile.gender == '1'">男</option>
                        <option value="2" :selected="userInfo.profile.gender == '2'">女</option>
                    </select>
                </hui-cell-item>
                <hui-cell-item arrow>
                    <span slot="left">手机号：</span>
                    <input slot="right" type="number" style="text-align: right;" v-model="userInfo.mobile" readonly="readonly" @click="showPopup = true">
                </hui-cell-item>
            </hui-cell-group>
            <div style="padding:0 25px 25px;">
                <hui-button size="large" type="warning" style="background:#01AAAF;" @click.native="updateInfo">提交信息</hui-button>
            </div>
            <hui-popup position="bottom" :show.sync="showPopup" height="50%">
                <div class="center-middle">
                    <hui-cell-group>
                        <hui-cell-item>
                            <span slot="left">手机号：</span>
                            <input type="text" slot="right" placeholder="请输入手机号码" v-model="userInfo.mobile" style="text-align: left!important;width: 50%;">
                            <hui-sendcode slot="right" v-model="sendcodeStart" @click.native="sendCode" type="warning" style="background:#01AAAF;"></hui-sendcode>
                        </hui-cell-item>
                        <hui-cell-item>
                            <span slot="left">验证码：</span>
                            <input slot="right" type="number" placeholder="请输入验证码" v-model="code" style="text-align: left!important;">
                        </hui-cell-item>
                        <hui-cell-item>
                            <span slot="left">密码：</span>
                            <input slot="right" type="password" placeholder="请输入密码，留空不更新" v-model="password" style="text-align: left!important;">
                        </hui-cell-item>
                        <hui-cell-item>
                            <span slot="left">确认密码：</span>
                            <input slot="right" type="password" placeholder="请确认密码" v-model="repassword" style="text-align: left!important;">
                        </hui-cell-item>
                    </hui-cell-group>
                    <div class="operate">
                        <button @click="showPopup = false">
                            取消
                        </button>
                        <button @click="setMobile" class="highlight">
                            确定
                        </button>
                    </div>
                </div>
            </hui-popup>
        </v-content>
    </div>
</template>
<script>
var userProfileCss = require('!style-loader/useable!css-loader!../../assets/css/user/profile.css');
let userProfileVue;
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
            showPopup: false,
            showCitySelect: false,
            sendcodeStart: false,
            code: '',
            password: '',
            repassword: ''
        }
    },
    created: function() {
        userProfileVue = this;
    },
    methods: {
        init() {

        },
        setMobile() {
            if (userProfileVue.password != userProfileVue.repassword) {
                util.toast('两次密码不一致');
            }
            util.post('api.php?entry=app&c=user&a=profile&do=bind', {
                mobile: userProfileVue.userInfo.mobile,
                password: userProfileVue.password,
                repassword: userProfileVue.repassword,
                code: userProfileVue.code
            }, (rs) => {
                if (rs.status == 1) {
                    userProfileVue.$store.commit('set_userInfo', rs.data.userInfo);
                    userProfileVue.$store.commit('set_token', rs.data.token);
                    util.toast(rs.message);
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            })
        },
        sendCode: function(e) {
            util.post('api.php?entry=app&c=user&a=profile&do=sendcode', {
                mobile: userProfileVue.userInfo.mobile
            }, (rs) => {
                if (rs.status == 1) {
                    userProfileVue.sendcodeStart = true;
                    util.toast(rs.message);
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            })
        },
        setAvatar: function() {
            if (util.isCordova()) {
                util.uploadImages(function(ret) {
                    userProfileVue.avatar = ret;
                });
            } else {
                wx.chooseImage({
                    count: 1,
                    sizeType: ["original", "compressed"],
                    sourceType: ["album", "camera"],
                    success: function(res) {
                        var localIds = res.localIds;
                        wx.uploadImage({
                            localId: localIds[0], // 需要上传的图片的本地ID，由chooseImage接口获得
                            isShowProgressTips: 1, // 默认为1，显示进度提示
                            success: function(res) {
                                var serverId = res.serverId; // 返回图片的服务器端ID
                                util.post('api.php?entry=app&c=utility&a=weUpload&do=uploadMedia', {
                                    media_id: serverId
                                }, (rs) => {
                                    if (rs.status == 1) {
                                        userProfileVue.avatar = util.tomedia(rs.data);
                                    } else {
                                        util.toast(rs.message);
                                    }
                                }, () => {
                                    util.toast('数据传输失败,请重试')
                                })
                            }
                        });
                    }
                })
            }
        },
        updateInfo() {
            util.post('api.php?entry=app&c=user&a=profile&do=update', {
                avatar: userProfileVue.userInfo.avatar,
                nickname: userProfileVue.userInfo.nickname,
                sex: userProfileVue.userInfo.profile.gender,
            }, (rs) => {
                if (rs.status == 1) {
                    userProfileVue.$store.commit('set_userInfo', rs.data.userInfo);
                    userProfileVue.$store.commit('set_token', rs.data.token);
                    util.toast(rs.message);
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            })
        }
    },
    mounted: function() {
        this.$nextTick(function() {
            userProfileCss.use();
            userProfileVue.init();
        });
    },
    destroyed: function() {
        userProfileCss.unuse();
    }
}
</script>

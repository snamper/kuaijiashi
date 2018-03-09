<template>
    <div>
        <v-content topHeight="0">
            <div class="ui-page-group">
                <!-- 单个page ,第一个.page默认被展示-->
                <div class="ui-page ui-page-current">
                    <!--页面内容开始-->
                    <div class="ui-content">
                        <!--头部内容开始-->
                        <div class="header">
                            <div>
                                <div class="chat-photo" :style="[{'background-image': 'url(' + (userInfo.avatar ? userInfo.avatar : loadImg()) + ')'}, {'background-size': 'cover'}]"></div>
                            </div>
                            <div>
                                {{userInfo.nickname}}
                                <svg class="svg" aria-hidden="true" style="color:#01AAAF;" v-if="userInfo.is_vip == 1">
                                    <use xlink:href="#icon-vip"></use>
                                </svg>
                            </div>
                            <div>
                                <router-link to="/user/profile">
                                    <svg class="svg icon-bianji" aria-hidden="true">
                                        <use xlink:href="#icon-edit"></use>
                                    </svg>
                                </router-link>
                            </div>
                        </div>
                        <!--头部内容结束-->
                        <!--中间我的订单开始-->
                        <div class="ui-cells">
                            <div class="ui-cell">
                                <div class="left-content">我的订单</div>
                                <div class="ui-cell-primary look-all">
                                    <router-link to="/order/list">
                                        查看全部订单
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-right"></use>
                                        </svg>
                                    </router-link>
                                </div>
                            </div>
                            <div class="ui-cell my-order">
                                <div class="ui-cell-center mall-list">
                                    <router-link to="/order/list?status=1">
                                        <div class="mall-list-img">
                                            <img src="../../assets/images/payment.png">
                                        </div>
                                        <div class="mall-name">待付款</div>
                                    </router-link>
                                </div>
                                <div class="ui-cell-center mall-list">
                                    <router-link to="/order/list?status=2">
                                        <div class="mall-list-img">
                                            <img src="../../assets/images/handing.png">
                                        </div>
                                        <div class="mall-name">进行中</div>
                                    </router-link>
                                </div>
                                <div class="ui-cell-center mall-list">
                                    <router-link to="/order/list?status=3">
                                        <div class="mall-list-img">
                                            <img src="../../assets/images/evaluate.png">
                                        </div>
                                        <div class="mall-name">待评价</div>
                                    </router-link>
                                </div>
                            </div>
                        </div>
                        <!--中间我的订单结束-->
                        <div class="ui-cells list-box">
                            <router-link to="/user/wallet">
                                <div class="ui-cell list-box-content">
                                    <div class="left-content">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-pay"></use>
                                        </svg>
                                        我的钱包
                                    </div>
                                    <div class="ui-cell-primary right-icon">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-right"></use>
                                        </svg>
                                    </div>
                                </div>
                            </router-link>
                            <router-link to="/bidding/list">
                                <div class="ui-cell list-box-content">
                                    <div class="left-content">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-similar"></use>
                                        </svg>
                                        竞标记录
                                    </div>
                                    <div class="ui-cell-primary right-icon">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-right"></use>
                                        </svg>
                                    </div>
                                </div>
                            </router-link>
                            <router-link to="/user/vip" v-if="userInfo.role == 2">
                                <div class="ui-cell list-box-content">
                                    <div class="left-content">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-vip"></use>
                                        </svg>
                                        我的VIP
                                    </div>
                                    <div class="ui-cell-primary right-icon">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-right"></use>
                                        </svg>
                                    </div>
                                </div>
                            </router-link>
                            <router-link to="/coach/recruit">
                                <div class="ui-cell list-box-content">
                                    <div class="left-content">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-business_card"></use>
                                        </svg>
                                        成为教练
                                    </div>
                                    <div class="ui-cell-primary right-icon">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-right"></use>
                                        </svg>
                                    </div>
                                </div>
                            </router-link>
                            <router-link to="/user/coupon/0">
                                <div class="ui-cell list-box-content">
                                    <div class="left-content">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-ticket"></use>
                                        </svg>
                                        我的优惠券
                                    </div>
                                    <div class="ui-cell-primary right-icon">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-right"></use>
                                        </svg>
                                    </div>
                                </div>
                            </router-link>
                            <router-link to="/user/follow">
                                <div class="ui-cell list-box-content">
                                    <div class="left-content">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-like"></use>
                                        </svg>
                                        我的关注
                                    </div>
                                    <div class="ui-cell-primary right-icon">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-right"></use>
                                        </svg>
                                    </div>
                                </div>
                            </router-link>
                        </div>
                        <div class="ui-cells list-box">
                            <a href="javascript:;" @click="changeRole">
                                <div class="ui-cell list-box-content">
                                    <div class="left-content">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-refresh"></use>
                                        </svg>
                                        当前角色：{{userInfo.role == 1 ? '学员' : '教练'}}
                                    </div>
                                    <div class="ui-cell-primary right-icon">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-right"></use>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                            <a href="javascript:;" @click="logout">
                                <div class="ui-cell list-box-content">
                                    <div class="left-content">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-settings"></use>
                                        </svg>
                                        退出账号
                                    </div>
                                    <div class="ui-cell-primary right-icon">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-right"></use>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <!--页面内容结束-->
                    <!--底部广告链接开始-->
                    <!--底部广告链接结束-->
                </div>
            </div>
        </v-content>
        <v-footer></v-footer>
    </div>
</template>
<script>
var userIndexCss = require('!style-loader/useable!css-loader!../../assets/css/user/index.css');
let userIndexVue;
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
        return {}
    },
    created: function() {
        userIndexVue = this;
    },
    methods: {
        init() {
            console.log(userIndexVue.userInfo);
        },
        loadImg(){
            return require('../../assets/images/avatar.png');
        },
        changeRole() {
            util.post('api.php?entry=app&c=user&a=profile&do=changeRole', {}, (rs) => {
                if (rs.status == 1) {
                    userIndexVue.$store.commit('set_userInfo', rs.data.userInfo);
                    userIndexVue.$store.commit('set_token', rs.data.token);
                    util.toast(rs.message);
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            })
        },
        logout() {
            userIndexVue.$store.commit('set_userInfo', null);
            userIndexVue.$store.commit('set_token', null);
            userIndexVue.$router.push('/home/index');
        }
    },
    mounted: function() {
        this.$nextTick(function() {
            userIndexCss.use();
            userIndexVue.init();
        });
    },
    destroyed: function() {
        userIndexCss.unuse();
    }
}
</script>

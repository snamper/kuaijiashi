<template>
    <div>
        <v-header title="优惠券">
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
                        <!--可使用-->
                        <hui-pullrefresh :callback="refresh" ref="refreshRef">
                            <hui-infinitescroll :callback="loadMore" ref="loadMoreRef">
                                <div slot="list">
                                    <div class="box" v-for="(row, index) in lists">
                                        <div class="box_item">
                                            <a class="ui-cell item btn_open" href="javascript:;">
                                                <div class="item_lt">
                                                    <div>￥<span class="item_price">{{row.value}}</span></div>
                                                </div>
                                                <div class="ui-cell-primary item_rt" @click="getCoupon(row.id)">
                                                    <div class="item_name">{{row.name}}</div>
                                                    <div class="item_timer">
                                                        使用期限: {{row.start_time}} - {{row.end_time}} </div>
                                                    <div class="ui-cell item_info">
                                                        <div class="ui-cell-primary">使用范围</div>
                                                        <div>
                                                            <img class="item_tx_icon" src="../../assets/images/open.png" v-if="!row.showDescription">
                                                            <img class="item_tx_icon" src="../../assets/images/close.png" v-if="row.showDescription">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="coupon_tips coupon_lt"><img src="../../assets/images/coupon_lt.png"></div>
                                                <div class="coupon_tips coupon_rt"><img src="../../assets/images/coupon_rt.png"></div>
                                                <div class="tx_btn" @click="toShowDescription(index)"></div>
                                            </a>
                                        </div>
                                        <div class="item_tx" v-if="row.showDescription">{{row.description}}</div>
                                    </div>
                                </div>
                            </hui-infinitescroll>
                        </hui-pullrefresh>
                        <!--已锁定-->
                        <!--已使用-->
                        <!--已过期-->
                    </div>
                    <!--页面内容结束-->
                </div>
            </div>
        </v-content>
        <v-footer :diy="true">
            <div slot="content">
                <div class="bottom-box">
                    <a class="coupon_dh" href="javascript:" :class="tab == 'my' ? 'active' : ''" @click="changeTab('my')">
                        <svg class="svg" aria-hidden="true">
                            <use xlink:href="#icon-goods_new"></use>
                        </svg>我的优惠券</a>
                    <a href="javascript:" :class="tab == 'all' ? 'active' : ''" @click="changeTab('all')">
                        <svg class="svg" aria-hidden="true">
                            <use xlink:href="#icon-list"></use>
                        </svg>领取优惠券</a>
                </div>
            </div>
        </v-footer>
    </div>
</template>
<script>
var userCouponCss = require('!style-loader/useable!css-loader!../../assets/css/user/coupon.css');
let userCouponVue;
import util from 'util'
import is from 'is'

export default {
    computed: {
        userInfo() {
            return this.$store.state.app.userInfo || {
                profile: []
            };
        },
    },
    data() {
        return {
            order_id: this.$route.params.order_id,
            lists: [],
            page: 1,
            tab: 'my',
            all: 0
        }
    },
    created: function() {
        userCouponVue = this;
    },
    methods: {
        init() {

        },
        changeTab(type) {
            userCouponVue.tab = type;
            if (type == 'all') {
                userCouponVue.all = 1
            } else {
                userCouponVue.all = 0;
            }
            userCouponVue.refresh();
        },
        getCoupon(id) {
            if (userCouponVue.all != 1) {
                if (userCouponVue.order_id != '0') {
                    userCouponVue.$router.push('/order/detail/' + userCouponVue.order_id + '/' + id);
                } else {
                    return false;
                }
            } else {
                util.post('api.php?entry=app&c=user&a=coupon&do=get', {
                    id: id,
                }, (rs) => {
                    if (rs.status == 1) {
                        util.toast(rs.message);
                    } else {
                        util.toast(rs.message);
                    }
                    userCouponVue.refresh();
                }, () => {
                    util.toast('数据传输失败,请重试')
                })
            }
        },
        loadCoupon: function(successFunc, errorFunc) {
            util.post('api.php?entry=app&c=user&a=coupon&do=list', {
                page: userCouponVue.page,
                all: userCouponVue.all
            }, (rs) => {
                if (rs.status == 1) {
                    if (successFunc) {
                        successFunc(rs.data.length);
                    }
                    if (!is.empty(rs.data)) {
                        userCouponVue.lists.push(...rs.data);
                        userCouponVue.page = userCouponVue.page + 1;
                    }
                } else {
                    if (errorFunc) {
                        errorFunc(rs.message);
                    }
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            })
        },
        refresh() {
            userCouponVue.lists = [];
            userCouponVue.page = 1;
            userCouponVue.loadMore();
            userCouponVue.$refs.refreshRef.$emit('hui.pullrefresh.finishLoad');
        },
        loadMore() {
            userCouponVue.loadCoupon(function(length) {
                if (length <= 0) {
                    userCouponVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                    return;
                }
                userCouponVue.$refs.loadMoreRef.$emit('hui.infinitescroll.finishLoad');
            }, function() {
                userCouponVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                return;
            });
        },
        toShowDescription(index) {
            let status = userCouponVue.lists[index].showDescription == 1 ? 0 : 1;
            userCouponVue.$set(userCouponVue.lists[index], 'showDescription', status);
        }
    },
    mounted: function() {
        this.$nextTick(function() {
            userCouponCss.use();
            userCouponVue.loadMore();
        });
    },
    destroyed: function() {
        userCouponCss.unuse();
    }
}
</script>

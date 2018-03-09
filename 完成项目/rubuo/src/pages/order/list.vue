<template>
    <div>
        <v-header title="订单列表">
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
        <v-content bottomHeight="0" @scroll="scrollEvent">
            <div class="ui-page-group">
                <!-- 单个page ,第一个.page默认被展示-->
                <div class="ui-page ui-page-current">
                    <!--页面内容开始-->
                    <div class="ui-content">
                        <hui-pullrefresh :callback="refresh" ref="refreshRef">
                            <hui-infinitescroll :callback="loadMore" ref="loadMoreRef">
                                <!--tab切换开始-->
                                <div class="ui-tab tab-list" slot="list">
                                    <!--tab头部开始-->
                                    <ul class="ui-tab-nav ui-row ui-no-gutter">
                                        <li class="ui-col-25 " :class="status == 0 ? 'current' : ''" @click="changeTab($event, 0)">全部</li>
                                        <li class="ui-col-25 " :class="status == 1 ? 'current' : ''" @click="changeTab($event, 1)">待支付</li>
                                        <li class="ui-col-25 " :class="status == 2 ? 'current' : ''" @click="changeTab($event, 2)">进行中</li>
                                        <li class="ui-col-25 " :class="status == 3 ? 'current' : ''" @click="changeTab($event, 3)">待评价</li>
                                    </ul>
                                    <!--tab头部结束-->
                                    <!--tab内容开始-->
                                    <div class="ui-tab-content">
                                        <div>
                                            <div class="ui-cells" v-for="(row, index) in lists">
                                                <div class="ui-cell">
                                                    <div class="list-left">订单号：</div>
                                                    <div class="list-zhong ui-cell-primary">{{row.ordersn}}</div>
                                                    <div class="list-right">{{row.statusText}}</div>
                                                </div>
                                                <div class="ui-cell">
                                                    <div class="box-left chat-photo" :style="[{'background-image': 'url(' + row.avatar + ')'}, {'background-size': 'cover'}]"></div>
                                                    <div class=" ui-cell-primary box-zhong ">
                                                        <div>订单金额<span class="color-prize ">￥{{row.total}}</span></div>
                                                        <div>{{row.name}}</div>
                                                        <div>{{row.createtime}}</div>
                                                    </div>
                                                    <div class="box-right ">
                                                        <router-link class="see-order " :to=" '/order/detail/' + row.id + '/' + row.coupon_id">查看订单</router-link>
                                                        <router-link class="go-evaluate " :to=" '/order/evaluate/' + row.id " v-if="row.status >= 3">查看评价</router-link>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--tab内容结束-->
                                </div>
                                <!--tab切换结束-->
                            </hui-infinitescroll>
                        </hui-pullrefresh>
                    </div>
                    <!--页面内容结束-->
                </div>
            </div>
        </v-content>
    </div>
</template>
<script>
var orderListCss = require('!style-loader/useable!css-loader!../../assets/css/order/list.css');
let orderListVue;
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
            page: 0,
            lists: [],
            status: 0,
        }
    },
    created: function() {
        orderListVue = this;
    },
    methods: {
        init: function(successFunc, errorFunc) {
            util.post('api.php?entry=app&c=order&a=list', {
                page: orderListVue.page,
                status: orderListVue.status
            }, (rs) => {
                if (rs.status == 1) {
                    if (successFunc) {
                        successFunc(rs.data.length);
                    }
                    if (!is.empty(rs.data)) {
                        if (!is.empty(rs.data)) {
                            orderListVue.lists.push(...rs.data);
                            orderListVue.page++;
                        }
                        orderListVue.count = rs.data.count;
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
        changeTab: function(e, status) {
            orderListVue.status = status;
            orderListVue.page = 1;
            orderListVue.$refs.loadMoreRef.$emit('hui.infinitescroll.reInit');
            orderListVue.refresh();
        },
        refresh() {
            Promise.resolve().then(function() {
                orderListVue.page = 1;
                orderListVue.lists = [];
                orderListVue.loadMore();
            }).then(function() {
                orderListVue.$refs.refreshRef.$emit('hui.pullrefresh.finishLoad');
            }).catch(function(err) {
                util.toast(err);
            });

        },
        loadMore() {
            orderListVue.init(function(length) {
                if (length <= 0) {
                    orderListVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                    return;
                }
                orderListVue.$refs.loadMoreRef.$emit('hui.infinitescroll.finishLoad');
            }, function() {
                orderListVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                return;
            });
        },
        scrollEvent(data) {
            if (data[1] > 46) { //当当前高度大于导航条到窗口顶部高度
                orderListVue.stick = true;
            } else {
                //如果回到导航条原位置之前则替换回原来的类名
                orderListVue.stick = false;
            }
        }
    },
    mounted: function() {
        orderListVue.status = this.$route.query.status || 0;
        this.$nextTick(function() {
            orderListCss.use();
            orderListVue.loadMore();
        });
    },
    destroyed: function() {
        orderListCss.unuse();
    }
}
</script>

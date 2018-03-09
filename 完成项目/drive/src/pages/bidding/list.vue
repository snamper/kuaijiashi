<template>
    <div>
        <v-header title="需求列表">
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
                        <hui-pullrefresh :callback="refresh" ref="refreshRef">
                            <hui-infinitescroll :callback="loadMore" ref="loadMoreRef">
                                <!--tab切换开始-->
                                <div class="ui-tab tab-list" slot="list">
                                    <!--tab内容开始-->
                                    <div class="ui-tab-content">
                                        <div>
                                            <div class="ui-cells" v-for="(row, index) in lists">
                                                <div class="ui-cell">
                                                    <div class="list-left">时间：</div>
                                                    <div class="list-zhong ui-cell-primary">{{row.createtime}}</div>
                                                    <div class="list-right">{{row.statusText}}</div>
                                                </div>
                                                <div class="ui-cell">
                                                    <div class="box-left chat-photo" :style="[{'background-image': 'url(' + row.avatar + ')'}, {'background-size': 'cover'}]"></div>
                                                    <div class=" ui-cell-primary box-zhong ">
                                                        <div>{{row.status == 4 ? row.realname : row.statusText}}</div>
                                                        <div class="desc">{{row.description}}</div>
                                                    </div>
                                                    <div class="box-right ">
                                                        <router-link class="see-order " :to=" '/bidding/detail/' + row.id">查看需求</router-link>
                                                        <router-link class="see-order " :to=" '/order/detail/' + row.oid + '/' + row.coupon_id" v-if="row.status == 4">查看订单</router-link>
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
        }
    },
    created: function() {
        orderListVue = this;
    },
    methods: {
        init: function(successFunc, errorFunc) {
            util.post('api.php?entry=app&c=bidding&a=list', {
                role: orderListVue.userInfo.role,
                page: orderListVue.page
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
        }
    },
    mounted: function() {
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

<template>
    <div>
        <v-header title="需求详情">
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
                        <!--订单信息开始-->
                        <div class="ui-cells">
                            <div class="ui-cell title">需求信息</div>
                            <div class="ui-cell ui-cells-access">
                                <span class="ui-cell-primary">创建时间</span>
                                <span>{{detail.createtime}}</span>
                            </div>
                            <div class="ui-cell ui-cells-access">
                                <div class="ui-cell-primary">需求详情</div>
                            </div>
                            <div class="ui-cell ui-cells-access">
                                <div>{{detail.description}}</div>
                            </div>
                        </div>
                        <div class="ui-cells" v-if="detail.status == 4">
                            <div class="ui-cell title">已选教练</div>
                            <div class="ui-cell ui-cells-access">
                                <!--大师头像-->
                                <div class="master-img">
                                    <img class="width100" :src="detail.avatar">
                                </div>
                                <!--大师项目价钱-->
                                <div class="master-prize ui-cell-primary">
                                    <div>{{detail.realname}}</div>
                                    <div class="color-prize">￥{{detail.price}}</div>
                                </div>
                            </div>
                            <div class="ui-cell ui-cells-access">
                                <span class="ui-cell-primary">手机</span>
                                <span>{{detail.mobile}}</span>
                            </div>
                            <div class="ui-cell ui-cells-access">
                                <span class="ui-cell-primary">挑选时间</span>
                                <span>{{detail.selecttime}}</span>
                            </div>
                        </div>
                        <!--订单信息结束-->
                        <!--支付信息开始-->
                        <div class="ui-cells" v-if="detail.status == 3">
                            <div class="ui-cell title">待选教练</div>
                            <hui-infinitescroll :callback="loadMore" ref="loadMoreRef">
                                <div class="ui-cell ui-cells-access" v-for="(row, index) in lists" slot="list" @click="selectCoach(row.id)">
                                    <span class="ui-cell-primary">{{row.realname}}</span>
                                    <span class="color-prize">￥{{row.price}}</span>
                                </div>
                            </hui-infinitescroll>
                        </div>
                        <!--支付信息结束-->
                    </div>
                    <!--页面内容结束-->
                </div>
            </div>
        </v-content>
    </div>
</template>
<style type="text/css" scoped>
.ui-content {
    bottom: 0px!important;
}
</style>
<script>
var biddingDetailCss = require('!style-loader/useable!css-loader!../../assets/css/order/detail.css');
let biddingDetailVue;
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
            detail: [],
            coaches: [],
            page: 1,
            lists: []
        }
    },
    created: function() {
        biddingDetailVue = this;
    },
    methods: {
        init() {
            util.post('api.php?entry=app&c=bidding&a=detail&do=display', {
                id: biddingDetailVue.id
            }, (rs) => {
                if (rs.status == 1) {
                    biddingDetailVue.detail = rs.data.detail;
                    if (biddingDetailVue.detail.status == 3) {
                        biddingDetailVue.loadMore();
                    }
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            });
        },
        loadCoach: function(successFunc, errorFunc) {
            util.post('api.php?entry=app&c=bidding&a=detail&do=getList', {
                id: biddingDetailVue.id,
                page: biddingDetailVue.page
            }, (rs) => {
                if (rs.status == 1) {
                    if (successFunc) {
                        successFunc(rs.data.length);
                    }
                    if (!is.empty(rs.data)) {
                        biddingDetailVue.lists.push(...rs.data);
                        biddingDetailVue.page++;
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
        loadMore() {
            biddingDetailVue.loadCoach(function(length) {
                if (length <= 0) {
                    biddingDetailVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                    return;
                }
                biddingDetailVue.$refs.loadMoreRef.$emit('hui.infinitescroll.finishLoad');
            }, function() {
                biddingDetailVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                return;
            });
        },
        selectCoach(id) {
            biddingDetailVue.$router.push({
                name: 'coachDeatil',
                params: {
                    id: id,
                    bid: biddingDetailVue.id
                }
            });
        },
        finish() {
            biddingDetailVue.$Confirm('确认结束该订单?', function() {
                util.post('api.php?entry=app&c=order&a=detail&do=finish', {
                    id: biddingDetailVue.id
                }, (rs) => {
                    if (rs.status == 1) {
                        util.toast(rs.message);
                        biddingDetailVue.init();
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
            biddingDetailCss.use();
            biddingDetailVue.init();
        });
    },
    destroyed: function() {
        biddingDetailCss.unuse();
    }
}
</script>

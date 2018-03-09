<template>
    <div>
        <v-header title="订单详情">
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
                        <!--进度条开始-->
                        <div class="ui-cells">
                            <div class="ui-row track-nav">
                                <div class="ui-col-25"><span class="active">选择教练</span></div>
                                <div class="ui-col-25"><span class="active">确定订单</span></div>
                                <div class="ui-col-25"><span>完成支付</span></div>
                                <div class="ui-col-25"><span>进行咨询</span></div>
                            </div>
                            <div class="ui-cell straight">
                                <span class="line-active" style="width: 17%;"></span>
                                <span class="line-active" style="width: 21%;"></span>
                                <span style="width: 18%;"></span>
                                <span style="width: 20%;"></span>
                                <span class="ui-cell-primary"></span>
                            </div>
                        </div>
                        <!--进度条结束-->
                        <!--订单信息开始-->
                        <div class="ui-cells">
                            <div class="ui-cell title">订单信息</div>
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
                                <span class="ui-cell-primary">订单编号</span>
                                <span>{{detail.orderno}}</span>
                            </div>
                            <div class="ui-cell ui-cells-access">
                                <span class="ui-cell-primary">下单时间</span>
                                <span>{{detail.createtime}}</span>
                            </div>
                        </div>
                        <!--订单信息结束-->
                        <!--支付信息开始-->
                        <div class="ui-cells">
                            <div class="ui-cell title">支付信息</div>
                            <div class="ui-cell ui-cells-access">
                                <span class="ui-cell-primary">订单金额</span>
                                <span class="color-prize">￥{{detail.price}}</span>
                            </div>
                            <!-- 普通优惠券开始 -->
                            <a class="ui-cell ui-cells-access cell-lianjie" v-for="(row, index) in copons" :key="index" :to="'/copon/detail/' + row.id">
                                <div class="ui-cell-primary confirm-coupon">
                                    <span>优惠金额</span>
                                    <span>{{row.title}}</span>
                                    <i class="iconfont icon-youjiantou"></i>
                                </div>
                                <span class="color-prize">￥{{row.money}}</span>
                            </a>
                            <!-- 普通优惠券结束 -->
                            <div class="ui-cell ui-cells-access">
                                <span class="">实付金额</span>
                                <span class="color-prize">￥58.00</span>
                            </div>
                        </div>
                        <!--支付信息结束-->
                    </div>
                    <!--页面内容结束-->
                    <!--底部悬浮支付按钮-->
                    <div class="btn-qdzf">
                        <a id="buy_btn" isok="1">确定支付</a>
                    </div>
                    <!--弹窗开始-->
                    <div class="concern_bbc_model">
                        <div class="concern_bbc_mask"></div>
                        <div class="concern_bbc_box clearfix">
                            <div class="concern_bbc_close"><i class="ui-icon icon-close"></i></div>
                            <div class="concern_bbc_content">
                                <div class="concern_title">您在支付中遇到问题？</div>
                                <div>
                                    解决方案一：
                                    <br> 长按识别下方二维码继续完成支付
                                </div>
                                <div class="concern_img_box">
                                    <img id="native_img" src="http://m.bangbangce.com/Template/Wechat/Yidake/Assets/Images/Public/concern.png">
                                </div>
                                <div>
                                    解决方案二：
                                    <br> 关闭弹窗后，选择使用支付宝支付
                                </div>
                                <a orderno="A18010311143499272" class="btn-completed">我已完成付款</a>
                            </div>
                        </div>
                    </div>
                    <!--弹窗结束-->
                </div>
            </div>
        </v-content>
    </div>
</template>
<script>
var homeIndexCss = require('!style-loader/useable!css-loader!../../assets/css/order/cart.css');
let homeIndexVue;
import util from 'util'
import is from 'is'

export default {
    computed: {
        location() {
            return this.$store.state.app.location || {};
        },
    },
    data() {
        return {
            id: this.$route.params.id,
            detail: [],
            copons: []
        }
    },
    created: function() {
        homeIndexVue = this;
    },
    methods: {
        init() {
            util.post('api.php?entry=app&c=order&a=detail&do=display', {
                id: coachDetailVue.id
            }, (rs) => {
                if (rs.status == 1) {
                    coachDetailVue.detail = rs.data.detail;
                    coachDetailVue.copons = rs.data.copons;
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
            homeIndexCss.use();
            homeIndexVue.init();
        });
    },
    destroyed: function() {
        homeIndexCss.unuse();
    }
}
</script>

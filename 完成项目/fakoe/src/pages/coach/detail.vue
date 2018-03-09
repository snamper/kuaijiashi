<template>
    <div>
        <v-header title="详情">
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
        <v-content>
            <div class="ui-page-group">
                <!-- 单个page ,第一个.page默认被展示-->
                <div class="ui-page ui-page-current">
                    <!-- 页面内容区 开始-->
                    <div class="ui-content">
                        <!--头部-->
                        <header>
                            <img class="master-img" :src="detail.avatar" alt="">
                            <div class="btn-follow"></div>
                            <div class="detail-sc">
                                <a href="javascript:" @click="follow" :style="[detail.followed ? {'color':'#01AAAF'} : '']">
                                    <div>
                                        <svg class="svg" aria-hidden="true" style="color:#fff">
                                            <use xlink:href="#icon-like"></use>
                                        </svg>
                                    </div>
                                    <div class="Collection" style="color:#fff">{{detail.followed ? '已关注' : '关注'}}</div>
                                </a>
                            </div>
                        </header>
                        <!--头部结束-->
                        <!--大师简介-->
                        <div class="ui-cell">
                            <div style="font-size: 0.8rem;">{{detail.realname}}</div>
                            <div class="ui-cell-primary service">
                                <span>
                                    <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-vip"></use>
                                    </svg>
                                    平台认证
                                </span>
                                <span>
                                    <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-safe"></use>
                                    </svg>
                                    交易担保
                                </span>
                            </div>
                            <div class="home-dashang">
                                <router-link :to="'/coach/reward/' + id">打赏</router-link>
                                <span>{{detail.rewardCount}}人打赏</span>
                            </div>
                        </div>
                        <div class="ui-cells master-detail">
                            <div class="category">
                                <span v-for="(item, index) in detail.tags">{{item}}</span>
                            </div>
                            <div class="ui-cell master-info">
                                <div style="width: 100%;" v-html="detail.description"></div>
                            </div>
                            <div class="ui-cell data-count">
                                <div class="ui-cell-primary"><span class="ui-icon icon-jieda"></span>{{detail.orderCount}}解答</div>
                                <div class="ui-cell-primary"><span class="ui-icon icon-pingjia"></span>{{detail.evaluationCount}}评价</div>
                                <div class="ui-cell-primary" id="data-fensi-num" data-fensi="111"><span class="ui-icon icon-fensi"></span>{{detail.fansCount}}粉丝</div>
                            </div>
                        </div>
                        <!--大师简介结束-->
                        <!--评价-星级评分-->
                        <div class="ui-cells">
                            <div class="ui-cells-title">评价反馈</div>
                            <div class="ui-cell">
                                <div>胜诉率高：</div>
                                <span data="service" class="basic ui-cell-primary" style="width: 100px;">
                                    <hui-rate slot="left" v-model="detail.qualityLevel" color="#FF7D70" active-color="#FF7D70" size="18px" :readonly="true"></hui-rate>
                                </span>
                                <div>{{detail.qualityLevel}}</div>
                            </div>
                            <div class="ui-cell no-border">
                                <div>服务保障：</div>
                                <span data="service" class="basic ui-cell-primary" style="width: 100px;">
                                    <hui-rate slot="left" v-model="detail.serviceLevel" color="#FF7D70" active-color="#FF7D70" size="18px" :readonly="true"></hui-rate>
                                </span>
                                <div>{{detail.serviceLevel}}</div>
                            </div>
                            <div class="ui-cell no-border">
                                <div>价格合理：</div>
                                <span data="service" class="basic ui-cell-primary" style="width: 100px;">
                                    <hui-rate slot="left" v-model="detail.replyLevel" color="#FF7D70" active-color="#FF7D70" size="18px" :readonly="true"></hui-rate>
                                </span>
                                <div>{{detail.replyLevel}}</div>
                            </div>
                        </div>
                        <!--评价-星级评分结束-->
                        <!--评价列表-->
                        <div class="comment-box">
                            <div class="ui-cell" v-for="(row, index) in evaluations" :key="index">
                                <!--<div class="ui-cell-lt head-pic"><img src="/Upload/" alt=""></div>-->
                                <div class="ui-cell-primary ui-cell-lt">
                                    <div class="ui-cell title">
                                        <div class="ui-cell-primary">
                                            {{row.nickname}}
                                            <br> {{row.createtime}}
                                        </div>
                                    </div>
                                    <div class="tabs"><span v-for="(item, key) in row.tags" :key="key">{{item}}</span></div>
                                    <div class="text">{{row.content}}</div>
                                    <div class="replay" v-if="row.seller_content">回复：{{row.seller_content}}</div>
                                </div>
                            </div>
                            <div class="ui-cell" style="padding: 1px 0"></div>
                            <router-link class="browse-more" :to="'/coach/evaluation/' + detail.id">查看全部{{detail.evaluationCount}}条评价 &gt; </router-link>
                            <div class="ui-cells master-detail" style="padding-bottom:20px;color:#000;font-size:18px;font-weight:blod;">
                                <div>
                                    海量用户资源&nbsp;竞标获取用户&nbsp;免费注册&nbsp;收入翻倍提高&nbsp;
                                    <router-link to="/coach/recruit/"><span style="padding:5px 8px;background:#FF4F59;color:#fff;border-radius:10px;margin-left:5px;font-size:16px;"> 入驻</span></router-link>
                                </div>
                            </div>
                        </div>
                        <!--评价列表结束-->
                    </div>
                    <!-- 页面内容区 结束-->
                </div>
            </div>
        </v-content>
        <v-footer :diy="true">
            <div slot="content">
                <!--底部购买按钮-->
                <div class="ui-cell btn-bottom">
                    <div class="ui-cell-primary">
                        <a style="display: inline-block;width: 20%;margin-left: 0;" href="javascript:;" @click="bidding(detail.id)">竞标</a>
                        <a style="display: inline-block;width: 73%;margin-left: 5%;" href="javascript:;" @click="toBuy">预约TA的服务(￥{{detail.price}})</a>
                    </div>
                </div>
                <!--按钮结束-->
            </div>
        </v-footer>
    </div>
</template>
<script>
var coachDetailCss = require('!style-loader/useable!css-loader!../../assets/css/coach/detail.css');
let coachDetailVue;
import util from 'util'
import is from 'is'

export default {
    computed: {
        location() {
            return this.$store.state.app.location || {};
        },
        userInfo() {
            return this.$store.state.app.userInfo || {
                profile: []
            };
        }
    },
    data() {
        return {
            id: this.$route.params.id,
            bid: this.$route.params.bid,
            detail: [],
            evaluations: [],
        }
    },
    created: function() {
        coachDetailVue = this;
    },
    methods: {
        init() {
            util.post('api.php?entry=app&c=coach&a=detail&do=display', {
                id: coachDetailVue.id
            }, (rs) => {
                if (rs.status == 1) {
                    coachDetailVue.detail = rs.data.detail;
                    coachDetailVue.evaluations = rs.data.evaluations;
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            })
        },
        bidding(id) {
            if (is.empty(coachDetailVue.userInfo.mobile)) {
                util.toast('请先绑定手机号');
                coachDetailVue.$router.push('/user/profile');
                return false;
            }
            coachDetailVue.$router.push('/bidding/create/' + id);
        },
        toBuy() {
            if (is.empty(coachDetailVue.userInfo.mobile)) {
                util.toast('请先绑定手机号');
                coachDetailVue.$router.push('/user/profile');
                return false;
            }
            if (coachDetailVue.bid) {
                coachDetailVue.$Confirm('确认选取该律师?', () => {
                    coachDetailVue.buy();
                });
            } else {
                coachDetailVue.buy();
            }
        },
        buy() {
            util.post('api.php?entry=app&c=coach&a=detail&do=buy', {
                id: coachDetailVue.id,
                bid: coachDetailVue.bid
            }, (rs) => {
                if (rs.status == 1) {
                    coachDetailVue.$router.push('/order/detail/' + rs.data + '/0');
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            })
        },
        follow: function() {
            util.post('api.php?entry=app&c=coach&a=detail&do=follow', {
                touid: coachDetailVue.detail.uid
            }, (rs) => {
                if (rs.status == 1) {
                    coachDetailVue.$set(coachDetailVue.detail, 'followed', rs.data);
                    if (rs.data == '1') {
                        coachDetailVue.$set(coachDetailVue.detail, 'fansCount', Number(coachDetailVue.detail.fansCount) + 1);
                    } else if (rs.data == '0') {
                        coachDetailVue.$set(coachDetailVue.detail, 'fansCount', Number(coachDetailVue.detail.fansCount) - 1);
                    }
                    util.toast(rs.message);
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            })
        },
    },
    mounted: function() {
        this.$nextTick(function() {
            coachDetailCss.use();
            coachDetailVue.init();
            console.log('bid', coachDetailVue.bid);
        });
    },
    destroyed: function() {
        coachDetailCss.unuse();
    }
}
</script>

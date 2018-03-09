<template>
    <div>
        <v-content topHeight="0">
            <div class="ui-page-group">
                <!-- 单个page ,第一个.page默认被展示-->
                <div class="ui-page ui-page-current">
                    <ul class="ui-row ui-no-gutter sort-options">
                        <li class="multi ui-col-33 sort-select" :class="[byHot, byType == 'hot' ? 'select' : '']" @click="orderByHot">
                            人气
                            <div class="arrow">
                                <svg class="svg paixushang" aria-hidden="true">
                                    <use xlink:href="#icon-fold"></use>
                                </svg>
                                <svg class="svg paixuxia" aria-hidden="true">
                                    <use xlink:href="#icon-unfold"></use>
                                </svg>
                            </div>
                        </li>
                        <li class="multi ui-col-33 sort-select" :class="[byPrice, byType == 'price' ? 'select' : '']" @click="orderByPrice">
                            价格
                            <div class="arrow">
                                <svg class="svg paixushang" aria-hidden="true">
                                    <use xlink:href="#icon-fold"></use>
                                </svg>
                                <svg class="svg paixuxia" aria-hidden="true">
                                    <use xlink:href="#icon-unfold"></use>
                                </svg>
                            </div>
                        </li>
                        <li class="multi up ui-col-33" @click="openFilter">
                            筛选
                            <svg class="svg" aria-hidden="true" style="font-size: 0.65rem;margin-left: 0.25rem;">
                                <use xlink:href="#icon-filter"></use>
                            </svg>
                        </li>
                    </ul>
                    <!--筛选开始-->
                    <div class="choose" :style="[{'right': filter ? '0%' : '-100%'}]">
                        <div class="mask" v-show="filter" @click="closeFilter"></div>
                        <div class="chooseContent" style="padding-bottom: 112.141px;overflow:scroll;">
                            <div class="sex-box">
                                <div>财务达人性别</div>
                                <div id="sex">
                                    <span @click="setSex('男')" :class="sex == '男' ? 'choActive' : ''">男</span>
                                    <span @click="setSex('女')" :class="sex == '女' ? 'choActive' : ''">女</span>
                                </div>
                            </div>
                            <div class="ui-cell">服务评估</div>
                            <div class="chooseBox">
                                <div class="labelCho">
                                    <span @click="setType('AAA+')" :class="type == 'AAA+' ? 'choActive' : ''">C1</span>
                                    <span @click="setType('AA+')" :class="type == 'AA+' ? 'choActive' : ''">C2</span>
                                    <span @click="setType('A+')" :class="type == 'A+' ? 'choActive' : ''">B2</span>
                                </div>
                            </div>
                            <div class="sex-box">
                                <div>所在城市</div>
                                <div id="sex">
                                    <span @click="setCity(location.city)" :class="city == location.city ? 'choActive' : ''">
                                        <svg class="svg" aria-hidden="true">
                                            <use xlink:href="#icon-location"></use>
                                        </svg>
                                        {{location.city}}
                                    </span>
                                </div>
                            </div>
                            <div class="ui-cell">标签</div>
                            <div class="chooseBox">
                                <div class="labelCho">
                                    <span v-for="(row, index) in tags" @click="setTag(row)" :class="tag == row ? 'choActive' : ''">{{row}}</span>
                                </div>
                            </div>
                            <div class="btnBox ui-cell">
                                <div @click="resetCondition">重置</div>
                                <div @click="setCondition">确定</div>
                            </div>
                        </div>
                    </div>
                    <!--筛选结束-->
                    <!--返回顶部-->
                    <div id="toTop" style="display: none;"><i class="ui-icon icon-fanhuidingbu"></i></div>
                    <!-- 页面内容区 开始-->
                    <div class="ui-content" id="scroll-page">
                        <!--大师列表-->
                        <hui-pullrefresh :callback="refresh" ref="refreshRef">
                            <hui-infinitescroll :callback="loadMore" ref="loadMoreRef">
                                <div class="master-list" slot="list">
                                    <router-link v-for="(row, index) in coaches" :key="index" :to="'/coach/detail/' + row.id">
                                        <div class="ui-cells master-content">
                                            <div class="ui-cell">
                                                <div class="head-pic"><img :src="row.avatar" alt=""></div>
                                                <div class="detail">
                                                    <div class="master-name">{{row.realname}}</div>
                                                    <div data="service" class="basic" style="width: 100px;">
                                                        <hui-rate slot="left" v-model="row.level" color="#FF7D70" active-color="#FF7D70" size="18px" :readonly="true"></hui-rate>
                                                    </div>
                                                    <span>{{row.level}}</span>
                                                </div>
                                                <div class="price"><i style="color:#999;">预付金&nbsp;</i>￥{{row.price}}</div>
                                            </div>
                                            <div class="ui-nowrap-2 master-info" v-html="stripHTML(row.description)"></div>
                                            <div class="ui-cell data-count">
                                                <div class="ui-cell-primary"><span class="ui-icon icon-jieda"></span>{{row.orderCount}}解答</div>
                                                <div class="ui-cell-primary"><span class="ui-icon icon-pingjia"></span>{{row.evaluationCount}}评价</div>
                                                <div class="ui-cell-primary"><span class="ui-icon icon-fensi"></span>{{row.fansCount}}粉丝</div>
                                            </div>
                                        </div>
                                    </router-link>
                                </div>
                            </hui-infinitescroll>
                        </hui-pullrefresh>
                        <hui-backtop></hui-backtop>
                        <!--大师列表结束-->
                    </div>
                    <!-- 页面内容区 结束-->
                </div>
                <!-- 单个page ,第一个.page默认被展示-->
            </div>
        </v-content>
        <v-footer></v-footer>
    </div>
</template>
<script>
var coachListCss = require('!style-loader/useable!css-loader!../../assets/css/coach/list.css');
let coachListVue;
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
            orderby: '',
            byType: '',
            byHot: '',
            byPrice: '',
            filter: false,
            page: 1,
            coaches: [],
            sex: this.$route.params.sex,
            type: this.$route.params.type,
            city: this.$route.params.city,
            tags: [],
            tag: this.$route.params.tag
        }
    },
    created: function() {
        coachListVue = this;
    },
    methods: {
        init() {
            util.post('api.php?entry=app&c=coach&a=list&do=display', {}, (rs) => {
                if (rs.status == 1) {
                    coachListVue.tags = rs.data;
                    coachListVue.refresh();
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            });
        },
        stripHTML(data) {
            return util.stripHTML(data);
        },
        orderByHot() {
            coachListVue.byType = 'hot';
            coachListVue.byHot = coachListVue.byHot == 'up' ? 'down' : 'up';
            coachListVue.orderby = coachListVue.byType + ',' + coachListVue.byHot;
            coachListVue.coaches = [];
            coachListVue.page = 1;
            coachListVue.loadMore();
        },
        orderByPrice() {
            coachListVue.byType = 'price';
            coachListVue.byPrice = coachListVue.byPrice == 'up' ? 'down' : 'up';
            coachListVue.orderby = coachListVue.byType + ',' + coachListVue.byPrice;
            coachListVue.coaches = [];
            coachListVue.page = 1;
            coachListVue.loadMore();
        },
        openFilter() {
            coachListVue.filter = !coachListVue.filter;
        },
        closeFilter() {
            coachListVue.filter = false;
        },
        loadCoach(successFunc, errorFunc) {
            util.post('api.php?entry=app&c=coach&a=list&do=getList', {
                page: coachListVue.page,
                orderby: coachListVue.orderby,
                sex: coachListVue.sex,
                type: coachListVue.type,
                city: coachListVue.city,
                tag: coachListVue.tag
            }, (rs) => {
                if (rs.status == 1) {
                    if (is.function(successFunc)) {
                        successFunc(rs.data.length);
                    }
                    for (var i = 0; i < rs.data.length; i++) {
                        coachListVue.coaches.push(rs.data[i]);
                    }
                    coachListVue.page++;
                } else {
                    if (is.function(errorFunc)) {
                        errorFunc();
                    }
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            })
        },
        refresh() {
            coachListVue.byType = '';
            coachListVue.byHot = '';
            coachListVue.byPrice = '';
            coachListVue.orderby = '';
            coachListVue.filter = false;
            coachListVue.coaches = [];
            coachListVue.page = 1;
            coachListVue.loadMore();
            coachListVue.$refs.refreshRef.$emit('hui.pullrefresh.finishLoad');
        },
        loadMore() {
            coachListVue.loadCoach(function(length) {
                if (length <= 0) {
                    coachListVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                    return;
                }
                coachListVue.$refs.loadMoreRef.$emit('hui.infinitescroll.finishLoad');
            }, function() {
                coachListVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                return;
            });
        },
        setSex(sex) {
            coachListVue.sex = sex;
        },
        setType(type) {
            coachListVue.type = type;
        },
        setCity(city) {
            coachListVue.city = city;
        },
        setTag(tag) {
            coachListVue.tag = tag;
        },
        setCondition() {
            coachListVue.coaches = [];
            coachListVue.page = 1;
            coachListVue.loadMore();
            coachListVue.filter = false;
        },
        resetCondition() {
            coachListVue.sex = '';
            coachListVue.type = '';
            coachListVue.city = '';
            coachListVue.tag = '';
            coachListVue.filter = false;
            this.setCondition();
        }
    },
    mounted: function() {
        let _this = this;
        _this.$nextTick(function() {
            coachListCss.use();
            if (is.empty(_this.$route.params.city) || _this.$route.params.city == 0) {
                _this.city = _this.location.city;
                console.log(_this.city);
            }
            coachListVue.init();
        });
    },
    destroyed: function() {
        coachListCss.unuse();
    }
}
</script>

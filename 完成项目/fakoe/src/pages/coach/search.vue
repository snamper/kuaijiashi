<template>
    <div>
        <v-header>
            <div slot="left" class="item" flex="main:center cross:center" v-on:click="$router.go(-1)">
                <svg class="svg" aria-hidden="true">
                    <use xlink:href="#icon-back"></use>
                </svg>
            </div>
            <div slot="center" class="box">
                <div class="form">
                    <svg class="svg" aria-hidden="true">
                        <use xlink:href="#icon-search"></use>
                    </svg>
                    <input placeholder="搜索" type="text" v-model.lazy="keyword" />
                </div>
            </div>
            <div slot="right" class="item" flex="main:center cross:center" v-on:click="toSearch">
                搜索
            </div>
        </v-header>
        <v-content>
            <div class="ui-page-group">
                <!-- 单个page ,第一个.page默认被展示-->
                <div class="ui-page ui-page-current">
                    <!-- 页面内容区 开始-->
                    <div class="ui-content" v-show="!keyword">
                        <!--搜索提示列表开始-->
                        <div class="ui-cells list-box" id="search-tips-list" style="display:none;">
                        </div>
                        <!--列表结束-->
                        <div class="search-list">
                            <p>历史搜索</p>
                            <div class="master-name">
                                <a v-for="(row, index) in coach_keywords" href="javascript:void(0);" @click="setKeyword(row)"><span style="font-size:12px;">{{ row }}</span></a>
                            </div>
                            <div class="btn-qk">
                                <a href="javascript:void(0);" @click="resetHistory">清空历史</a>
                            </div>
                        </div>
                        <div class="search-list">
                            <p>热门搜索</p>
                            <div class="master-name">
                                <a v-for="(row, index) in hots" @click="setKeyword(row.keyword)" href="javascript:void(0);">
                                    <span style="font-size:12px;">{{ row.keyword }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="ui-content" v-show="keyword">
                        <hui-pullrefresh :callback="refresh" ref="refreshRef">
                            <hui-infinitescroll :callback="loadMore" ref="loadMoreRef">
                                <div class="master-list" slot="list">
                                    <router-link v-for="(row, index) in coaches" :key="index" :to="'/coach/detail/' + row.id">
                                        <div class="ui-cells master-content">
                                            <div class="ui-cell">
                                                <div class="head-pic"><img :src="row.avatar" alt=""></div>
                                                <div class="detail">
                                                    <div class="master-name" v-html="row.realname"></div>
                                                    <div data="service" class="basic" style="width: 100px;">
                                                        <hui-rate slot="left" v-model="row.level" color="#FF7D70" active-color="#FF7D70" size="18px" :readonly="true"></hui-rate>
                                                    </div>
                                                    <span>{{row.level}}</span>
                                                </div>
                                                <div class="price">￥{{row.price}}</div>
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
                    </div>
                    <!-- 页面内容区 结束-->
                </div>
                <!-- 单个page ,第一个.page默认被展示-->
            </div>
        </v-content>
    </div>
</template>
<script>
let coachSearchCss = require('!style-loader/useable!css-loader!../../assets/css/coach/search.css');
var coachListCss = require('!style-loader/useable!css-loader!../../assets/css/coach/list.css');
let coachSearchVue;
import util from 'util'
import is from 'is'
export default {
    computed: {
        coach_keywords() {
            return this.$store.state.app.coach_keywords || [];
        }
    },
    data() {
        return {
            page: 1,
            hots: [],
            keyword: '',
            latitude: '',
            longitude: '',
            coaches: [],
        }
    },
    created: function() {
        coachSearchVue = this;
    },
    watch: {
        // 如果 keyword 发生改变，这个函数就会运行  
        keyword: function(newQuestion) {
            this.toSearch();
        }
    },
    methods: {
        init: function() {
            util.post('api.php?entry=app&c=coach&a=search', {}, (rs) => {
                if (rs.status == 1) {
                    for (var i = 0; i < rs.data.length; i++) {
                        coachSearchVue.hots.push(rs.data[i]);
                    }
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            })
            coachSearchVue.loadCoach(function() {
                coachSearchVue.coaches = [];
            });
        },
        stripHTML(data) {
            return util.stripHTML(data);
        },
        loadCoach: function(successFunc, errorFunc) {
            if (!is.empty(coachSearchVue.keyword)) {
                let oldHistory = coachSearchVue.coach_keywords;
                console.log(oldHistory.indexOf(coachSearchVue.keyword) == -1, coachSearchVue.keyword);
                if (oldHistory.indexOf(coachSearchVue.keyword) == -1) {
                    if (util.count(oldHistory) >= 10) {
                        coachSearchVue.$store.commit('push_coach_keywords', {
                            coach_keywords: oldHistory.slice(-9),
                            pushData: coachSearchVue.keyword
                        });
                    } else {
                        coachSearchVue.$store.commit('push_coach_keywords', {
                            coach_keywords: oldHistory,
                            pushData: coachSearchVue.keyword
                        });
                    }
                }
                util.post('api.php?entry=app&c=coach&a=list&do=getList', {
                    page: coachSearchVue.page,
                    keyword: coachSearchVue.keyword,
                }, (rs) => {
                    if (rs.status == 1) {
                        if (successFunc) {
                            successFunc();
                        }
                        for (var i = 0; i < rs.data.length; i++) {
                            coachSearchVue.coaches.push(rs.data[i]);
                        }
                    } else {
                        if (errorFunc) {
                            errorFunc();
                        }
                    }
                }, () => {
                    util.toast('数据传输失败,请重试')
                })
            }
        },
        setKeyword: function(keyword) {
            coachSearchVue.keyword = keyword;
            coachSearchVue.page = 1;
            coachSearchVue.coaches = [];
            coachSearchVue.loadMore();
        },
        resetHistory: function() {
            coachSearchVue.$store.commit('set_coach_keywords', []);
        },
        refresh() {
            coachSearchVue.keyword = '';
            coachSearchVue.coaches = [];
            coachSearchVue.page = 1;
            coachSearchVue.loadMore();
            coachSearchVue.$refs.refreshRef.$emit('hui.pullrefresh.finishLoad');
        },
        loadMore() {
            coachSearchVue.loadCoach(function(length) {
                if (length <= 0) {
                    coachSearchVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                    return;
                }
                coachSearchVue.$refs.loadMoreRef.$emit('hui.infinitescroll.finishLoad');
            }, function() {
                coachSearchVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                return;
            });
        },
        toSearch: _.debounce(function() {
            coachSearchVue.page = 1;
            coachSearchVue.coaches = [];
            coachSearchVue.loadMore();
        }, 500)
    },
    mounted: function() {
        coachSearchVue.$nextTick(function() {
            coachSearchCss.use();
            coachListCss.use();
            coachSearchVue.init();

        })
    },
    destroyed: function() {
        coachSearchCss.unuse();
        coachListCss.unuse();
    }
}
</script>

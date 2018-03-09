<template>
    <div>
        <v-content topHeight="0">
            <div class="ui-page-group">
                <!-- 单个page ,第一个.page默认被展示-->
                <div class="ui-page ui-page-current">
                    <!-- 页面内容区 开始-->
                    <div class="ui-content">
                        <!--列表开始-->
                        <hui-pullrefresh :callback="refresh" ref="refreshRef">
                            <hui-infinitescroll :callback="loadMore" ref="loadMoreRef">
                                <div class="quick-list" slot="list">
                                    <hui-swiper :options="swiperOption" ref="categorySwiper" class="nav-swiper">
                                        <hui-swiper-slide class="category-list">
                                            <div class="category-list-click item" :class="cate == '' ? 'active-nav' : ''" @click="setCate('')">全部</div>
                                        </hui-swiper-slide>
                                        <hui-swiper-slide v-for="(item, key) in category" :key="key" class="category-list">
                                            <div class="category-list-click item" :class="cate == item ? 'active-nav' : ''" @click="setCate(item)">{{item}}</div>
                                        </hui-swiper-slide>
                                    </hui-swiper>
                                    <!--循环此处列表-->
                                    <a v-for="(row, index) in lists" :href="row.link ? row.link : '#/article/detail/' + row.id" class="ui-cell list-box">
                                        <div class="list-right">
                                            <img :src="row.thumb">
                                        </div>
                                        <div class="list-left ui-cell-primary">
                                            <div>{{row.title}}</div>
                                            <div class="line-camp">{{row.description}}</div>
                                        </div>
                                    </a>
                                </div>
                            </hui-infinitescroll>
                        </hui-pullrefresh>
                        <!--列表结束-->
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
var articleListCss = require('!style-loader/useable!css-loader!../../assets/css/article/list.css');
let articleListVue;
import util from 'util'
import is from 'is'

export default {
    data() {
            return {
                lists: [],
                page: 1,
                category: [],
                swiperOption: {
                    notNextTick: true,
                    direction: 'horizontal',
                    width: window.screen.width / 4
                },
                cate: ''
            }
        },
        created: function() {
            articleListVue = this;
        },
        methods: {
            init() {
                util.post('api.php?entry=app&c=article&a=list&do=display', {}, (rs) => {
                    if (rs.status == 1) {
                        articleListVue.category = rs.data;
                        articleListVue.loadMore();
                    } else {
                        util.toast(rs.message);
                    }
                }, () => {
                    util.toast('数据传输失败,请重试')
                })
            },
            setCate(cate) {
                articleListVue.cate = cate;
                articleListVue.lists = [];
                articleListVue.page = 1;
                articleListVue.loadMore();
            },
            loadArticle: function(successFunc, errorFunc) {
                util.post('api.php?entry=app&c=article&a=list&do=getList', {
                    page: articleListVue.page,
                    category: articleListVue.cate
                }, (rs) => {
                    if (rs.status == 1) {
                        if (successFunc) {
                            successFunc(rs.data.length);
                        }
                        if (!is.empty(rs.data)) {
                            articleListVue.lists.push(...rs.data);
                            articleListVue.page = articleListVue.page + 1;
                        }
                    } else {
                        if (errorFunc) {
                            errorFunc(rs.message);
                        }
                    }
                }, () => {
                    util.toast('数据传输失败,请重试')
                });
            },
            refresh() {
                articleListVue.cate = '';
                articleListVue.lists = [];
                articleListVue.page = 1;
                articleListVue.loadMore();
                articleListVue.$refs.refreshRef.$emit('hui.pullrefresh.finishLoad');
            },
            loadMore() {
                articleListVue.loadArticle(function(length) {
                    if (length <= 0) {
                        articleListVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                        return;
                    }
                    articleListVue.$refs.loadMoreRef.$emit('hui.infinitescroll.finishLoad');
                }, function() {
                    articleListVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                    return;
                });
            },
        },
        mounted: function() {
            this.$nextTick(function() {
                articleListCss.use();
                articleListVue.init();
            });
        },
        destroyed: function() {
            articleListCss.unuse();
        }
}
</script>

<template>
    <div>
        <v-content topHeight="0">
            <div class="ui-page-group">
                <!-- 单个page ,第一个.page默认被展示-->
                <div class="ui-page ui-page-current">
                    <!--内容开始-->
                    <div class="ui-content">
                        <!--搜索框开始-->
                        <div class="ui-cell searchBox">
                            <router-link to="/coach/search" class="ui-cell-primary">
                                <div class="serachDiv">
                                    <svg class="svg" aria-hidden="true">
                                        <use xlink:href="#icon-search"></use>
                                    </svg>
                                    搜索律师
                                </div>
                            </router-link>
                            <router-link class="newsBox" to="/home/city">
                                <div>
                                    <svg class="svg" aria-hidden="true">
                                        <use xlink:href="#icon-location"></use>
                                    </svg>
                                </div>
                                <div>{{location.city}}</div>
                            </router-link>
                        </div>
                        <!--搜索框结束-->
                        <!--分类开始-->
                        <div class="ui-cells all-fenlei">
                            <!--大型活动banner-->
                            <!--修改2017年1月7日-->
                            <hui-swiper :options="swiperOption" ref="navigationsSwiper" class="swiper-Fl">
                                <hui-swiper-slide v-for="(row, index) in navigations" :key="index">
                                    <div class="ui-cell-center mall-list" v-for="(item, key) in navigations[index]" :key="key">
                                        <a :href="item.link">
                                            <div class="ui-avatar-radius">
                                                <img :src="item.thumb">
                                            </div>
                                            <div class="mall-name">{{item.title}}</div>
                                        </a>
                                    </div>
                                </hui-swiper-slide>
                                <div class="Fl-pagination swiper-pagination" slot="pagination" style="bottom:-5px;"></div>
                            </hui-swiper>
                            <!--轮播开始2016.12.3-->
                            <hui-swiper :options="autoSwiperOption" ref="bannersSwiper" class="index-slide">
                                <hui-swiper-slide v-for="(row, index) in banners" :key="index">
                                    <a :href="row.link">
                                        <img :src="row.thumb" alt="" />
                                    </a>
                                </hui-swiper-slide>
                                <div class="swiper-pagination" slot="pagination"></div>
                            </hui-swiper>
                            <!--轮播结束-->
                        </div>
                        <!--分类结束-->
                        <!--2016.12.2增加内容开始-->
                        <div class="ui-cells">
                            <div class="ui-cell hot_quick">
                                <div class="ui-cell-primary">热门文章</div>
                                <div class="quick_title">
                                    <hui-swiper :options="articlesSwiperOption" ref="articlesSwiper" class="swiper-container1">
                                        <hui-swiper-slide v-for="(row, index) in articles" :key="index">
                                            <p class="ui-nowrap">
                                                <a :href="row.link ? row.link : '#/article/detail/' + row.id">{{row.title}}</a>
                                            </p>
                                        </hui-swiper-slide>
                                        <div class="swiper-pagination" slot="pagination"></div>
                                    </hui-swiper>
                                </div>
                            </div>
                            <div class="ui-cell user_ad_box">
                                <div>
                                    <a :href="settings.ad1_link"><img :src="settings.ad1_thumb"></a>
                                </div>
                                <div class="ui-cell-primary small_list">
                                    <div>
                                        <a :href="settings.ad2_link"><img :src="settings.ad2_thumb"></a>
                                    </div>
                                    <div>
                                        <a :href="settings.ad3_link"><img :src="settings.ad3_thumb"></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--Top10-->
                        <div class="top10">
                            <div class="top-title">律师排行榜</div>
                            <div class="top10-lb">
                                <div class="top-list-title">订单榜TOP10</div>
                                <hui-swiper :options="topTenSwiperOption" ref="navigationsSwiper" class="topTen-swiper">
                                    <hui-swiper-slide v-for="(row, index) in coaches1" :key="index" class="topTen-list" style="width: 72.6667px; margin-right: 8px;">
                                        <router-link :to="'/coach/detail/' + row.id">
                                            <div class="master-img-box">
                                                <img :src="row.avatar" alt="">
                                                <span class="rankCount">{{index + 1}}</span>
                                            </div>
                                            <div class="mask_text">{{row.realname}}</div>
                                        </router-link>
                                    </hui-swiper-slide>
                                </hui-swiper>
                                <div class="list-line"></div>
                            </div>
                            <div class="top10-lb">
                                <div class="top-list-title">好评榜TOP10</div>
                                <hui-swiper :options="topTenSwiperOption" ref="navigationsSwiper" class="topTen-swiper">
                                    <hui-swiper-slide v-for="(row, index) in coaches2" :key="index" class="topTen-list" style="width: 72.6667px; margin-right: 8px;">
                                        <router-link :to="'/coach/detail/' + row.id">
                                            <div class="master-img-box">
                                                <img :src="row.avatar" alt="">
                                                <span class="rankCount">{{index + 1}}</span>
                                            </div>
                                            <div class="mask_text">{{row.realname}}</div>
                                        </router-link>
                                    </hui-swiper-slide>
                                </hui-swiper>
                                <div class="list-line"></div>
                            </div>
                            <div class="top10-lb">
                                <div class="top-list-title">潜力榜TOP10</div>
                                <hui-swiper :options="topTenSwiperOption" ref="navigationsSwiper" class="topTen-swiper">
                                    <hui-swiper-slide v-for="(row, index) in coaches3" :key="index" class="topTen-list" style="width: 72.6667px; margin-right: 8px;">
                                        <router-link :to="'/coach/detail/' + row.id">
                                            <div class="master-img-box">
                                                <img :src="row.avatar" alt="">
                                                <span class="rankCount">{{index + 1}}</span>
                                            </div>
                                            <div class="mask_text">{{row.realname}}</div>
                                        </router-link>
                                    </hui-swiper-slide>
                                </hui-swiper>
                                <div class="list-line"></div>
                            </div>
                        </div>
                    </div>
                    <!--内容结束-->
                    <!--底部广告链接开始-->
                    <!--底部广告链接结束-->
                </div>
            </div>
        </v-content>
        <v-footer></v-footer>
    </div>
</template>
<script>
var homeIndexCss = require('!style-loader/useable!css-loader!../../assets/css/home/index.css');
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
            navigations: [],
            autoSwiperOption: {
                notNextTick: true,
                autoplay: 3000,
                direction: 'horizontal',
                pagination: '.swiper-pagination'
            },
            swiperOption: {
                notNextTick: true,
                direction: 'horizontal',
                pagination: '.swiper-pagination'
            },
            topTenSwiperOption: {
                notNextTick: true,
                direction: 'horizontal',
                pagination: '.swiper-pagination',
                width: window.screen.width / 4
            },
            articlesSwiperOption: {
                notNextTick: true,
                direction: 'vertical',
                autoplay: 3000,
            },
            banners: [],
            articles: [],
            coaches1: [],
            coaches2: [],
            coaches3: [],
            settings: []
        }
    },
    created: function() {
        homeIndexVue = this;
    },
    methods: {
        init() {
            util.post('api.php?entry=app&c=home&a=index&do=display', {}, (rs) => {
                if (rs.status == 1) {
                    homeIndexVue.navigations = rs.data.navigations;
                    homeIndexVue.articles = rs.data.articles;
                    homeIndexVue.banners = rs.data.banners;
                    homeIndexVue.coaches1 = rs.data.coaches1;
                    homeIndexVue.coaches2 = rs.data.coaches2;
                    homeIndexVue.coaches3 = rs.data.coaches3;
                    homeIndexVue.settings = rs.data.settings;
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            });
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

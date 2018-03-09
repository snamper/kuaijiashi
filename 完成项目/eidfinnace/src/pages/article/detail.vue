<template>
    <div>
        <v-header title="文章详情">
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
                        <!--头部-->
                        <header>
                            <img :src="detail.thumb" alt="">
                            <div class="detail-title">{{detail.title}}</div>
                        </header>
                        <!--头部结束-->
                        <!--文章内容开始-->
                        <div class="detail-article">
                            <div class="article-content" v-html="detail.description"></div>
                        </div>
                        <!--文章内容结束-->
                        <div class="service-box">
                            <div class="content-title">内容详情</div>
                            <div class="service-content" v-html="detail.content"></div>
                        </div>
                    </div>
                    <!--页面内容结束-->
                </div>
            </div>
        </v-content>
    </div>
</template>
<script>
var articleDetailCss = require('!style-loader/useable!css-loader!../../assets/css/article/detail.css');
let articleDetailVue;
import util from 'util'
import is from 'is'

export default {
    data() {
            return {
                id: this.$route.params.id,
                detail: []
            }
        },
        created: function() {
            articleDetailVue = this;
        },
        methods: {
            init() {
                util.post('api.php?entry=app&c=article&a=detail&do=display', {
                    id: articleDetailVue.id
                }, (rs) => {
                    if (rs.status == 1) {
                        articleDetailVue.detail = rs.data;
                    } else {
                        util.toast(rs.message);
                    }
                }, () => {
                    util.toast('数据传输失败,请重试')
                });
            },
        },
        mounted: function() {
            this.$nextTick(function() {
                articleDetailCss.use();
                articleDetailVue.init();
            });
        },
        destroyed: function() {
            articleDetailCss.unuse();
        }
}
</script>

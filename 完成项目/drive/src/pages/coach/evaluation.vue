<template>
    <div>
        <v-header title="评价">
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
                    <!--页面内容开始-->
                    <div class="ui-content" id="scroll-page">
                        <hui-pullrefresh :callback="refresh" ref="refreshRef">
                            <hui-infinitescroll :callback="loadMore" ref="loadMoreRef">
                                <!--评价反馈开始-->
                                <!--<div class="content-title">评价反馈（）</div>-->
                                <div class="comment-box" slot="list">
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
                                </div>
                                <!--评价反馈结束-->
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
var coachEvaluationCss = require('!style-loader/useable!css-loader!../../assets/css/coach/evaluation.css');
let coachEvaluationVue;
import util from 'util'
import is from 'is'

export default {
    data() {
            return {
                id: this.$route.params.id,
                evaluations: [],
                page: 1,
            }
        },
        created: function() {
            coachEvaluationVue = this;
        },
        methods: {
            loadEvaluation: function(successFunc, errorFunc) {
                util.post('api.php?entry=app&c=coach&a=evaluation', {
                    page: coachEvaluationVue.page,
                    id: coachEvaluationVue.id
                }, (rs) => {
                    if (rs.status == 1) {
                        if (is.function(successFunc)) {
                            successFunc(rs.data.length);
                        }
                        coachEvaluationVue.evaluations.push(...rs.data);
                        coachEvaluationVue.page = coachEvaluationVue.page + 1;
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
                Promise.resolve().then(function() {
                    coachEvaluationVue.page = 1;
                    coachEvaluationVue.loadEvaluation(function() {
                        coachEvaluationVue.evaluations = [];
                    });
                }).then(function() {
                    coachEvaluationVue.$refs.refreshRef.$emit('hui.pullrefresh.finishLoad');
                    coachEvaluationVue.$refs.loadMoreRef.$emit('hui.infinitescroll.reInit');
                }).catch(function(err) {
                    util.toast(err);
                });

            },
            loadMore() {
                coachEvaluationVue.loadEvaluation(function(length) {
                    if (length <= 0) {
                        coachEvaluationVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                        return;
                    }
                    coachEvaluationVue.$refs.loadMoreRef.$emit('hui.infinitescroll.finishLoad');
                }, function() {
                    coachEvaluationVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                    return;
                });
            }
        },
        mounted: function() {
            this.$nextTick(function() {
                coachEvaluationCss.use();
                coachEvaluationVue.loadMore();
            });
        },
        destroyed: function() {
            coachEvaluationCss.unuse();
        }
}
</script>

<template>
    <div>
        <v-header>
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
                        <!--大师信息开始-->
                        <div class="ui-cells comment-box">
                            <div class="ui-cell add-master">
                                <div class="reply-photo" :style="[{'background-image': 'url(' + coach.avatar + ')'}, {'background-size': 'cover'}]"></div>
                                <div class="ui-cell-primary add-master-box">
                                    <div>{{coach.realname}}</div>
                                    <div class="ui-nowrap-2" v-html="stripHTML(coach.description)"></div>
                                    <div @click="follow" :style="[coach.followed ? {'color':'#01AAAF'} : '']">
                                        <span class="attention">
                                            <svg class="svg" aria-hidden="true">
                                                <use xlink:href="#icon-like"></use>
                                            </svg>
                                        {{coach.followed ? '已关注' : '关注'}}
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--大师信息结束-->
                        <div class="ui-cells" style="padding-bottom:0.5rem">
                            <div class="ui-cell quality star-score-list">
                                <div>胜诉率高:</div>
                                <div class="ui-cell-primary">
                                    <hui-rate slot="left" v-model="evaluation.qualityLevel" color="#FF7D70" active-color="#FF7D70" size="18px" :readonly="userInfo.role != 1 && order.status == 4"></hui-rate>
                                </div>
                                <div class="score1 quality-score">{{evaluation.qualityLevel}}</div>
                            </div>
                            <div class="ui-cell quality star-score-list">
                                <div>服务保障: </div>
                                <div class="ui-cell-primary">
                                    <hui-rate slot="left" v-model="evaluation.serviceLevel" color="#FF7D70" active-color="#FF7D70" size="18px" :readonly="userInfo.role != 1 && order.status == 4"></hui-rate>
                                </div>
                                <div class="score2 attitude-score">{{evaluation.serviceLevel}}</div>
                            </div>
                            <div class="ui-cell quality star-score-list">
                                <div>价格合理: </div>
                                <div class="ui-cell-primary">
                                    <hui-rate slot="left" v-model="evaluation.replyLevel" color="#FF7D70" active-color="#FF7D70" size="18px" :readonly="userInfo.role != 1 && order.status == 4"></hui-rate>
                                </div>
                                <div class="score3 speed-score">{{evaluation.replyLevel}}</div>
                            </div>
                            <div class="evaluate clearfix">
                                <div class="">
                                    <span>评</span>
                                    <span>价:</span>
                                </div>
                                <div class="comment-tag" v-if="userInfo.role == 1 && order.status == 3">
                                    <span v-for="(row, index) in defindTags" :key="index" :class="in_array(row) > -1 ? 'active' : ''" @click="addTag(row)">{{row}}</span>
                                </div>
                                <div class="comment-tag" v-if="userInfo.role == 1 && order.status == 3">
                                    <span v-for="(row, index) in evaluation.tags">{{row}}</span>
                                </div>
                            </div>
                            <!--用户回复文字开始-->
                            <div class="ui-cell quality" v-if="evaluation.content">{{evaluation.content}}</div>
                            <!--用户回复文字结束-->
                            <!--大师回复开始-->
                            <div class="replay" v-if="evaluation.seller_content">{{evaluation.seller_content}}</div>
                            <!--大师回复结束-->
                            <div class="ui-cell evaluate-box" v-if="(!evaluation.content && userInfo.role == 1) || (!evaluation.seller_content && userInfo.role == 2)">
                                <textarea rows="5" placeholder="请输入内容" v-model="content"></textarea>
                            </div>
                        </div>
                        <!--底部按钮-->
                        <div class="btn-bottom" v-if="(!evaluation.content && userInfo.role == 1) || (!evaluation.seller_content && userInfo.role == 2)">
                            <a href="javascript:void(0);" @click="evaluate">提交</a>
                        </div>
                    </div>
                    <!--页面内容结束-->
                </div>
            </div>
        </v-content>
    </div>
</template>
<script>
var orderEvaluateCss = require('!style-loader/useable!css-loader!../../assets/css/order/evaluate.css');
let orderEvaluateVue;
import util from 'util'
import is from 'is'

export default {
    computed: {
        userInfo() {
            return this.$store.state.app.userInfo || {
                profile: []
            };
        },
    },
    data() {
        return {
            id: this.$route.params.id,
            evaluation: [],
            content: '',
            order: [],
            coach: [],
            defindTags: ['专业技术厉害', '良师益友', '细致耐心', '服务保障炒鸡好', '很有帮助', '准确度高'],
            tags: []
        }
    },
    created: function() {
        orderEvaluateVue = this;
    },
    methods: {
        stripHTML(data) {
            return util.stripHTML(data);
        },
        init() {
            util.post('api.php?entry=app&c=order&a=evaluate&do=display', {
                id: orderEvaluateVue.id
            }, (rs) => {
                if (rs.status == 1) {
                    orderEvaluateVue.order = rs.data.detail;
                    orderEvaluateVue.coach = rs.data.coach;
                    orderEvaluateVue.evaluation = rs.data.evaluation;
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            });
        },
        follow: function() {
            util.post('api.php?entry=app&c=coach&a=detail&do=follow', {
                touid: orderEvaluateVue.coach.uid
            }, (rs) => {
                if (rs.status == 1) {
                    orderEvaluateVue.$set(orderEvaluateVue.coach, 'followed', rs.data);
                    util.toast(rs.message);
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            })
        },
        addTag(tag) {
            let isIn = orderEvaluateVue.in_array(tag);
            if (isIn > -1) {
                orderEvaluateVue.tags.splice(isIn, 1)
            } else {
                orderEvaluateVue.tags.push(tag);
            }
        },
        evaluate() {
            let tags = JSON.stringify(orderEvaluateVue.tags);
            util.post('api.php?entry=app&c=order&a=evaluate&do=post', {
                id: orderEvaluateVue.id,
                tags: tags,
                content: orderEvaluateVue.content,
                qualityLevel: orderEvaluateVue.evaluation.qualityLevel,
                serviceLevel: orderEvaluateVue.evaluation.serviceLevel,
                replyLevel: orderEvaluateVue.evaluation.replyLevel
            }, (rs) => {
                if (rs.status == 1) {
                    orderEvaluateVue.init();
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            });
        },
        in_array(tag) {
            return _.indexOf(orderEvaluateVue.tags, tag);
        }
    },
    mounted: function() {
        this.$nextTick(function() {
            orderEvaluateCss.use();
            orderEvaluateVue.init();
        });
    },
    destroyed: function() {
        orderEvaluateCss.unuse();
    }
}
</script>

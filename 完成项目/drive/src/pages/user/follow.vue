<template>
    <div>
        <v-header title="我的关注">
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
                        <!--中间内容开始-->
                        <hui-pullrefresh :callback="refresh" ref="refreshRef">
                            <hui-infinitescroll :callback="loadMore" ref="loadMoreRef">
                                <div class="ui-cells" slot="list">
                                    <div class="follow-box" v-for="(row, index) in lists">
                                        <router-link class="ui-cell" :to="'/coach/detail/' + row.pid">
                                            <div class="master-img chat-photo" :style="[{'background-image': 'url(' + row.avatar + ')'}, {'background-size': 'cover'}]"></div>
                                            <div class="ui-cell-primary list-box">
                                                <div>{{row.realname}}</div>
                                                <div class="ui-nowrap-2" v-html="stripHTML(row.description)"></div>
                                            </div>
                                        </router-link>
                                        <a href="javascript:void(0);" class="btn-follow" @click="deleteItem(row.id)">取消关注</a>
                                    </div>
                                </div>
                            </hui-infinitescroll>
                        </hui-pullrefresh>
                        <!--中间内容结束-->
                    </div>
                    <!--页面内容结束-->
                </div>
            </div>
        </v-content>
    </div>
</template>
<script>
var userFollowCss = require('!style-loader/useable!css-loader!../../assets/css/user/follow.css');
let userFollowVue;
import util from 'util'
import is from 'is'

export default {
    data() {
            return {
                lists: [],
                page: 1,
            }
        },
        created: function() {
            userFollowVue = this;
        },
        methods: {
            stripHTML(data) {
                return util.stripHTML(data);
            },
            loadFollow: function(successFunc, errorFunc) {
                util.post('api.php?entry=app&c=user&a=follow&do=display', {
                    page: userFollowVue.page
                }, (rs) => {
                    if (rs.status == 1) {
                        if (successFunc) {
                            successFunc(rs.data.length);
                        }
                        if (!is.empty(rs.data)) {
                            userFollowVue.lists.push(...rs.data);
                            userFollowVue.page = userFollowVue.page + 1;
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
                userFollowVue.lists = [];
                userFollowVue.page = 1;
                userFollowVue.loadMore();
                userFollowVue.$refs.refreshRef.$emit('hui.pullrefresh.finishLoad');
            },
            loadMore() {
                userFollowVue.loadFollow(function(length) {
                    if (length <= 0) {
                        userFollowVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                        return;
                    }
                    userFollowVue.$refs.loadMoreRef.$emit('hui.infinitescroll.finishLoad');
                }, function() {
                    userFollowVue.$refs.loadMoreRef.$emit('hui.infinitescroll.loadedDone');
                    return;
                });
            },
            deleteItem(id) {
                util.post('api.php?entry=app&c=user&a=follow&do=delete', {
                    id: id
                }, (rs) => {
                    if (rs.status == 1) {
                        util.toast(rs.message);
                        userFollowVue.refresh();
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
                userFollowCss.use();
                userFollowVue.loadMore();
            });
        },
        destroyed: function() {
            userFollowCss.unuse();
        }
}
</script>

<style lang="less" scoped>
.footer {
    position: absolute;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 50;
    text-align: center;
    border-top: 1px solid #ddd;
    background: #fff;
    ul {
        padding: 5px 0;
        margin: 0;
        li {
            list-style: none;
            a {
                position: relative;
                display: block;
                text-decoration: none;
                color: #222;
                svg {
                    display: block;
                    height: 24px;
                    line-height: 24px;
                    margin: auto;
                    width: 2rem;
                }
                em {
                    line-height: 16px;
                    font-size: 13px;
                    font-style: normal;
                }
                .count {
                    position: absolute;
                    top: 0;
                    left: 50%;
                    z-index: 2;
                    padding: 1px 4px;
                    line-height: 12px;
                    border-radius: 50%;
                    text-align: center;
                    font-size: 12px;
                    opacity: 0.9;
                    color: #fff;
                    background: red;
                }
            }
        }
        .active {
            svg,
            em {
                color: #01AAAF;
            }
        }
    }
}
</style>
<template>
    <footer class="footer" :style="{height: height + 'px'}">
        <slot name="content" v-if="diy">
            <div></div>
        </slot>
        <ul flex="box:mean" v-if="!diy">
            <li :class="{ active: item.path == $route.path }" v-for="item in list">
                <router-link :to="item.path">
                    <svg class="svg" aria-hidden="true">
                        <use :xlink:href="'#' + item.icon"></use>
                    </svg>
                    <em>{{ item.title }}</em>
                </router-link>
            </li>
        </ul>
    </footer>
</template>
<script>
import util from 'util'

export default {
    props: {
        diy: {
            default: false
        },
        height: {
            default: 49
        }
    },
    data() {
        return {
            count: 0,
            list: [{
                title: '首页',
                path: '/home/index',
                icon: 'icon-chao-shouye'
            }, {
                title: '达人',
                path: '/coach/list/0/0/0/0',
                icon: 'icon-chao-daren'
            }, {
                title: '发现',
                path: '/article/list',
                icon: 'icon-chao-faxian'
            }, {
                title: '我的',
                path: '/user/index',
                icon: 'icon-chao-wode'
            }]
        }
    },
    created() {
        this.getCount()
    },
    methods: {
        getCount() {

        }
    }
}
</script>

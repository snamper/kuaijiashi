import 'normalize.css'
import 'flex.css'
import './assets/css/etui/etui.css'
import './assets/css/common/common.css'
import './assets/css/common/swiper.min.css'
import _ from 'lodash'
import base from 'base'
import Vue from 'vue'
import VueRouter from 'vue-router'
Vue.use(VueRouter)
import util from 'util'
import routes from 'routes'
import stores from 'stores'
import is from 'is'
import wx from 'weixin-js-sdk'
import VConsole from 'vconsole'
//var vConsole = new VConsole();
import VueHui from 'vue-hui'
import 'vue-hui/dist/hui.px.css';
Vue.use(VueHui, {
    editor: {
        showModuleName: false,
        icons: {
            text: "custom-icon text",
            color: "custom-icon color",
            font: "custom-icon font",
            align: "custom-icon align",
            list: "custom-icon list",
            link: "custom-icon link",
            unlink: "custom-icon unlink",
            tabulation: "custom-icon table",
            image: "custom-icon image",
            "horizontal-rule": "custom-icon hr",
            eraser: "custom-icon eraser",
            hr: "custom-icon hr",
            undo: "custom-icon undo",
            "full-screen": "custom-icon full-screen",
            info: "custom-icon info ",
        },
        image: {
            uploadHandler(cfn) {
                wx.chooseImage({
                    count: 1,
                    sizeType: ["original", "compressed"],
                    sourceType: ["album", "camera"],
                    success: function(res) {
                        var localIds = res.localIds;
                        wx.uploadImage({
                            localId: localIds[0], // 需要上传的图片的本地ID，由chooseImage接口获得
                            isShowProgressTips: 1, // 默认为1，显示进度提示
                            success: function(res) {
                                var serverId = res.serverId; // 返回图片的服务器端ID
                                util.post('api.php?entry=app&c=utility&a=weUpload&do=uploadMedia', {
                                    media_id: serverId
                                }, (rs) => {
                                    if (rs.status == 1) {
                                        cfn(rs.data);
                                    } else {
                                        util.toast(rs.message);
                                    }
                                }, () => {
                                    util.toast('数据传输失败,请重试')
                                })
                            }
                        });
                    }
                })
            }
        },
        language: "zh-cn",
        i18n: {
            "zh-cn": {
                "align": "对齐方式",
                "image": "图片",
                "list": "列表",
                "link": "链接",
                "unlink": "去除链接",
                "table": "表格",
                "font": "文字",
                "full screen": "全屏",
                "text": "排版",
                "eraser": "格式清除",
                "info": "关于",
                "color": "颜色",
                "please enter a url": "请输入地址",
                "create link": "创建链接",
                "bold": "加粗",
                "italic": "倾斜",
                "underline": "下划线",
                "strike through": "删除线",
                "subscript": "上标",
                "superscript": "下标",
                "heading": "标题",
                "font name": "字体",
                "font size": "文字大小",
                "left justify": "左对齐",
                "center justify": "居中",
                "right justify": "右对齐",
                "ordered list": "有序列表",
                "unordered list": "无序列表",
                "fore color": "前景色",
                "background color": "背景色",
                "row count": "行数",
                "column count": "列数",
                "save": "确定",
                "upload": "上传",
                "progress": "进度",
                "unknown": "未知",
                "please wait": "请稍等",
                "error": "错误",
                "abort": "中断",
                "reset": "重置"
            }
        },
        hiddenModules: [],
        visibleModules: ["text", "color", "font", "align", "list", "link", "unlink", "tabulation", "image", "hr", "eraser", "undo", "full-screen"],
        modules: {}
    }
});
import components from './components/' // 加载公共组件
Object.keys(components).forEach((key) => {
    var name = key.replace(/(\w)/, (v) => v.toUpperCase()) // 首字母大写
    Vue.component(`v${name}`, components[key])
})
import iconfont from './util/iconfont'
const router = new VueRouter({
    routes,
    base: '/'
})
router.beforeEach(function({
    meta,
    path,
    name
}, from, next) {
    console.log('path:', path);
    if (path == '/') {
        return next({
            path: '/home/index'
        });
    }
    if (meta.auth && is.empty(stores.state.app.userInfo)) {
        return next({
            path: '/account/login'
        });
    }
    if (meta.auth) {
        util.post("api.php?entry=app&c=user&a=profile", {}, (rs) => {
            if (rs.status == '1') {
                Promise.resolve().then(function() {
                    initApp.$store.commit('set_userInfo', rs.data.userInfo);
                    initApp.$store.commit('set_token', rs.data.token);
                }).then(function() {
                    return next();
                }).catch(function(err) {
                    console.log(err);
                    initApp.$Toast(err);
                });
            } else {
                util.toast(rs.message);
                initApp.$router.push('/account/login');
                return false;
            }
        }, () => {
            util.toast('验证失败');
        });
    } else {
        next();
    }
});
router.afterEach(function({
    meta,
    path,
    name
}, from, next) {
    if (util.isWechat()) {
        util.directRightUrl('/wechat/');
    }
});
let app = {
    initialize: function() {
        app.bindEvents();
    },
    bindEvents: function() {
        Promise.resolve().then(function() {
            window.initApp = new Vue({
                store: stores,
                router
            }).$mount('#app');
        }).then(function() {
            window.onload = app.onDeviceReady();
        }).catch(function(err) {
            console.log(err);
            Vue.prototype.$Toast(err);
        });
    },
    onDeviceReady: function() {
        app.getCurrentPosition();
    },
    getCurrentPosition: function() {
        if (is.empty(stores.state.app.location)) {
            if (util.isWechat()) {
                let params = {
                    debug: false,
                    url: window.location.href.split('#')[0],
                    jsApiList: ['chooseImage', 'previewImage', 'uploadImage', 'downloadImage', 'checkJsApi']
                };
                util.getJsConfig(params, (err, obj) => {
                    if (err) {
                        return util.toast(err);
                    }
                    wx.config(obj);
                    wx.ready(() => {
                        console.log('wx.ready');
                        wx.getLocation({
                            type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
                            success: function(res) {
                                var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
                                var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
                                var speed = res.speed; // 速度，以米/每秒计
                                var accuracy = res.accuracy; // 位置精度
                                util.locateCity(latitude, longitude, 'wgs84ll', function(data) {
                                    initApp.$store.commit('set_location', {
                                        city: data.city,
                                        latitude: data.location.lat, //纬度
                                        longitude: data.location.lng //经度
                                    });
                                    console.log('微信定位成功');
                                    Vue.prototype.$Toast('定位成功', 'bottom');
                                }, function() {
                                    Vue.prototype.$Toast('定位失败', 'bottom');
                                });
                            }
                        });
                    });
                    wx.error(function(res) {
                        console.log('wx err', res);
                        //可以更新签名
                    });
                });
            }
        }
    }
};
app.initialize();
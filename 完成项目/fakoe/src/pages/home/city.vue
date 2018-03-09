<template>
    <div>
        <v-header>
            <div slot="left" class="item" flex="main:center cross:center" @click="$router.go(-1)">
                <svg class="svg" aria-hidden="true">
                    <use xlink:href="#icon-back"></use>
                </svg>
            </div>
            <div slot="center" class="box">
                <div class="form">
                    <svg class="svg" aria-hidden="true">
                        <use xlink:href="#icon-search"></use>
                    </svg>
                    <input placeholder="搜索" type="text" v-model="keyword" />
                </div>
            </div>
            <div slot="right" class="item" flex="main:center cross:center" @click="reset">
                <svg class="svg" aria-hidden="true">
                    <use xlink:href="#icon-location"></use>
                </svg>
            </div>
        </v-header>
        <v-content bottomHeight="0">
            <section v-if="keyword">
                <ul style="background: #fff;">
                    <li class="city-cell" v-for="item in searchCityData" @click="changecity(item.Name)">{{item.Name}}
                        <span>{{item.CityCode}}</span>
                    </li>
                </ul>
            </section>
            <section v-if="!keyword">
                <div class="mint-indexlist">
                    <ul class="mint-indexlist-content" style="margin-right: 0px; height: 667px;">
                        <li class="mint-indexsection">
                            <p class="mint-indexsection-index" style="background:#f5f5f5">定位/常用</p>
                            <ul class="hot-city clearfix" style="padding-bottom:0">
                                <li class="hot-city-cell current-place active" @click="changecity(location.city)">
                                    <i class="iconfont icon-dizhi"></i>{{location.city}}</li>
                            </ul>
                        </li>
                        <li class="mint-indexsection">
                            <p class="mint-indexsection-index" style="background:#f5f5f5">热门城市</p>
                            <ul class="hot-city clearfix">
                                <li class="hot-city-cell" v-for="(item, index) in Hcity" :key="index" @click="changecity(item.Name)">{{item.Name}}</li>
                            </ul>
                        </li>
                        <li class="mint-indexsection" v-for="(item, key) in resolveCityData" :key="key">
                            <p class="mint-indexsection-index">{{key}}</p>
                            <ul>
                                <li class="city-cell" v-for="(it, itkey) in item" :key="itkey" @click="changecity(it.Name)">{{it.Name}}
                                    <span>{{it.CityCode}}</span>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </section>
        </v-content>
    </div>
</template>
<script>
let homeCityCss = require('!style-loader/useable!css-loader!../../assets/css/home/city.css');
let homeCityVue;
import util from 'util'
import is from 'is'
import wx from 'weixin-js-sdk'
import {
    Hcity,
    Cdata
} from '../../util/city.data'
export default {
    computed: {
        location() {
            return this.$store.state.app.location || {};
        },
        resolveCityData() {
            let ob = {}
            this.Cdata.Cities.forEach((cities) => {
                let key = cities.Code[0];
                if (ob[key] == undefined) {
                    ob[key] = new Array();
                }
                ob[key].push(cities);
            });
            return ob;
        }
    },
    watch: {
        keyword(newValue) {
            if (newValue === '') {
                this.searchCityData = [];
            } else {
                this.searchCityData = this.Cdata.Cities.filter(cities => cities.CityCode.toLowerCase().indexOf(this.keyword.toLowerCase()) !== -1 || cities.Code.toLowerCase().indexOf(this.keyword.toLowerCase()) !== -1 || cities.Name.indexOf(this.keyword) !== -1);
            }
        }
    },
    data() {
        return {
            keyword: '',
            Hcity,
            Cdata,
            Tcity: '',
            searchCityData: []
        }
    },
    created: function() {
        homeCityVue = this;
        if (util.isWechat()) {
            let params = {
                debug: false,
                url: window.location.href.split('#')[0],
            };
            util.getJsConfig(params, (err, obj) => {
                if (err) {
                    return util.toast(err);
                }

                console.log('jsconfig ', obj);

                wx.config(obj);

                wx.ready(() => {
                    console.log('wx.ready');
                });

                wx.error(function(res) {
                    console.log('wx err', res);
                    //可以更新签名
                });
            });
        }
    },
    methods: {
        changecity(city) {
            util.post('api.php?entry=app&c=normal&a=lbs&do=coordinate', {
                city: city
            }, (rs) => {
                if (rs.status == 1) {
                    var coordinate = rs.data;
                    if (!is.empty(coordinate)) {
                        homeCityVue.$store.commit('set_location', {
                            city: city,
                            latitude: coordinate.lat,
                            longitude: coordinate.lng
                        });
                        homeCityVue.$router.push('/home/index');
                    }
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('获取数据失败')
            });

        },
        reset: function() {
            homeCityVue.$Loading('开始定位');
            wx.getLocation({
                type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
                success: function(res) {
                    var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
                    var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
                    var speed = res.speed; // 速度，以米/每秒计
                    var accuracy = res.accuracy; // 位置精度
                    util.locateCity(latitude, longitude, 'wgs84ll', function(data) {
                        homeCityVue.$store.commit('set_location', {
                            city: data.city,
                            latitude: data.location.lat, //纬度
                            longitude: data.location.lng //经度
                        });
                        homeCityVue.$Loading.done();
                        util.toast('定位成功', 'bottom');
                    }, function() {
                        homeCityVue.$Loading.done();
                        util.toast('定位失败', 'bottom');
                    });
                }
            });

        }
    },
    mounted: function() {
        this.$nextTick(function() {
            homeCityCss.use();
        })
    },
    destroyed: function() {
        homeCityCss.unuse();
    }
}
</script>

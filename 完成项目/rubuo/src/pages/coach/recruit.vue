<template>
    <div>
        <v-header title="成为达人">
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
                        <!--第一步-->
                        <div>
                            <!--填写信息开始-->
                            <div class="info-box">
                                <div class="ui-cell item">
                                    <div class="ui-cell-lt" style="margin-right: 0.5rem;">头像</div>
                                    <div class="ui-cell-primary">
                                        <div class="up-img">
                                            <div class="small-img">
                                                <div class="add-img" style="display: inline-block;" @click="uploader('avatar')" v-if="!avatar">
                                                    <div style="margin-top: 1rem;"><i class="ui-icon icon-bangbangce-zengjia"></i></div>
                                                    <div>上传图片</div>
                                                </div>
                                                <div class="img-box" v-if="avatar">
                                                    <img :src="avatar" />
                                                    <div class="delete-box" @click="deleteItem('avatar')"><img src="../../assets/images/reduce.png"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ui-cell item-list">
                                    <div>真实姓名</div>
                                    <div class="ui-cell-primary">
                                        <input type="text" class="changeInput" v-model="realname" placeholder="与身份证上一致">
                                    </div>
                                </div>
                                <div class="ui-cell item-list">
                                    <div>手机号码</div>
                                    <div class="ui-cell-primary">
                                        <input type="tel" v-model="mobile" placeholder="请输入手机号码">
                                    </div>
                                </div>
                                <div class="ui-cell item-list">
                                    <div>性 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 别</div>
                                    <div class="ui-cell-primary sex-box">
                                        <label class="ui-label-checkbox ui-item-content">
                                            <input type="radio" class="sex sex-man" v-model="gender" value="1">
                                            <div class="ui-item-media"><i class="ui-icon ui-icon-form-checkbox"></i>男</div>
                                        </label>
                                        <label class="ui-label-checkbox ui-item-content">
                                            <input type="radio" class="sex sex-girl" v-model="gender" value="2">
                                            <div class="ui-item-media"><i class="ui-icon ui-icon-form-checkbox"></i>女</div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!--填写信息结束-->
                            <!--证件信息开始-->
                            <div class="card-info">
                                <div class="ui-cell item-list">
                                    <div>身份证号码</div>
                                    <div class="ui-cell-primary">
                                        <input type="text" class="changeInput" v-model="idcard" placeholder="请准确填写身份证号码" data-params="cardnum">
                                    </div>
                                </div>
                                <div class="ui-cell item">
                                    <div class="ui-cell-lt" style="margin-right: 0.5rem;">身份证正面</div>
                                    <div class="ui-cell-primary">
                                        <div class="up-img">
                                            <div class="small-img">
                                                <div class="add-img" style="display: inline-block;" @click="uploader('idcard_front')" v-if="!idcard_front">
                                                    <div style="margin-top: 1rem;"><i class="ui-icon icon-bangbangce-zengjia"></i></div>
                                                    <div>上传图片</div>
                                                </div>
                                                <div class="img-box" v-if="idcard_front">
                                                    <img :src="idcard_front" />
                                                    <div class="delete-box" @click="deleteItem('idcard_front')"><img src="../../assets/images/reduce.png"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ui-cell item">
                                    <div class="ui-cell-lt" style="margin-right: 0.5rem;">身份证反面</div>
                                    <div class="ui-cell-primary">
                                        <div class="up-img">
                                            <div class="small-img1">
                                                <div class="add-img1" style="display: inline-block;" @click="uploader('idcard_reverse')" v-if="!idcard_reverse">
                                                    <div style="margin-top: 1rem;"><i class="ui-icon icon-bangbangce-zengjia"></i></div>
                                                    <div>上传图片</div>
                                                </div>
                                                <div class="img-box1" v-if="idcard_reverse">
                                                    <img :src="idcard_reverse" />
                                                    <div class="delete-box1" @click="deleteItem('idcard_reverse')"><img src="../../assets/images/reduce.png"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ui-cell item">
                                    <div class="ui-cell-lt" style="margin-right: 0.5rem;">工作照</div>
                                    <div class="ui-cell-primary">
                                        <div class="up-img">
                                            <div class="small-img">
                                                <div class="add-img" style="display: inline-block;" @click="uploader('licence_front')" v-if="!licence_front">
                                                    <div style="margin-top: 1rem;"><i class="ui-icon icon-bangbangce-zengjia"></i></div>
                                                    <div>上传图片</div>
                                                </div>
                                                <div class="img-box" v-if="licence_front">
                                                    <img :src="licence_front" />
                                                    <div class="delete-box" @click="deleteItem('licence_front')"><img src="../../assets/images/reduce.png"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ui-cell item">
                                    <div class="ui-cell-lt" style="margin-right: 0.5rem;">服务场所</div>
                                    <div class="ui-cell-primary">
                                        <div class="up-img">
                                            <div class="small-img1">
                                                <div class="add-img1" style="display: inline-block;" @click="uploader('licence_reverse')" v-if="!licence_reverse">
                                                    <div style="margin-top: 1rem;"><i class="ui-icon icon-bangbangce-zengjia"></i></div>
                                                    <div>上传图片</div>
                                                </div>
                                                <div class="img-box1" v-if="licence_reverse">
                                                    <img :src="licence_reverse" />
                                                    <div class="delete-box1" @click="deleteItem('licence_reverse')"><img src="../../assets/images/reduce.png"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--证件信息结束-->
                            <div class="info-box">
                                <div class="ui-cell item-list">
                                    <div>信用级别</div>
                                    <div class="ui-cell-primary sex-box">
                                        <label class="ui-label-checkbox ui-item-content">
                                            <input type="radio" class="sex sex-man" v-model="type" value="AAA">
                                            <div class="ui-item-media"><i class="ui-icon ui-icon-form-checkbox"></i>C1</div>
                                        </label>
                                        <label class="ui-label-checkbox ui-item-content">
                                            <input type="radio" class="sex sex-girl" v-model="type" value="AA">
                                            <div class="ui-item-media"><i class="ui-icon ui-icon-form-checkbox"></i>C2</div>
                                        </label>
                                        <label class="ui-label-checkbox ui-item-content">
                                            <input type="radio" class="sex sex-girl" v-model="type" value="A">
                                            <div class="ui-item-media"><i class="ui-icon ui-icon-form-checkbox"></i>B2</div>
                                        </label>
                                    </div>
                                </div>
                                <div class="ui-cell item-list">
                                    <div>所在区域</div>
                                    <div class="ui-cell-primary">
                                        <input class="changeInput" type="text" @click.stop="toShowCitySelect" v-model="reside" readonly>
                                    </div>
                                </div>
                                <hui-cityselect v-model="showCitySelect" :callback="toCitySelect" :items="cityData" :province="province" :city="city" :area="area" :ready="ready"></hui-cityselect>
                                <div class="ui-cell item-list">
                                    <div>服务价格</div>
                                    <div class="ui-cell-primary">
                                        <input class="changeInput" type="text" v-model="price">
                                    </div>
                                    <div>元</div>
                                </div>
                                <div class="ui-cell item-list">
                                    <div>从业年限</div>
                                    <div class="ui-cell-primary">
                                        <input class="changeInput" type="text" v-model="year">
                                    </div>
                                    <div>年</div>
                                </div>
                                <div class="ui-cell textarea">
                                    <div class="ui-cell-lt">从业经验</div>
                                    <div class="ui-cell-primary">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hui-editor :content="description" @change="updateContent" @click.native="clearDefault"></hui-editor>
                        <!--第一步结束-->
                        <!--按钮-->
                        <div class="btn" @click="submit">提交</div>
                    </div>
                    <!--页面内容结束-->
                </div>
            </div>
        </v-content>
    </div>
</template>
<script>
var coachRecruitCss = require('!style-loader/useable!css-loader!../../assets/css/coach/recruit.css');
let coachRecruitVue;
import util from 'util'
import is from 'is'
import wx from 'weixin-js-sdk'
export default {
    computed: {
        location() {
            return this.$store.state.app.location || {};
        },
    },
    data() {
        return {
            avatar: '',
            realname: '',
            mobile: '',
            gender: '',
            idcard: '',
            idcard_front: '',
            idcard_reverse: '',
            licence_front: '',
            licence_reverse: '',
            price: '',
            year: '',
            description: '',
            province: '北京市',
            city: '市辖区',
            area: '东城区',
            reside: '',
            cityData: [],
            showCitySelect: false,
            ready: false,
            type: ''
        }
    },
    created: function() {
        coachRecruitVue = this;
    },
    methods: {
        init() {
            util.post('api.php?entry=app&c=coach&a=recruit&do=display', {}, (rs) => {
                if (rs.status == 1) {
                    if (!is.empty(rs.data)) {
                        coachRecruitVue.avatar = rs.data.coach.avatar;
                        coachRecruitVue.realname = rs.data.coach.realname;
                        coachRecruitVue.mobile = rs.data.coach.mobile;
                        coachRecruitVue.gender = rs.data.coach.gender;
                        coachRecruitVue.idcard = rs.data.coach.idcard;
                        coachRecruitVue.idcard_front = rs.data.coach.idcard_front;
                        coachRecruitVue.idcard_reverse = rs.data.coach.idcard_reverse;
                        coachRecruitVue.licence_front = rs.data.coach.licence_front;
                        coachRecruitVue.licence_reverse = rs.data.coach.licence_reverse;
                        coachRecruitVue.price = rs.data.coach.price;
                        coachRecruitVue.year = rs.data.coach.year;
                        coachRecruitVue.description = rs.data.coach.description;
                        coachRecruitVue.province = rs.data.coach.province;
                        coachRecruitVue.city = rs.data.coach.city;
                        coachRecruitVue.area = rs.data.coach.area;
                        coachRecruitVue.cityData = rs.data.cityData;
                        coachRecruitVue.ready = true;
                        coachRecruitVue.type = rs.data.coach.type;
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
                    }
                    coachRecruitVue.reside = coachRecruitVue.province + ' ' + coachRecruitVue.city + ' ' + coachRecruitVue.area;
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            })
        },
        uploader(name) {
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
                                    coachRecruitVue[name] = rs.data;
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
        },
        deleteItem(name) {
            coachRecruitVue[name] = '';
        },
        clearDefault() {
            if (coachRecruitVue.description == '请输入正文……') {
                coachRecruitVue.description = '';
            }
        },
        updateContent(data) {
            coachRecruitVue.description = data;
        },
        toShowCitySelect() {
            coachRecruitVue.showCitySelect = true;
        },
        toCitySelect(ret) {
            console.log(ret);
            coachRecruitVue.province = ret.itemName1;
            coachRecruitVue.city = ret.itemName2;
            coachRecruitVue.area = ret.itemName3;
            coachRecruitVue.reside = coachRecruitVue.province + ' ' + coachRecruitVue.city + ' ' + coachRecruitVue.area;
        },
        submit() {
            if (!coachRecruitVue.avatar) {
                util.toast('请上传头像');
                return false;
            }
            if (!coachRecruitVue.realname) {
                util.toast('请填写真实姓名');
                return false;
            }
            if (!coachRecruitVue.mobile) {
                util.toast('请填写手机号码');
                return false;
            }
            if (!coachRecruitVue.gender) {
                util.toast('请选择性别');
                return false;
            }
            if (!coachRecruitVue.idcard) {
                util.toast('请填写身份证号码');
                return false;
            }
            if (!coachRecruitVue.idcard_front) {
                util.toast('请上传身份证正面照片');
                return false;
            }
            if (!coachRecruitVue.idcard_reverse) {
                util.toast('请上传身份证反面照片');
                return false;
            }
            if (!coachRecruitVue.licence_front) {
                util.toast('请上传驾驶证正面照片');
                return false;
            }
            if (!coachRecruitVue.licence_front) {
                util.toast('请上传驾驶证正面照片');
                return false;
            }
            if (!coachRecruitVue.price) {
                util.toast('请填写服务价格');
                return false;
            }
            if (!coachRecruitVue.year) {
                util.toast('请填写从业年限');
                return false;
            }
            if (!coachRecruitVue.description) {
                util.toast('请填写从业经验');
                return false;
            }
            if (!coachRecruitVue.type) {
                util.toast('请选择信用级别');
                return false;
            }
            util.post('api.php?entry=app&c=coach&a=recruit&do=post', {
                avatar: coachRecruitVue.avatar,
                realname: coachRecruitVue.realname,
                mobile: coachRecruitVue.mobile,
                gender: coachRecruitVue.gender,
                idcard: coachRecruitVue.idcard,
                idcard_front: coachRecruitVue.idcard_front,
                idcard_reverse: coachRecruitVue.idcard_reverse,
                licence_front: coachRecruitVue.licence_front,
                licence_reverse: coachRecruitVue.licence_reverse,
                price: coachRecruitVue.price,
                year: coachRecruitVue.year,
                description: coachRecruitVue.description,
                reside: coachRecruitVue.province + ' ' + coachRecruitVue.city + ' ' + coachRecruitVue.area,
                type: coachRecruitVue.type
            }, (rs) => {
                if (rs.status == 1) {
                    util.toast(rs.message);
                    coachRecruitVue.$router.push('/user/index');
                } else {
                    util.toast(rs.message);
                }
            }, () => {
                util.toast('数据传输失败,请重试')
            })
        }
    },
    mounted: function() {
        this.$nextTick(function() {
            coachRecruitCss.use();
            coachRecruitVue.init();
        });
    },
    destroyed: function() {
        coachRecruitCss.unuse();
    }
}
</script>

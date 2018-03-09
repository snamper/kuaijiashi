import ajax from './ajax'
import is from 'is'
import stores from 'stores'
import base from 'base'
import Vue from 'vue'
ajax.beforeEach((res, next) => {
    let accesstoken = stores.state.app.token;
    if (accesstoken) {
        res.data.appkey = accesstoken;
    }
    res.url = base.target + res.url;
    next();
})
ajax.afterEach((res, next) => {
    if (res) {
        next()
    } else {
        Vue.prototype.$Toast('加载失败', 'bottom')
    }
})
export default {
    stripHTML(data) {
        var reTag = /<(?:.|\s)*?>/g;
        return data.replace(reTag, "");
    },
    get(url, data = {}, success = () => {}, error = () => {}) {
        ajax({
            url,
            data,
            success,
            error,
            type: 'GET'
        })
    },
    post(url, data = {}, success = () => {}, error = () => {}) {
        ajax({
            url,
            data,
            success,
            error,
            type: 'POST'
        })
    },
    updateUserInfo() {
        this.post("api.php?entry=app&c=user&a=profile", {}, (rs) => {
            if (rs.status == '1') {
                stores.commit('set_userInfo', rs.data.userInfo);
                stores.commit('set_token', rs.data.token);
            } else {
                Vue.prototype.$Toast(rs.message);
                stores.commit('set_login', true);
                return false;
            }
        }, () => {
            Vue.prototype.$Toast('验证失败');
        });
    },
    toast(message = '', time = 2000) {
        console.log(message);
        Vue.prototype.$Toast(message, 'bottom', time);
    },
    directRightUrl(secondPathNmae) {
        let {
            href,
            protocol,
            host,
            search,
            hash
        } = window.location;
        const pathname = secondPathNmae ? secondPathNmae : ''; // 解决支付路径问题添加的前缀，替换成你的
        search = search ? search : '?';
        hash = hash ? hash : '#/';
        let newHref = protocol + '//' + host + pathname + search + hash;
        if (newHref !== href) {
            window.location.replace(newHref);
        }
    },
    getJsConfig(params, callback) {
        this.post('api.php?entry=app&c=normal&a=getJsConfig', params, (rs) => {
            if (rs.status == 1) {
                // jssdk config 对象
                var jssdkconfig = rs.data || {};
                // 是否启用调试
                jssdkconfig.debug = params.debug;
                jssdkconfig.jsApiList = ['checkJsApi', 'onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem', 'translateVoice', 'startRecord', 'stopRecord', 'onRecordEnd', 'playVoice', 'pauseVoice', 'stopVoice', 'uploadVoice', 'downloadVoice', 'chooseImage', 'previewImage', 'uploadImage', 'downloadImage', 'getNetworkType', 'openLocation', 'getLocation', 'hideOptionMenu', 'showOptionMenu', 'closeWindow', 'scanQRCode', 'chooseWXPay', 'openProductSpecificView', 'addCard', 'chooseCard', 'openCard']
                callback(null, jssdkconfig);
            } else {
                callback(rs.message);
            }
        }, () => {
            Vue.prototype.$Toast('数据传输失败,请重试')
        })
    },
    isCordova: function() {
        return (typeof(cordova) !== 'undefined' || typeof(phonegap) !== 'undefined');
    },
    isWechat() {
        let ua = window.navigator.userAgent.toLowerCase();
        if (ua.match(/MicroMessenger/i) == 'micromessenger') {
            return true;
        }
        return false;
    },
    tomedia: function(a) {
        return a ? 0 == a.indexOf("http://") || 0 == a.indexOf("https://") ? a : base.ossUrl + a : "";
    },
    toLink: function(a) {
        return a ? 0 == a.indexOf("http://") || 0 == a.indexOf("https://") ? a : "http://" + a : "";
    },
    dateFormat: function(dateTimeStamp) {
        function unify(time) {
            time -= 0;
            if (("" + time).length === 10) {
                time *= 1000;
            }
            return time;
        }

        function ago(time) {
            var ago, curTime, diff, int;
            time = unify(time);
            int = parseInt;
            curTime = +new Date();
            diff = curTime - time;
            ago = "";
            if (1000 * 60 > diff) {
                ago = int(diff / 1000) + "秒前";
            } else if (1000 * 60 <= diff && 1000 * 60 * 60 > diff) {
                ago = int(diff / (1000 * 60)) + "分钟前";
            } else if (1000 * 60 * 60 <= diff && 1000 * 60 * 60 * 24 > diff) {
                ago = int(diff / (1000 * 60 * 60)) + "小时前";
            } else if (1000 * 60 * 60 * 24 <= diff && 1000 * 60 * 60 * 24 * 30 > diff) {
                ago = int(diff / (1000 * 60 * 60 * 24)) + "天前";
            } else if (1000 * 60 * 60 * 24 * 30 <= diff && 1000 * 60 * 60 * 24 * 30 * 12 > diff) {
                ago = int(diff / (1000 * 60 * 60 * 24 * 30)) + "月前";
            } else {
                ago = int(diff / (1000 * 60 * 60 * 24 * 30 * 12)) + "年前";
            }
            return ago;
        }
        return ago(dateTimeStamp);
    },
    iosdb: function(averagePower) {
        var level; // The linear 0.0 .. 1.0 value we need.
        var minDecibels = -80.0; // Or use -60dB, which I measured in a silent room.
        var decibels = averagePower;
        if (decibels < minDecibels) {
            level = 0.0;
        } else if (decibels >= 0.0) {
            level = 1.0;
        } else {
            var root = 2.0;
            var minAmp = Math.pow(10.0, 0.05 * minDecibels);
            var inverseAmpRange = 1.0 / (1.0 - minAmp);
            var amp = Math.pow(10.0, 0.05 * decibels);
            var adjAmp = (amp - minAmp) * inverseAmpRange;
            level = Math.pow(adjAmp, 1.0 / root);
        }
        return level * 120;
    },
    date: function(format, timestamp) {
        var that = this;
        var jsdate, f;
        var txt_words = ["Sun", "Mon", "Tues", "Wednes", "Thurs", "Fri", "Satur", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        var formatChr = /\\?(.?)/gi;
        var formatChrCb = function(t, s) {
            return f[t] ? f[t]() : s
        };
        var _pad = function(n, c) {
            n = String(n);
            while (n.length < c) {
                n = "0" + n
            }
            return n
        };
        f = {
            d: function() {
                return _pad(f.j(), 2)
            },
            D: function() {
                return f.l().slice(0, 3)
            },
            j: function() {
                return jsdate.getDate()
            },
            l: function() {
                return txt_words[f.w()] + "day"
            },
            N: function() {
                return f.w() || 7
            },
            S: function() {
                var j = f.j();
                var i = j % 10;
                if (i <= 3 && parseInt((j % 100) / 10, 10) == 1) {
                    i = 0
                }
                return ["st", "nd", "rd"][i - 1] || "th"
            },
            w: function() {
                return jsdate.getDay()
            },
            z: function() {
                var a = new Date(f.Y(), f.n() - 1, f.j());
                var b = new Date(f.Y(), 0, 1);
                return Math.round((a - b) / 86400000)
            },
            W: function() {
                var a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3);
                var b = new Date(a.getFullYear(), 0, 4);
                return _pad(1 + Math.round((a - b) / 86400000 / 7), 2)
            },
            F: function() {
                return txt_words[6 + f.n()]
            },
            m: function() {
                return _pad(f.n(), 2)
            },
            M: function() {
                return f.F().slice(0, 3)
            },
            n: function() {
                return jsdate.getMonth() + 1
            },
            t: function() {
                return (new Date(f.Y(), f.n(), 0)).getDate()
            },
            L: function() {
                var j = f.Y();
                return j % 4 === 0 & j % 100 !== 0 | j % 400 === 0
            },
            o: function() {
                var n = f.n();
                var W = f.W();
                var Y = f.Y();
                return Y + (n === 12 && W < 9 ? 1 : n === 1 && W > 9 ? -1 : 0)
            },
            Y: function() {
                return jsdate.getFullYear()
            },
            y: function() {
                return f.Y().toString().slice(-2)
            },
            a: function() {
                return jsdate.getHours() > 11 ? "pm" : "am"
            },
            A: function() {
                return f.a().toUpperCase()
            },
            B: function() {
                var H = jsdate.getUTCHours() * 3600;
                var i = jsdate.getUTCMinutes() * 60;
                var s = jsdate.getUTCSeconds();
                return _pad(Math.floor((H + i + s + 3600) / 86.4) % 1000, 3)
            },
            g: function() {
                return f.G() % 12 || 12
            },
            G: function() {
                return jsdate.getHours()
            },
            h: function() {
                return _pad(f.g(), 2)
            },
            H: function() {
                return _pad(f.G(), 2)
            },
            i: function() {
                return _pad(jsdate.getMinutes(), 2)
            },
            s: function() {
                return _pad(jsdate.getSeconds(), 2)
            },
            u: function() {
                return _pad(jsdate.getMilliseconds() * 1000, 6)
            },
            e: function() {
                throw "Not supported (see source code of date() for timezone on how to add support)"
            },
            I: function() {
                var a = new Date(f.Y(), 0);
                var c = Date.UTC(f.Y(), 0);
                var b = new Date(f.Y(), 6);
                var d = Date.UTC(f.Y(), 6);
                return ((a - c) !== (b - d)) ? 1 : 0
            },
            O: function() {
                var tzo = jsdate.getTimezoneOffset();
                var a = Math.abs(tzo);
                return (tzo > 0 ? "-" : "+") + _pad(Math.floor(a / 60) * 100 + a % 60, 4)
            },
            P: function() {
                var O = f.O();
                return (O.substr(0, 3) + ":" + O.substr(3, 2))
            },
            T: function() {
                return "UTC"
            },
            Z: function() {
                return -jsdate.getTimezoneOffset() * 60
            },
            c: function() {
                return "Y-m-d\\TH:i:sP".replace(formatChr, formatChrCb)
            },
            r: function() {
                return "D, d M Y H:i:s O".replace(formatChr, formatChrCb)
            },
            U: function() {
                return jsdate / 1000 | 0
            }
        };
        this.date = function(format, timestamp) {
            that = this;
            jsdate = (timestamp === undefined ? new Date() : (timestamp instanceof Date) ? new Date(timestamp) : new Date(timestamp * 1000));
            return format.replace(formatChr, formatChrCb)
        };
        return this.date(format, timestamp);
    },
    formatSeconds: function(value) {
        var theTime = parseInt(value); // 秒  
        var theTime1 = 0; // 分  
        var theTime2 = 0; // 小时  
        if (theTime > 60) {
            theTime1 = parseInt(theTime / 60);
            theTime = parseInt(theTime % 60);
            if (theTime1 > 60) {
                theTime2 = parseInt(theTime1 / 60);
                theTime1 = parseInt(theTime1 % 60);
            }
        }
        var result = "" + parseInt(theTime) + "秒";
        if (theTime1 > 0) {
            result = "" + parseInt(theTime1) + "分" + result;
        }
        if (theTime2 > 0) {
            result = "" + parseInt(theTime2) + "小时" + result;
        }
        return result;
    },
    exitApp: function() {
        navigator.app.exitApp();
    },
    checkFile: function(file) {
        window.resolveLocalFileSystemURL(file, function(root) {
            return true;
        }, function(err) {
            return false;
        });
    },
    deleteFile: function(file) {
        window.resolveLocalFileSystemURL(file, function(fileEntry) {
            fileEntry.remove(function() {
                return true;
            }, function(err) {
                return false;
            }, function() {
                return false;
            });
        })
    },
    createAndWriteFile: function(folder) {
        var thisAppFunc = this;
        var path = '';
        if (device.platform == "iOS") {
            path = cordova.file.tempDirectory;
        } else if (device.platform == "Android") {
            path = cordova.file.externalRootDirectory;
        }
        window.resolveLocalFileSystemURL(path, function(fileEntry) {
            fileEntry.getDirectory('holdskill', {
                create: true,
                exclusive: false
            }, function(dirEntry) {
                dirEntry.getDirectory(folder, {
                    create: true,
                    exclusive: false
                }, function(subfolder) {
                    return subfolder;
                }, thisAppFunc.onErrorGetDir);
            }, thisAppFunc.onErrorGetDir);
        }, thisAppFunc.onErrorLoadFs);
    },
    base64StrFromMp3: function(path, callback) { //转base64的代码
        window.resolveLocalFileSystemURL(path, gotFile, fail);

        function fail(e) {
            Vue.prototype.$Toast('Cannot found requested file', 'bottom');
        }

        function gotFile(fileEntry) {
            fileEntry.file(function(file) {
                var reader = new FileReader();
                reader.onloadend = function(e) {
                    var content = this.result;
                    callback(content);
                };
                // The most important point, use the readAsDatURL Method from the file plugin
                reader.readAsDataURL(file);
            });
        }
    },
    onErrorGetDir: function(error) { //文件夹创建失败回调
        Vue.prototype.$Toast("文件夹创建失败！", 'bottom');
        return false;
    },
    onErrorLoadFs: function(error) { //FileSystem加载失败回调
        Vue.prototype.$Toast("文件系统加载失败！", 'bottom');
        return false;
    },
    uploadImages: function(func, maxnum) {
        var thisAppFunc = this;
        ImagePicker.getPictures(function(results) {
            if (results.length > 0) {
                Vue.prototype.$Loading('上传中...');
                var uploadPromises = results.map(uploadFunction);
                var sequence = Promise.resolve();
                uploadPromises.forEach(function(curPromise) {
                    sequence = sequence.then(function() {
                        return curPromise;
                    }).then(function(url) {
                        Vue.prototype.$Loading.done();
                        func(url);
                    }).catch(function(err) {
                        console.log(err);
                        Vue.prototype.$Toast(err, 'bottom');
                    });
                });
            }
        }, function(error) {
            console.log('Error: ' + error);
        }, {
            maximumImagesCount: maxnum ? maxnum : 1,
            width: 1920,
            height: 1440,
            quality: 100
        });

        function uploadFunction(url) {
            return new Promise(function(resolve, reject) {
                let accesstoken = stores.state.app.token;
                var uri = encodeURI(HS.url + 'api.php?entry=app&c=utility&a=file&do=upload&type=image&appidkey=' + accesstoken);
                var options = new FileUploadOptions();
                options.fileKey = "file";
                options.fileName = url.substr(url.lastIndexOf('/') + 1);
                options.mimeType = "image/jpeg";
                var ft = new FileTransfer();
                ft.upload(url, uri, function(result) {
                    console.log(result);
                    var resp = JSON.parse(result.response);
                    if (resp.status == '1') {
                        resolve(resp.data.attachment);
                    } else {
                        reject(resp.message);
                    }
                }, function(result) {
                    var resp = JSON.parse(result.response);
                    reject(resp.message);
                }, options);
            })
        }
    },
    forEach: function(collection, callback, scope) {
        if (Object.prototype.toString.call(collection) === '[object Object]') {
            for (var prop in collection) {
                if (Object.prototype.hasOwnProperty.call(collection, prop)) {
                    callback.call(scope, collection[prop], prop, collection);
                }
            }
        } else {
            for (var i = 0, len = collection.length; i < len; i++) {
                callback.call(scope, collection[i], i, collection);
            }
        }
    },
    getFlatternDistance: function(lat1, lng1, lat2, lng2) {
        if ((lat1 == lat2) && (lng1 == lng2)) {
            return 0;
        }

        function getRad(d) {
            return d * Math.PI / 180.0; //经纬度转换成三角函数中度分表形式。
        }
        var f = getRad((lat1 + lat2) / 2);
        var g = getRad((lat1 - lat2) / 2);
        var l = getRad((lng1 - lng2) / 2);
        var sg = Math.sin(g);
        var sl = Math.sin(l);
        var sf = Math.sin(f);
        var s, c, w, r, d, h1, h2;
        var a = 6378137.0;
        var fl = 1 / 298.257;
        sg = sg * sg;
        sl = sl * sl;
        sf = sf * sf;
        s = sg * (1 - sl) + (1 - sf) * sl;
        c = (1 - sg) * (1 - sl) + sf * sl;
        w = Math.atan(Math.sqrt(s / c));
        r = Math.sqrt(s * c) / w;
        d = 2 * w * a;
        h1 = (3 * r - 1) / 2 / c;
        h2 = (3 * r + 1) / 2 / s;
        return d * (1 + fl * (h1 * sf * (1 - sg) - h2 * (1 - sf) * sg));
    },
    locateCity: function(latitude, longitude, coordtype = 'bd09ll', successFunc, errorFunc) {
        var thisAppFunc = this;
        thisAppFunc.post('api.php?entry=app&c=normal&a=lbs&do=city', {
            lat: latitude,
            lng: longitude,
            coordtype: coordtype
        }, (rs) => {
            if (rs.status == 1) {
                if (is.function(successFunc) && !is.empty(rs.data)) {
                    successFunc(rs.data);
                }
            } else {
                if (is.function(errorFunc)) {
                    errorFunc();
                }
            }
        }, () => {
            Vue.prototype.$Toast('获取数据失败', 'bottom')
        });
    },
    count: function(o) {
        if (is.empty(o)) return '0';
        var t = typeof o;
        if (t == 'string') {
            return o.length;
        } else if (t == 'object') {
            var n = 0;
            for (var i in o) {
                n++;
            }
            return n;
        }
        return false;
    },
    hasClass: function(obj, cls) {
        return obj.className.match(new RegExp('(\\s|^)' + cls + '(\\s|$)'));
    },
    addClass: function(obj, cls) {
        if (!this.hasClass(obj, cls)) obj.className += " " + cls;
    },
    removeClass: function(obj, cls) {
        if (this.hasClass(obj, cls)) {
            var reg = new RegExp('(\\s|^)' + cls + '(\\s|$)');
            obj.className = obj.className.replace(reg, ' ');
        }
    },
    toggleClass: function(obj, cls) {
        if (this.hasClass(obj, cls)) {
            this.removeClass(obj, cls);
        } else {
            this.addClass(obj, cls);
        }
    },
    toggleClassTest: function(ele) {
        var obj = document.querySelector(ele);
        this.toggleClass(obj, "testClass");
    },
    offset: function(ele) {
        console.log(typeof(ele));
        if (typeof(ele) != 'undefined') {
            var el = ele;
            var box = el.getBoundingClientRect();
            var body = document.body;
            var clientTop = el.clientTop || body.clientTop || 0;
            var clientLeft = el.clientLeft || body.clientLeft || 0;
            var scrollTop = window.pageYOffset || el.scrollTop;
            var scrollLeft = window.pageXOffset || el.scrollLeft;
            return {
                top: box.top + scrollTop - clientTop,
                left: box.left + scrollLeft - clientLeft
            };
        } else {
            return null;
        }
    }
}
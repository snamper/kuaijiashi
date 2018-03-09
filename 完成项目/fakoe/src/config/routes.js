import App from '../app'
/**
 * auth true登录才能访问，false不需要登录，默认true
 */
export default [{
    path: '/',
    component: App,
    children: [{
        name: 'homeIndex',
        path: '/home/index',
        meta: {
            auth: false
        },
        component: require('../pages/home/index.vue')
    }, {
        path: '/home/city',
        meta: {
            auth: false
        },
        component: require('../pages/home/city.vue')
    }, {
        name: 'accountLogin',
        path: '/account/login',
        meta: {
            auth: false
        },
        component: require('../pages/account/login.vue')
    }, {
        name: 'accountRegister',
        path: '/account/register',
        meta: {
            auth: false
        },
        component: require('../pages/account/register.vue')
    }, {
        name: 'articleList',
        path: '/article/list',
        meta: {
            auth: false
        },
        component: require('../pages/article/list.vue')
    }, {
        name: 'articleDetail',
        path: '/article/detail/:id',
        meta: {
            auth: false
        },
        component: require('../pages/article/detail.vue')
    }, {
        name: 'coachCategory',
        path: '/coach/category',
        meta: {
            auth: false
        },
        component: require('../pages/coach/category.vue')
    }, {
        name: 'coachList',
        path: '/coach/list/:sex/:type/:city/:tag',
        meta: {
            auth: false
        },
        component: require('../pages/coach/list.vue')
    }, {
        name: 'coachDeatil',
        path: '/coach/detail/:id',
        meta: {
            auth: false
        },
        component: require('../pages/coach/detail.vue')
    }, {
        name: 'coachSearch',
        path: '/coach/search',
        meta: {
            auth: false
        },
        component: require('../pages/coach/search.vue')
    }, {
        name: 'coachRecruit',
        path: '/coach/recruit',
        meta: {
            auth: false
        },
        component: require('../pages/coach/recruit.vue')
    }, {
        name: 'coachReward',
        path: '/coach/reward/:id',
        meta: {
            auth: true
        },
        component: require('../pages/coach/reward.vue')
    }, {
        name: 'coachEvaluation',
        path: '/coach/evaluation/:id',
        meta: {
            auth: true
        },
        component: require('../pages/coach/evaluation.vue')
    }, {
        name: 'orderList',
        path: '/order/list',
        meta: {
            auth: true
        },
        component: require('../pages/order/list.vue')
    }, {
        name: 'orderDetail',
        path: '/order/detail/:id/:coupon_id',
        meta: {
            auth: true
        },
        component: require('../pages/order/detail.vue')
    }, {
        name: 'orderEvaluate',
        path: '/order/evaluate/:id',
        meta: {
            auth: true
        },
        component: require('../pages/order/evaluate.vue')
    }, {
        name: 'userIndex',
        path: '/user/index',
        meta: {
            auth: true
        },
        component: require('../pages/user/index.vue')
    }, {
        name: 'userProfile',
        path: '/user/profile',
        meta: {
            auth: true
        },
        component: require('../pages/user/profile.vue')
    }, {
        name: 'userVip',
        path: '/user/vip',
        meta: {
            auth: true
        },
        component: require('../pages/user/vip.vue')
    }, {
        name: 'userCoupon',
        path: '/user/coupon/:order_id',
        meta: {
            auth: true
        },
        component: require('../pages/user/coupon.vue')
    }, {
        name: 'userFollow',
        path: '/user/follow',
        meta: {
            auth: true
        },
        component: require('../pages/user/follow.vue')
    }, {
        name: 'userWallet',
        path: '/user/wallet',
        meta: {
            auth: true
        },
        component: require('../pages/user/wallet.vue')
    }, {
        name: 'biddingCreate',
        path: '/bidding/create/:cid',
        meta: {
            auth: true
        },
        component: require('../pages/bidding/create.vue')
    }, {
        name: 'biddingList',
        path: '/bidding/list',
        meta: {
            auth: true
        },
        component: require('../pages/bidding/list.vue')
    }, {
        name: 'biddingDetail',
        path: '/bidding/detail/:id',
        meta: {
            auth: true
        },
        component: require('../pages/bidding/detail.vue')
    }, ]
}]
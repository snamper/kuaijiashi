<template>
    <div>
        <v-content topHeight="0">
        </v-content>
    </div>
</template>
<script>
var homeIndexCss = require('!style-loader/useable!css-loader!../../assets/css/home/index.css');
let homeIndexVue;
import util from 'util'
import is from 'is'

export default {
    computed: {
        location() {
            return this.$store.state.app.location || {};
        },
    },
    data() {
        return {}
    },
    created: function() {
        homeIndexVue = this;
    },
    methods: {
        init() {

        }
    },
    mounted: function() {
        this.$nextTick(function() {
            homeIndexCss.use();
            homeIndexVue.init();
        });
    },
    destroyed: function() {
        homeIndexCss.unuse();
    }
}
</script>

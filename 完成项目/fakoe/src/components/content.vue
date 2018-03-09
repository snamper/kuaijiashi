<style lang="less" scoped>
.main-content {
    position: absolute;
    right: 0;
    left: 0;
    z-index: 100;
    -webkit-overflow-scrolling: touch;
    overflow-x: hidden;
    z-index: 1;
}
</style>
<template>
    <div class="main-content" ref="contentScroll" @scroll="isScroll($event)" :style="[{'top': topHeight + 'px'}, {'bottom': bottomHeight + 'px'}, {'overflow-y': overflowy}]">
        <slot></slot>
    </div>
</template>
<script>
import is from 'is';
export default {
    props: {
        topHeight: {
            default: 46
        },
        bottomHeight: {
            default: 50
        },
        overflowy: {
            default: 'scroll'
        }
    },
    data() {
        return {
            top: 0,
            startY: 0,
        }
    },
    methods: {
        isScroll(e) {
            if (!is.empty(this.$refs.contentScroll.clientHeight) && !is.empty(this.$el.scrollTop)) {
                this.$emit('scroll', [this.$refs.contentScroll.clientHeight, this.$el.scrollTop]);
            }
            return;
        }
    }
}
</script>

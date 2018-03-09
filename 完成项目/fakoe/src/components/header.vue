<style lang="less" scoped>
.header {
    position: absolute;
    right: 0;
    left: 0;
    z-index: 100;
    height: 45px;
    line-height: 45px;
    .item {
        width: 50px;
    }
    .title {
        overflow: hidden;
        padding: 0;
        margin: 0;
        text-align: center;
        font-size: 16px;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
}
</style>
<template>
    <header class="header" flex="box:justify" :style="[{'background': background}, {'border-bottom': borderBottom}]">
        <slot name="left">
            <div class="item" flex="main:center cross:center"></div>
        </slot>
        <slot name="center" v-if="!title">
            <div></div>
        </slot>
        <h2 class="title" v-if="title">{{ title }}</h2>
        <slot name="right">
            <div class="item" flex="main:center cross:center"></div>
        </slot>
    </header>
</template>
<script>
export default {
    props: {
        title: {
            type: String,
            default: ''
        },
        background: {
            type: String,
            default: '#fff'
        },
        borderBottom: {
            type: String,
            default: '1px solid #f5f5f5'
        }
    }
}
</script>

var path = require('path')
var utils = require('./utils')
var config = require('../config')
var vueLoaderConfig = require('./vue-loader.conf')

function resolve(dir) {
  return path.join(__dirname, '..', dir)
}
module.exports = {
  entry: {
    app: './src/main.js'
  },
  output: {
    path: config.build.assetsRoot,
    filename: '[name].js',
    publicPath: process.env.NODE_ENV === 'production' ? config.build.assetsPublicPath : config.dev.assetsPublicPath
  },
  resolve: {
    extensions: ['.js', '.vue', '.json'],
    alias: {
      'vue$': 'vue/dist/vue.esm.js',
      'base$': resolve('config/base.js'), //程序的一些基本配置
      'routes$': path.resolve(__dirname, '../src/config/routes.js'), //路由
      'util$': path.resolve(__dirname, '../src/util/index.js'), //常用工具方法
      'is-seeing$': path.resolve(__dirname, '../src/util/is-seeing.js'),
      'stores': path.resolve(__dirname, '../src/stores/'), //常用工具方法
      '@': resolve('src')
    }
  },
  module: {
    rules: [{
      test: /\.useable\.css$/,
      use: ['style-loader/useable', 'css-loader']
    }, {
      test: /\.vue$/,
      loader: 'vue-loader',
      options: vueLoaderConfig
    }, {
      test: /\.js$/,
      loader: 'babel-loader',
      include: [resolve('src'), resolve('test')]
    }, {
      test: /\.(png|jpe?g|gif|svg)(\?.*)?$/,
      loader: 'url-loader',
      options: {
        limit: 100,
        name: utils.assetsPath('images/[name].[hash:7].[ext]')
      }
    }, {
      test: /\.(woff2?|eot|ttf|otf)(\?.*)?$/,
      loaders: [{
        loader: path.resolve(__dirname, 'cssPathResolver')
      }, {
        loader: 'url-loader',
        query: {
          limit: 10000,
          name: utils.assetsPath('icons/[name].[hash:7].[ext]'),
        }
      }]
    }]
  }
}

// page/candindex/candindex.js
var app = getApp();
var picPath = app.globalData.picPath;
var service = require('../../utils/util.js')
var pathUrl = app.globalData.pathUrl;
var collectflag = false, dakaflag = false;
Page({
  data: {
    picPath: picPath,
  },
  onLoad: function (options) {
    // 页面初始化 options为页面跳转所带来的参数
  },
  onReady: function () {
    // 页面渲染完成
  },
  onShow: function () {
    // 页面显示
  },
  onHide: function () {
    // 页面隐藏
  },
  onUnload: function () {
    // 页面关闭
  },
  Editecand: function () {
    wx.navigateTo({
      url: '/page/editecand/editecand'
    })
  },
  Edcandimg: function () {
    wx.navigateTo({
      url: '/page/edcandimg/edcandimg'
    })
  }
})
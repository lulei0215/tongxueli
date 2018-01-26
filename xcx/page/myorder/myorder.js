// page/mycreate/mycreate.js
var app = getApp();
var picPath = app.globalData.picPath;
var service = require('../../utils/util.js')
var pathUrl = app.globalData.pathUrl;
Page({
  data: {
    picPath: picPath,
  },
  onLoad: function (options) {
    // 页面初始化 options为页面跳转所带来的参数
    this.getHttpInfo();
  },
  // 获取初始化信息
  getHttpInfo: function () {
    var that = this;
    var url = pathUrl + '/orders';
    service.http(url, {}, function (data) {
      var data = data.data;
      if (data.err_code == '000') {
        that.setData({
          info: data.data
        })
      } else {
        wx.showModal({
          title: '提示',
          showCancel: false,
          content: data.err_msg,
        })
      }
    }, function (data) {
      wx.showToast({ title: '接口请求失败' });
      console.log(data);
    })
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
  }
})
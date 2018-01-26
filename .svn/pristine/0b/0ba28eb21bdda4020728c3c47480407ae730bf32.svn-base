// page/address/address.js
var app = getApp();
var picPath = app.globalData.picPath;
var service = require('../../utils/util.js')
var pathUrl = app.globalData.pathUrl;
Page({
  data: {
    picPath: picPath,

    region: ['北京市', '北京市', '朝阳区'],
  },
  onLoad: function (options) {
    // 页面初始化 options为页面跳转所带来的参数
  },
  bindRegionChange: function (e) {
    console.log('picker发送选择改变，携带值为', e.detail.value)
    this.setData({
      region: e.detail.value
    })
  },
  // 提交
  formSubmit: function(e){
    var that = this;
    var po = e.detail.value;
    if (po.cont == ''){
      wx.showModal({
        title: '提示',
        showCancel: false,
        content: '请输入详细地址',
      })
      return;
    } else if (po.name == ''){
      wx.showModal({
        title: '提示',
        showCancel: false,
        content: '请输入名字',
      })
      return;
    } else if (po.phone == '') {
      wx.showModal({
        title: '提示',
        showCancel: false,
        content: '请输入手机号',
      })
      return;
    } else if (!(/^1[3|4|5|7|8][0-9]\d{8}$/.test(po.phone))) {
      wx.showModal({
        title: '提示',
        showCancel: false,
        content: '请输入正确的手机号',
      })
      return;
    }
    wx.setStorageSync('addressinfo', JSON.stringify(po));
    wx.navigateBack({
      delta: 1
    })
  },
  toPercenter: function () {
    wx.navigateBack({
      delta: 3
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
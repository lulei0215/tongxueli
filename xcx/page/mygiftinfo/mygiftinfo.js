// page/mygiftinfo/mygiftinfo.js
var app = getApp();
var picPath = app.globalData.picPath;
var service = require('../../utils/util.js')
var pathUrl = app.globalData.pathUrl;
var subflag = false;
Page({
  data: {
    picPath: picPath,
    num: 1,
    drowFlag: false
  },
  onLoad: function (options) {
    // 页面初始化 options为页面跳转所带来的参数
    var that = this;
    that.getHttpInfo(options.id);
  },
  // 获取初始化信息
  getHttpInfo: function (id) {
    var that = this;
    var url = pathUrl + '/clickdh';
    service.http(url, {id: id}, function (data) {
      var data = data.data;
      if (data.err_code == '000') {
        that.setData({
          info: data.data,
          id: id
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
  // 礼品数量变化
  inpClick: function(e){
    var that = this;
    var title = e.currentTarget.dataset.title;
    var num = that.data.num;
    if (title == 'jian'){
      num = num - 1 <= 0 ? 1 : num - 1;
    } else if (title == 'jia'){
      num = num*1 + 1;
    }else{
      num = e.detail.value <= 0 ? 1 : e.detail.value;
    }
    that.setData({
      num: num
    })
  },
  // 提交
  submit: function(){
    var that = this;
    if (subflag)return;
    var dataInfo = that.data;
    var po = {
      id: dataInfo.id,
      addressinfo: dataInfo.info.addressinfo,
      num: dataInfo.num
    }
    if (po.addressinfo == ''){
      wx.showModal({
        title: '提示',
        showCancel: false,
        content: '请填写地址',
      })
      return;
    }
    var url = pathUrl + '/order';
    subflag = true;
    service.http(url, po, function (data) {
      subflag = false;
      var data = data.data;
      if (data.err_code == '000') {
        that.setData({
          drowFlag:true
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
    var that = this,id;
    var addressinfo = wx.getStorageSync('addressinfo');
    if (addressinfo){
      addressinfo = JSON.parse(addressinfo); 
      wx.removeStorageSync('addressinfo');
      addressinfo.addre = addressinfo.address.join(" ") +' '+ addressinfo.cont;
      var info = that.data.info;
      info.addressinfo = addressinfo;
      that.setData({
        info: info
      })
    }
  },
  onHide: function () {
    // 页面隐藏
  },
  onUnload: function () {
    // 页面关闭
  },
  toPercenter: function(){
    wx.navigateBack({
      delta: 2
    })
  },
  Myorder: function () {
    wx.reLaunch({
      url: '/page/myorder/myorder'
    })
  },
  Index: function () {
    wx.reLaunch({
      url: '/page/index/index'
    })
  },
  toAddress: function () {
    wx.navigateTo({
      url: '/page/address/address'
    })
  }
})
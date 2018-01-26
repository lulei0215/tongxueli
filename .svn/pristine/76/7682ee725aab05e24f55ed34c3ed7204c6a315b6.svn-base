
//获取应用实例
var app = getApp();
// 请求服务post
function http(url, data, success, fail, complete, that) {
  //请求动画开始 
  wx.showLoading({
    mask: true,
    title: '加载中',
  });
  data.session = wx.getStorageSync('session');
  complete = complete == undefined ? function () { } : complete;
  wx.request({
    url: url,
    data: data,
    method: 'POST', // OPTIONS, GET, HEAD, POST, PUT, DELETE, TRACE, CONNECT
    // header: {}, // 设置请求的 header
    success: function (res) {
      wx.hideLoading();
      if(res.data.err_code == '001'){
        wx.removeStorageSync('session');
        app.getUserSession(function (res, session){
          http(url, data, success, fail, complete, that);
        })
      } else {
        success(res);
      }
    },
    fail: function (res) {
      wx.hideLoading();
      fail(res)
    },
    complete: function (res) {
      //请求动画结束
      if (that != undefined) {
        that.setData({ rescss: false })
      }
      complete(res)
    }
  })
}
// 请求服务post无动画
function http1(url, data, success, fail, complete, that) {
  //请求动画开始 
  // if (that != undefined) {
  //   that.setData({ rescss: true })
  // };
  data.session = wx.getStorageSync('session');
  complete = complete == undefined ? function () { } : complete;
  wx.request({
    url: url,
    data: data,
    method: 'POST', // OPTIONS, GET, HEAD, POST, PUT, DELETE, TRACE, CONNECT
    // header: {}, // 设置请求的 header
    success: function (res) {
      if (res.data.err_code == '001') {
        wx.removeStorageSync('session');
        app.getUserSession(function (res, session) {
          http(url, data, success, fail, complete, that);
        })
      } else {
        success(res);
      }
    },
    fail: function (res) {
      fail(res)
    },
    complete: function (res) {
      //请求动画结束
      if (that != undefined) {
        that.setData({ rescss: false })
      }
      complete(res)
    }
  })
}
// 请求服务get
function http_get(url, data, success, fail, complete, that) {
  //请求动画开始 
  // if (that != undefined) {
  //   that.setData({ rescss: true })
  // }
  complete = complete == undefined ? function () { } : complete;
  wx.request({
    url: url,
    data: data,
    method: 'GET', // OPTIONS, GET, HEAD, POST, PUT, DELETE, TRACE, CONNECT
    // header: {}, // 设置请求的 header
    success: function (res) {
      success(res)
    },
    fail: function (res) {
      fail(res)
    },
    complete: function (res) {
      //请求动画结束
      // if (that != undefined) {
      //   that.setData({ rescss: false })
      // }
      complete(res)
    }
  })
}
module.exports = {
  http: http,
  http1: http1,
  http_get: http_get
}

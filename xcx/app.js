// const openIdUrl = require('./config').openIdUrl

App({
  onLaunch: function () {
    console.log('App Launch');
  },
  onShow: function () {
    console.log('App Show')
    var that = this;
    wx.checkSession({
      success: function () {
        //ssion 未过期，并且在本生命周期一直有效
        console.log("已经登陆");
        that.init();
      },
      fail: function () {
        console.log("登录过期")
        //登录态过期
        wx.login({
          success: function (res) {
            console.log("重新登陆成功", res)
            that.init();
          }
        })
      }
    })
  },
  onHide: function () {
    console.log('App Hide')
  },
  globalData: {
    hasLogin: false,
    openid: null,
    userinfo: '',
    // pathUrl: 'https://tongxueli.ibaomi.cn/public/index.php/index/Wx',
    // picPath: 'https://tongxueli.ibaomi.cn/public/image'
    pathUrl: 'http://192.168.1.50/tongxueli/public/index.php/index/Wx',
    picPath: 'http://192.168.1.50/tongxueli/public/image'
  },
  init: function () {
    var that = this;
    that.getUserSession(function (res, session) {
      console.log(session);
      that.getuserinfo();
    })
  },
  getuserinfo: function(callback){
    var that = this;
    callback = callback || function(){};
    wx.getUserInfo({
      success: function (res) {
        that.globalData.userinfo = res.userInfo;
        var userInfo = res;
        var session = wx.getStorageSync('session')
        wx.request({
          method: 'POST',
          url: that.globalData.pathUrl + '/getuser',
          data: {
            userinfo: userInfo,
            session: session
          },
          success: function (res) {
            console.log('获取信息成功');
            console.log(res.data)
            if (res.data.err_code == '001') {
              wx.removeStorageSync('session');
              that.init();
            }else{
              callback();
            }
          },
          fail: function (res) {
            console.log('获取信息失败！')
          }
        })
      },
      fail: function (res) {
        console.log(res);
      }
    })
  },
  // 获取用户session
  getUserSession: function (callback) {
    var self = this;
    var session = wx.getStorageSync('session')
    if (session != '') {
      callback(null, session)
    } else {
      wx.login({
        success: function (data) {
          wx.request({
            method: 'POST',
            url: self.globalData.pathUrl + '/getopenid',
            data: {
              code: data.code
            },
            success: function (res) {
              wx.setStorageSync('session', res.data.data);
              callback(null, res.data.data);
            },
            fail: function (res) {
              console.log('拉取用户openid失败，将无法正常使用开放接口等服务', res)
              callback(res)
            }
          })
        },
        fail: function (err) {
          console.log('wx.login 接口调用失败，将无法正常使用开放接口等服务', err)
          callback(err)
        }
      })
    }
  },
  // 检测授权状态
  checkSettingStatu: function (authType, callback) {
    var that = this;
    authType = authType || 'scope.userInfo';
    callback = callback || function () { };
    wx.getSetting({
      success: (res) => {
        console.log(res)
        if (!res.authSetting[authType]) {
          wx.authorize({
            scope: authType,
            success() {
              console.log("用户已经授权");
              callback();
            },
            fail() {
              wx.showModal({
                title: '用户未授权',
                content: '如需正常使用该程序，请按确定并在授权管理中授权',
                showCancel: false,
                success: function (res) {
                  if (res.confirm) {
                    console.log('用户点击确定')
                    wx.openSetting({
                      success: function success(res) {
                        console.log('openSetting success', res.authSetting);
                      }
                    });
                  }
                }
              })
            }
          })
        } else {
          callback();
        }
      }
    })
  }
})

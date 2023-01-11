// app.js

App({
  onLaunch() {
    // 展示本地存储能力
    const logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs)

    // 登录
    wx.login({
      success: res => {
        // 发送 res.code 到后台换取 openId, sessionKey, unionId
        console.log(res);
        this.globalData.code = res.code
      }
    })
  },
  uploadimg(data) {
    var that = this
    wx.uploadFile({
      url: 'https://safe.61kids.com.cn/admin/api/app_upload',
      filePath: data.path[0],
      name: 'file',
      formData: null,
      success: (res) => {
      },
      fail: (res) => {
      },
      complete: (com) => {
      }
    })
  },
  globalData: {
    userInfo: null,
    code:'',
    baseImg:'https://safe.61kids.com.cn/',
  }
})

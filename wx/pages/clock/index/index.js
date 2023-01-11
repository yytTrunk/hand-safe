// pages/clock/index/index.js
Page({

  /**
   * 页面的初始数据
   */
  data: {

  },
  // 班前会打卡
  colck1: function () {
    if (wx.getStorageSync('is_grid') == 1) {
      wx.navigateTo({
        url: '../../clock/metting/metting',
      })
    } else {
      wx.showToast({
        title: '没有权限',
        icon: 'error',
        duration: 2000,
      })
    }
  },
  // 危大工程打卡
  colck2: function () {
    if (wx.getStorageSync('is_grid') == 1) {
      wx.navigateTo({
        url: '../../clock/select/select',
      });
    } else {
      wx.showToast({
        title: '没有权限',
        icon: 'error',
        duration: 2000,
      })
    }
  },
  // 打卡记录
  colck3: function () {
    wx.navigateTo({
      url: '../record/record',
    })
  },

// 打卡记录
  colck4: function () {
    wx.navigateTo({
      url: '../clas/clas',
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})
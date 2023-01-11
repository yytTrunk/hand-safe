// pages/mine/mine.js
import {
  request
} from '../request/index.js'
Page({

  /**
   * 页面的初始数据
   */
  data: {
    phone: '',
    name: '',
    job: '',
    num: '',
    gird: 0,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let grid = wx.getStorageSync('is_grid');
    this.setData({
      grid: grid
    })


  },
  /**
   * 跳转到积分详情页
   */
  skip: function () {
    wx.showToast({
      title: '暂未开放',
      icon: 'error',
      duration: 2000,
    })
    // return;
    wx.navigateTo({
      url: '../../pages/point/point',
    })
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
    request({
      url: 'defaultLogin',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id')
      }
    }).then(res => {
      this.setData({
        phone: res.data.data.user.phone,
        name: res.data.data.user.nickname,
        job: res.data.data.user.role_name,
        thumb: res.data.data.user.thumb,
        score: res.data.data.user.score,
      })
    })
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
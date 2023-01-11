// pages/point/point.js
import {request} from '../request/index.js'
Page({

  /**
   * 页面的初始数据
   */
  data: {

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that=this;
    request({
      url:'defaultLogin',
      method:'POST',
      data:{
        user_id:wx.getStorageSync('user_id')
      }
    }).then(res=>{
      that.setData({
        phone:res.data.data.user.phone,
        name:res.data.data.user.nickname,
        job:res.data.data.user.role_name,
        thumb:res.data.data.user.thumb,
        score:res.data.data.user.score,
        score_detail :res.data.data.score,
        user:res.data.data.user
      })
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
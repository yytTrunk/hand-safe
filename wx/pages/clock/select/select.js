// pages/clock/select/select.js
import {
  request
} from '../../request/index.js';
Page({

  /**
   * 页面的初始数据
   */
  data: {
      record_lat_list:[]
  },
  // 危大工程打卡
  colck: function (e) {
    let lng = e.currentTarget.dataset.lng;
    let lat = e.currentTarget.dataset.lat;
    let id = e.currentTarget.dataset.id;
    if (wx.getStorageSync('is_grid') == 1) {
      if(e.currentTarget.dataset.type == 1) {
        wx.redirectTo({
          url: '../../clock/clock?lat='+lat+'&lng='+lng+'&lat_id='+id,
        });
      }else{
        wx.redirectTo({
          url: '../../clock/whether/whether'
        });
      }

    } else {
      wx.showToast({
        title: '没有权限',
        icon: 'error',
        duration: 2000,
      })
    }
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    request({
      url: 'defaultLogin',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id')
      }
    }).then(res => {
      that.setData({
        record_lat_list: res.data.data.records,
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
// pages/work/work.js
import {
  request
} from '../request/index.js';
Page({

  /**
   * 页面的初始数据
   */
  data: {
    workList: [],
    satus: 1,
    grid: 0,
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

    wx.setNavigationBarRightButton({
      hide: true
    })

    // 获取当前身份
    let grid = wx.getStorageSync('is_grid');
    this.setData({
      grid: grid
    })
  },
  // 添加工单
  add: function () {
    if(this.data.grid != 1) {
      wx.showToast({
        title: '非安全/网格员无法提交隐患',
        icon: 'error',
        duration: 2000
      })
      return;
    }
    wx.navigateTo({
      url: '../../pages/work/addWork/addWork',
    })
  },
  // 跳转到详情页
  getDeatil: function (e) {
    let status = e.currentTarget.dataset.status;
    if(status == '待整改') {
      wx.navigateTo({
        url: './workPending/work?id=' + e.currentTarget.dataset.id,
      })
    }

    if(status == '待确认') {
      wx.navigateTo({
        url: './addWorkDetail/work?id=' + e.currentTarget.dataset.id,
      })
    }
    
    if(status == '待审核') {
      wx.navigateTo({
        url: './workPendingDetail/work?id=' + e.currentTarget.dataset.id,
      })
    }

    if(status == '已结束') {
      wx.navigateTo({
        url: './workDeatil/workDeatil?id=' + e.currentTarget.dataset.id,
      })
    }
      wx.navigateTo({
        url: './workDeatil/workDeatil?id=' + e.currentTarget.dataset.id,
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
    // 工单列表

    request({
      url: 'eventList',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id')
      }
    }).then(res => {
      this.setData({
        workList: res.data.data,
        satus: res.data.data.status
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
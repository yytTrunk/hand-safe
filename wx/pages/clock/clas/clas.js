// pages/clock/record/record .js
import {
  request
} from '../../request/index.js'
Page({

  /**
   * 页面的初始数据
   */
  data: {
      data :[]
  },
  accept:function(e){
    let that = this;
    let id = e.currentTarget.dataset.id;
    wx.showModal({
      title: '提示',
      content: '确定代班？',
      success (res) {
        if (res.confirm) {
          request({
            url: 'record/accept',
            method: 'POST',
            data: {
              user_id: wx.getStorageSync('user_id'),
              id:id,
              status:1,
            }
          }).then(res => {
            if (res.data.code == 200) {
              wx.showToast({
                title: '提交成功',
                icon: 'success',
                duration: 2000,
              })
              setTimeout(function () {
                wx.switchTab({
                  url: '../../clock/index/index',
                })
              }, 2000)
      
            } else {
              wx.showToast({
                title: res.data.message,
                icon: 'error',
                duration: 2000,
              })
            }
          })
        } else if (res.cancel) {

        }
      }
    })
  },
  reject:function(e) {
    let that = this;
    let id = e.currentTarget.dataset.id;
    wx.showModal({
      title: '提示',
      content: '确定拒绝代班？',
      success (res) {
        if (res.confirm) {
          request({
            url: 'record/reject',
            method: 'POST',
            data: {
              user_id: wx.getStorageSync('user_id'),
              id:id,
              status:2,
            }
          }).then(res => {
            if (res.data.code == 200) {
              wx.showToast({
                title: '提交成功',
                icon: 'success',
                duration: 2000,
              })
              setTimeout(function () {
                wx.switchTab({
                  url: '../../clock/index/index',
                })
              }, 2000)
      
            } else {
              wx.showToast({
                title: res.data.message,
                icon: 'error',
                duration: 2000,
              })
            }
          })
        } else if (res.cancel) {

        }
      }
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
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
    let that = this;
    request({
      url: 'record/clas',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id')
      }
    }).then(res => {
      if (res.data.code == 200) {
        that.setData({
          data: res.data.data
        })
        console.log(res.data.data)
      } else {
        wx.showModal({
          title: '提示',
          content: '暂无代班记录，是否返回？',
          success(res) {
            if (res.confirm) {
              wx.navigateBack();
            } else if (res.cancel) {}
          }
        })
      }
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
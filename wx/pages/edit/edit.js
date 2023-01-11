// pages/edit/edit.js
import {
  request
} from '../request/index.js';
Page({

  /**
   * 页面的初始数据
   */
  data: {
    img_list: '',
    images: '',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },
  /**
   * 添加图片
   */
  img_show: function () {
    let that = this;
    wx.chooseImage({
      sizeType: ['original', 'compressed'], //可以指定是原图还是压缩图
      sourceType: ['album', 'camera'], //指定来源是相机
      success: function (res) {
        var imgsrc = res.tempFilePaths;
        that.setData({
          img_list: imgsrc
        })
        var imageList = that.data.imageList;
        that.uploadimg({
          path: imgsrc, //这里是你最开始定义的图片数组
          method: 'POST'
        })
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
        console.log(res)
        that.setData({
          images: res.data
        })
      },
      fail: (res) => {},
      complete: (com) => {}
    })
  },
  // 提交
  submitEdit: function (res) {
    console.log(res);
    let that = this;
    request({
      url: 'modifyInfo',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id'),
        phone: res.detail.value.phone,
        nickname:res.detail.value.name,
        thumb:that.data.images,
      }
    }).then(res => {
      if (res.data.code == 200) {
        wx.switchTab({
          url: '../index/index',
        })
      } else {
        wx.showToast({
          title: res.data.message,
          icon: 'error',
          duration: 2000
        })
      }
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
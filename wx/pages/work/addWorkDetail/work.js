import {
  request
} from "../../request/index.js";
let app = getApp();
// pages/work/add_work/addWork.js
Page({
  /**
   * 页面的初始数据
   */
  data: {
    baseImg:getApp().globalData.baseImg,
    images: [],
    img_list: [], //图片列表
    xcimg: [], //现场图片
    zgimg:[],
  },
  // 图片预览
  preview(event) {
    let currentUrl = event.target.dataset.src
    let img_index = event.target.dataset.index
    console.log(event)
    wx.previewImage({
      current: currentUrl[img_index], // 当前显示图片的http链接
      urls: currentUrl // 需要预览的图片http链接列表
    })
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
          img_list: that.data.img_list.concat(imgsrc)
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
        that.setData({
          images: that.data.images.concat(res.data)
        })
      },
      fail: (res) => {},
      complete: (com) => {}
    })
  },
  /**
   * 情况说明字数限制
   */
  bindInput2: function (e) {
    if (e.detail.cursor <= 50) {
      this.setData({
        explain_num: e.detail.cursor
      })
    }
  },
  // 提交事件
  submitWork: function (res) {
    let that = this;
    let r = res;

    if (that.data.images.length == 0) {
      wx.showToast({
        title: '请上传整改图片',
        icon: 'error',
        duration: 2000
      })
      return;
    }

    if (!r.detail.value.msg) {
      wx.showToast({
        title: '请填写整改说明',
        icon: 'error',
        duration: 2000
      })
      return;
    }

    request({
      url: 'eventSubmit/',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id'),
        event_id: that.data.event_id,
        msg: res.detail.value.msg,
        image: that.data.images
      }
    }).then(res => {
      if (res.data.code == 200) {
        wx.switchTab({
          url: '../../work/work',
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
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    request({
      url: 'eventDetail',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id'),
        event_id: options.id
      }
    }).then(res => {
      if (res.data.code == 200) {
        let is_reject = false;
        if( !res.data.data.reject_reason ||res.data.data.reject_reason !=''){
            is_reject = true;
        }

        that.setData({
          user_id: wx.getStorageSync('user_id'),
          item_name: res.data.data.project.name,
          item_work: res.data.data.work.name,
          dwgc: res.data.data.unit_project,
          zynr: res.data.data.work_content,
          wtms: res.data.data.question,
          bcsm: res.data.data.describution,
          audit_msg: res.data.data.audit_msg,
          reject_reason:res.data.data.reject_reason,
          item_date: res.data.data.create_time,
          subject: res.data.data.subject,
          source: res.data.data.source,
          event_id: options.id,
          status: res.data.data.status,
          end_time: res.data.data.end_time,
          xcimg:res.data.data.images,
          zgimg:res.data.data.reform_images,
          is_reject:is_reject,
          msg:res.data.data.msg,
        })
      } else {
        wx.showToast({
          title: res.data.message,
          icon: "error",
          duration: 2000,
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

  },
})
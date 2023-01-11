// pages/work/workDeatil/workDeatil.js
import {
  request
} from '../../request/index.js'
Page({

  /**
   * 页面的初始数据
   */
  data: {
    baseImg:getApp().globalData.baseImg,
    item_name: '', //项目名称
    item_work: '', //工区
    dwgc: '', //单位工程
    zynr: '', //作业内容
    wtms: '', //问题描述
    bcms: '', //补充描述
    user: '', //安全科员
    item_date: '', //时间
    subject: '', //整改建议
    source: '', //判断依据
    event_id: 0, //事件id
    status: 0,
    end_time: '',
    xcimg: [], //现场图片
    zgimg: [], //整改图片
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
        that.setData({
          item_name: res.data.data.project.name,
          item_work: res.data.data.work.name,
          dwgc: res.data.data.unit_project,
          zynr: res.data.data.work_content,
          wtms: res.data.data.question,
          bcms: res.data.data.describution,
          user: res.data.data.safeRoleAndSmall,
          item_date: res.data.data.create_time,
          subject: res.data.data.subject == null ? '暂无整改建议' : res.data.data.subject,
          source: res.data.data.source,
          event_id: options.id,
          status: res.data.data.status,
          end_time: res.data.data.end_time,
          msg:res.data.data.msg == null ? '暂无整改说明' : res.data.data.msg,
          audit_msg:res.data.data.audit_msg == null ? '暂无审批意见' : res.data.data.audit_msg,
          xcimg:res.data.data.images,
          zgimg:res.data.data.reform_images,
          type:res.data.data.type,
          reject_reason:res.data.data.reject_reason,
        })
        console.log(res);
      } else {
        wx.showToast({
          title: res.data.message,
          icon: "error",
          duration: 2000,
        })
      }
    })
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
  // 下载
  Load: function () {
    if (wx.getStorageSync('is_grid') == 1) {
      wx.getSavedFileList({
        success: (result) => {
          console.log(result);
        },
      })
    } else {
      wx.showToast({
        title: '没有权限',
        icon: 'error',
        duration: 2000
      })
    }

  },
  // 撤回
  withdraw: function () {
    if (wx.getStorageSync('is_grid') == 1) {
      request({
        url: 'cancelEvent',
        method: 'POST',
        data: {
          user_id: wx.getStorageSync('user_id'),
          event_id: this.data.event_id,
        }
      }).then(res => {
        if (res.data.code == 200) {
          wx.showToast({
            title: '已撤回',
            icon: 'success',
            duration: 1000
          })
          setTimeout(function () {
            wx.switchTab({
              url: '../../work/work',
            })
          }, 1000)
        } else {
          wx.showToast({
            title: res.data.message,
            icon: 'error',
            duration: 2000
          })
        }
      })
    } else {
      wx.showToast({
        title: '没有权限',
        icon: 'error',
        duration: 2000
      })
    }

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
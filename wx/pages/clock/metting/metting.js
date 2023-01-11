// pages/clock/metting/metting.js
import {
  request
} from '../../request/index.js';
Page({

  /**
   * 页面的初始数据
   */
  data: {
    itemName: '',
    images: [],
    img_list: [],
    itemName: '', //项目
    work: [], //工区
    text_num: 0, //标题字数
    explain_num: 0, //说明字数
    itemId: 0,
    workId: 0,
  },
  /**
   * 添加图片
   */
  img_show: function () {
    let that = this;
    wx.chooseImage({
      sizeType: ['original', 'compressed'], //可以指定是原图还是压缩图
      sourceType: ['camera'], //指定来源是相机
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
   * 标题字数限制
   */
  bindInput: function (e) {
    if (e.detail.cursor <= 50) {
      this.setData({
        text_num: e.detail.cursor
      })
    }
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

  /**
   * 选择工区
   */
  bindWorkChange: function (e) {
    console.log('picker发送选择改变，携带值为', e.detail.value)
    let index_num = e.detail.value;
    let project_id = this.data.project;
    let index_ = this.data.index_
    this.setData({
      item: e.detail.value,
      workId: project_id[index_num].id
    })
  },

  // 提交
  submitClock: function (res) {
    let that = this;
    if(that.data.workId == 0 ) {
      wx.showLoading({
        title: '请选择工区',
        duration:1000,
      })
      return;
    }

    if(res.detail.value.describution == '') {
      wx.showLoading({
        title: '请填写情况说明',
        duration:1000,
      })
      return;
    }
    console.log(res)
    request({
      url: 'record',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id'),
        project_id: that.data.itemId,
        work_id: that.data.workId,
        describution: res.detail.value.describution,
        name: res.detail.value.title,
        type: 2,
        images: that.data.images
      }
    }).then(res => {
      console.log(res);
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
    let that = this;
    request({
      url: 'defaultLogin',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id')
      }
    }).then(res => {
      let arr = [];
      for (let i = 0; i < res.data.data.works.length; i++) {
        arr.push(res.data.data.works[i].name)
      }
      that.setData({
        itemId: res.data.data.user.project_id,
        work: arr,
        project: res.data.data.works,
        itemName: res.data.data.user.project_name
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
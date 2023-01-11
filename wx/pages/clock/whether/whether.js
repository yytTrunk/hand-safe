// pages/clock/clock.js
import {
  request
} from '../../request/index.js';
Page({

  /**
   * 页面的初始数据
   */
  data: {
    index_: 0,
    project: [],
    itemName: '', //项目
    work: [], //工区
    text_num: 0, //标题字数
    explain_num: 0, //说明字数
    address: '', //地址
    itemId: 0,
    workId: 0,
    date:'',
    type:[
      '特殊原因不工作',
      '请假/代班',
    ],
    project_safe_grid:[],
    is_select_work:false,
    type_item:0
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

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

  bindDateChange: function(e) {
    console.log('picker发送选择改变，携带值为', e.detail.value)
    this.setData({
      date: e.detail.value
    })
  },
    /**
   * 选择类别
   */
  bindTypeChange: function (e) {
    console.log('picker发送选择改变，携带值为', e.detail.value)
    let is_select_work = false;
    if(e.detail.value == 1)  {
      is_select_work = true;
    }else{
      is_select_work = false;
    }
    this.setData({
      type_item: e.detail.value,
      is_select_work:is_select_work
    })

  },
  /**
   * 选择代班人员
   */
  bindSafeChange: function (e) {
    console.log('picker发送选择改变，携带值为', e.detail.value)
    let project_safe_grid = this.data.project_safe_grid;
    this.setData({
      safe_item: e.detail.value,
    })

  },
  // 提交
  submitClock: function (res) {
    let that = this;
    let user;
    if (res.detail.value.describution == '') {
      wx.showLoading({
        title: '请填写情况说明',
        duration: 1000,
      })
      return;
    }
  
    if(that.data.type_item == 1) {
      if(!that.data.safe_item) {
        wx.showLoading({
          title: '请选择代班人员',
          duration: 1000,
        })
        return;
      }
      if(that.data.date == '') {
        wx.showLoading({
          title: '请选择代班日期',
          duration: 1000,
        })
        return;
      }

      for (let i = 0; i < that.data.project_user.length; i++) {
          let user_name = that.data.project_user[i].nickname+'('+that.data.project_user[i].phone+')';
          if(that.data.project_safe_grid[that.data.safe_item] === user_name) {
              user = that.data.project_user[i].id
          }
      }
    }

    request({
      url: 'record/whether',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id'),
        project_id:that.data.itemId,
        whether: that.data.type_item,
        whether_date : that.data.date,
        whether_user_id:user,
        whether_work_reason:res.detail.value.describution
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
      let project_safe_grid = [];
      for (let i = 0; i < res.data.data.project_safe_grid.length; i++) {
        project_safe_grid.push(res.data.data.project_safe_grid[i].nickname+'('+res.data.data.project_safe_grid[i].phone+')')
      }
      let date = new Date();
      let year = date.getFullYear();
      let month = date.getMonth()+1;  
      let day = date.getDate();
      let date_now = year+'-'+month+'-'+day;
      that.setData({
        itemId: res.data.data.user.project_id,
        work: arr,
        project: res.data.data.works,
        itemName: res.data.data.user.project_name,
        project_safe_grid:project_safe_grid,
        date_now:date_now,
        project_user:res.data.data.project_safe_grid
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
  onUnload: function () {},

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
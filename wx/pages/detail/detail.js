// pages/detail/detail.js
import {request} from '../request/index.js';
Page({

  /**
   * 页面的初始数据
   */
  data: {
    item_name:'',
    item_date:'',
    item_describution:'',
    item_user:[],
    item_work:[],
    baseurl:'https://safe.61kids.com.cn',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    console.log(options);
    request({
      url:'project',
      method:'POST',
      data:{
        user_id:wx.getStorageSync('user_id'),
        project_id:options.id
      }
    }).then(res=>{
      console.log(res);
      if(res.data.code == 200){
        that.setData({
          item_name:res.data.data.name,
          item_date:res.data.data.create_time,
          item_describution:res.data.data.describution,
          item_user:res.data.data.projectUser, 
          item_work:res.data.data.workOrder,

        })
      }else{
        wx.showToast({
          title: res.data.message,
          icon:'error',
          duration:2000,
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
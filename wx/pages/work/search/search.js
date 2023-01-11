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
    title: '',
    lists:[]
  },

  selected:function(e){
    console.log(e);
    let id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../addWork/addWork?id='+id,
    })
  },
  // 提交事件
  submitWork: function (res) {
    console.log(res.detail.value.title);
    let that = this;
    request({
      url: 'search',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id'),
        title:res.detail.value.title
      }
    }).then(res => {
      if (res.data.code == 200) {
        that.setData({
          lists:res.data.data
        })
      } else {
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
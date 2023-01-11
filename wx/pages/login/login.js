// pages/login/login.js
import {request} from '../../pages/request/index.js';
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
tel:'',
password:''
  },
    /**
   * 跳转到忘记密码页面
   */
  forget:function(){
wx.navigateTo({
  url: '../../pages/login/forget/forget',
})
  },
    /**
   * 登录
   */
  formSubmit:function(res){
    let that = this;
    
      let phone = res.detail.value.phone;
      let password = res.detail.value.password;
      // if(phone == '15690673460' && password == '123456'){
        request({
          url: 'login',
          data:{
            code:app.globalData.code,
            tel:phone,
            password:password,
          },
          method: 'POST',
        }).then(res=>{
          
          if(res.data.code == 200){
            wx.setStorageSync('tel', phone);
            wx.setStorageSync('password', password);
            wx.setStorageSync('user_id', res.data.data.user.id);
            wx.switchTab({
              url: '../index/index',
            })
          }else{
            wx.showToast({
              title: '账号或密码错误',
              icon: 'error',
              duration: 2000
            })
          }
        })
      // }else{
      //   wx.showToast({
      //     title: '账号或密码错误',
      //     icon: 'error',
      //     duration: 2000
      //   })
      // }
      
    
   
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
   let tel =  wx.getStorageSync('tel');
   let pass =  wx.getStorageSync('password');
   if(tel != '' && pass != ''){
    request({
      url: 'login',
      data:{
        code:app.globalData.code,
        tel:tel,
        password:pass,
      },
      method: 'POST',
    }).then(res=>{
      console.log(res)
      if(res.data.code == 200){
        wx.switchTab({
          url: '../index/index',
        })
      }
    })
   }
   this.setData({
     tel:tel,
     password:pass
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
// pages/login/forget/forget.js
import {request} from '../../request/index.js';
Page({

  /**
   * 页面的初始数据
   */
  data: {
    codeList:[], //验证码
    phone:0, //手机号码
    code_text:"验证码", //发送验证码按钮文字
    code_disable:false, //发送验证码按钮是否可点击
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },
  /**
   * 输入验证码
   */
  bindFocus:function(e){
    console.log(e.detail.value);
    let that = this;
    if(e.detail.value.length <= 4){
      that.setData({
        codeList:e.detail.value
      })
    }
  },
   /**
   * 获取手机号
   */
  getPhone:function(e){
    let phone = e.detail.value
    this.setData({
        phone:phone,
    })
  },
   /**
   * 获取验证码
   */
  getCode:function(){
    let that = this
  let reg_tel = /^(13[0-9]|14[01456879]|15[0-35-9]|16[2567]|17[0-8]|18[0-9]|19[0-35-9])\d{8}$/
  let new_phone = this.data.phone;
  if( reg_tel.test(new_phone)){
      request({
          url:'sms',
          data:{
              tel:that.data.phone
          },
          method:"POST",
  })
      .then(res=>{
          if(res.data.code == 200){
              wx.showToast({
                  title: '发送成功',
                  icon: 'success',
                  duration: 2000
                });
                let time = 60;
                let setinterval_ = setInterval(function(){
                  time -- ;
                  if(time < 0){
                      that.setData({
                          code_text:"验证码", //发送验证码按钮文字
                          code_disable:false, //发送验证码按钮是否可点击
                      })
                      clearInterval(setinterval_ ); //取消倒计时
                  }else{
                      that.setData({
                          code_text:time+"s", //发送验证码按钮文字
                          code_disable:true, //发送验证码按钮是否可点击
                      })
                  }
                },1000)
          }
         
      })
  }else{
      wx.showToast({
          title: '号码不正确',
          icon: 'error',
          duration: 2000
        })
  }
 
},
 /**
   * 修改密码
   */
  formSubmit:function(e){
    let that = this;
    console.log(e)
    if(e.detail.value.password == e.detail.value.confirmPassword  && e.detail.value.password!=''){
      request({
        url:'modify/password',
        data:{
          user_id:wx.getStorageSync('user_id'),
          password:e.detail.value.password,
          repassword:e.detail.value.confirmPassword,
          code:e.detail.value.code,
          tel:that.data.phone,
        },
        method:'POST',
      }).then(res=>{
        if(res.data.code == 200){
          wx.showToast({
            title: '修改成功',
            icon: 'success',
            duration: 1000
          })
          wx.setStorageSync('password',e.detail.value.password )
          setTimeout(function(){
            wx.navigateTo({
              url: '../../login/login',
            })
          },1000)
        }
      })
    }else{
      wx.showToast({
        title: '密码输入不一致',
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
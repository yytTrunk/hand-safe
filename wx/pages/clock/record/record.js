// pages/clock/record/record .js
import {
  request
} from '../../request/index.js'
Page({

  /**
   * 页面的初始数据
   */
  data: {
    latitude: "",
    longitude: "",
    scale: 14,
    markers: [],
    address: '',
    describution: '',
    date: '',
    items: '',
    works: '',
    recard_type: 1,
  },
  //获取当前位置经纬度，并把定位到相应的位置
  getCenterLocation: function () {
    //获取当前位置：经纬度
    this.mapCtx.getCenterLocation({
      success: res => {
        //获取成功后定位到相应位置
        console.log(res);
        //参数对象中设置经纬度，我这里设置为获取当前位置得到的经纬度值
        this.mapCtx.moveToLocation({
          longitude: res.longitude,
          latitude: res.latitude
        })
      }
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let QQMapWX = require('../../../utils/qqmap-wx-jssdk.min.js');
    var qqmapsdk = new QQMapWX({
      key: 'NZWBZ-QILWO-65QWU-SQS2P-JDTJZ-AXFGO'
    });

    wx.getLocation({
      type: 'wgs84',
      success: (res) => {

        qqmapsdk.reverseGeocoder({
          //位置坐标，默认获取当前位置，非必须参数
          //Object格式
          location: {
            latitude: res.latitude,
            longitude: res.longitude
          },

          //成功后的回调
          success: r => {
            console.log(r.result.address_component);
            let citty = r.result.address_component.city
            this.address = citty
            that.setData({
              latitude: res.latitude,
              longitude: res.longitude
            })
          }
        });

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
      url: 'record_today',
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
          content: '暂无打卡记录，是否返回？',
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
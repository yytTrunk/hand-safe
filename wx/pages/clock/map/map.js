// pages/clock/clock.js
let  qqmapsdk 

Page({
 
  /**
   * 页面的初始数据
   */
  data: {
  
    latitude: "",
    longitude: "",
    scale: 14,
    markers: [],

  },
 
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let QQMapWX = require('../../../utils/qqmap-wx-jssdk.min.js');
    var qqmapsdk = new QQMapWX({ key: 'NZWBZ-QILWO-65QWU-SQS2P-JDTJZ-AXFGO'  });

    wx.getLocation({
      type: 'wgs84',
      success :(res) => {
         console.log(res);
           qqmapsdk.reverseGeocoder({
             //位置坐标，默认获取当前位置，非必须参数
              //Object格式
                location: {
                  latitude: res.latitude,
                  longitude: res.longitude
                },
                
                //成功后的回调
                 success: r =>{
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
   //获取当前位置经纬度，并把定位到相应的位置
   getCenterLocation: function () {
    //获取当前位置：经纬度
    this.mapCtx.getCenterLocation({
      success:res=>{
        //获取成功后定位到相应位置
        console.log(res);
        //参数对象中设置经纬度，我这里设置为获取当前位置得到的经纬度值
        this.mapCtx.moveToLocation({
          longitude:res.longitude,
          latitude:res.latitude
        })
      }
    })
  },
  // 返回上一页
  submit:function(){
    wx.navigateBack({
      delta: 1,
    })
  },
})
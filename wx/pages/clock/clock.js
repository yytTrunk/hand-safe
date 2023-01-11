// pages/clock/clock.js
import {
  request
} from '../request/index.js';
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
    latitude: 0, //维度
    longitude: 0, //经度
    distance: 0 ,//距离
    type: ['日常打卡', '定位不准拍照打卡'],
    type_item: 0,
    img_list: [], //图片列表
    images: [],
    is_show:false,
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
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let lat = options.lat;
    let lng = options.lng;
    let lat_id = options.lat_id;
    let that = this;
    this.setData({
      lat_id:lat_id
    })
    let _this = this;
    let QQMapWX = require('../../utils/qqmap-wx-jssdk.min.js');
    var qqmapsdk = new QQMapWX({
      key: 'NZWBZ-QILWO-65QWU-SQS2P-JDTJZ-AXFGO'
    });
    qqmapsdk.reverseGeocoder({
      // location: , //默认当前位置
      success: function (res) { //成功后的回调
        console.log(res);
        var res = res.result;
        var mks = [];
        mks.push({ // 获取返回结果，放到mks数组中
          title: res.address,
          id: 0,
          latitude: res.location.lat,
          longitude: res.location.lng,
          iconPath: './resources/placeholder.png', //图标路径
          width: 20,
          height: 20,
          callout: { //在markers上展示地址名称，根据需求是否需要
            content: res.address,
            color: '#000',
            display: 'ALWAYS'
          }
        });
        _this.setData({ //设置markers属性和地图位置poi，将结果在地图展示
          markers: mks,
          poi: {
            latitude: res.location.lat,
            longitude: res.location.lng
          }
        });
        console.log(res.location.lat)
        console.log(res.location.lng)
        qqmapsdk.calculateDistance({
          mode: 'straight', //可选值：'driving'（驾车）、'walking'（步行），不填默认：'walking',可不填
          //获取表单提交的经纬度并设置from和to参数（示例为string格式）
          from: {
            latitude: res.location.lat,
            longitude: res.location.lng
          }, //若起点有数据则采用起点坐标，若为空默认当前地址
          to: [{
            location: {
              lat: lat,
              lng: lng
            }
          }], //终点坐标
          success: function (res1) { //成功后的回调
            console.log(res1);
            var res1 = res1.result;
            var dis = [];
            for (var i = 0; i < res1.elements.length; i++) {
              dis.push(res1.elements[i].distance); //将返回数据存入dis数组，
            }
            _this.setData({ //设置并更新distance数据
              distance: dis
            });
            console.log(dis);
          },
          fail: function (error) {
            console.error(error);
          },
          complete: function (res) {
            console.log(res);
          }
        })
      },
      fail: function (error) {
        console.error(error);
      },
      complete: function (res) {
        console.log(res);
      }
    });
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
   *上报类型
   */
  bindTypeChange: function (e) {
    let is_show= false;
    if(e.detail.value == 1) {
      is_show = true; 
    }
    this.setData({
      type_item: e.detail.value,
      is_show:is_show
    })
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
    if (res.detail.value.describution == '') {
      wx.showLoading({
        title: '请填写情况说明',
        duration: 1000,
      })
      return;
    }

    if(that.data.type_item == 1) {
      if (that.data.images.length == 0) {
        wx.showToast({
          title: '请上传现场图片',
          icon: 'error',
          duration: 2000
        })
        return;
      }
    }



    if(that.data.distance[0] > 100) {
      wx.showModal({
        title:'提示',
        content:'打卡超出范围是否继续提交?',
        success(r) {
          if(r.confirm) {
            request({
              url: 'record',
              method: 'POST',
              data: {
                user_id: wx.getStorageSync('user_id'),
                address: that.data.address,
                project_id: that.data.itemId,
                work_id: that.data.workId,
                describution: res.detail.value.describution,
                name: res.detail.value.title,
                type: 1,
                distance: that.data.distance[0],
                lat_id:that.data.lat_id,
                record_images: that.data.images,
              }
            }).then(res => {
              if (res.data.code == 200) {
                wx.showToast({
                  title: '打卡成功',
                  icon: 'success',
                  duration: 2000,
                })
                setTimeout(function () {
                  wx.switchTab({
                    url: '../clock/index/index',
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
          }else if(r.cancel){
          }
        }
      })
    }else{
      request({
        url: 'record',
        method: 'POST',
        data: {
          user_id: wx.getStorageSync('user_id'),
          address: that.data.address,
          project_id: that.data.itemId,
          work_id: that.data.workId,
          describution: res.detail.value.describution,
          name: res.detail.value.title,
          type: 1,
          distance: that.data.distance[0],
          lat_id:that.data.lat_id,
          record_images: that.data.images,
        }
      }).then(res => {
        if (res.data.code == 200) {
          wx.showToast({
            title: '打卡成功',
            icon: 'success',
            duration: 2000,
          })
          setTimeout(function () {
            wx.switchTab({
              url: '../clock/index/index',
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
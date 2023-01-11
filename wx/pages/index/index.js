// index.js
// 获取应用实例
const app = getApp();
import {
  request
} from '../request/index.js';
Page({
  data: {
    userName: '', //名称
    job: '', //职位
    phone: '', //电话
    pic: '', //头像
    project: [], //项目
  },

  // 跳转到详情页
  getDetail: function (e) {
    console.log(e);
    wx.navigateTo({
      url: '../../pages/detail/detail?id=' + e.currentTarget.dataset.id,
    })
  },
  onLoad() {
  },

  onShow:function(){
    let that = this;
    request({
      url: 'defaultLogin',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id')
      }
    }).then(res => {
      wx.setStorageSync('project', res.data.data)
      wx.setStorageSync('newEvent', res.data.data.is_new_event)
      wx.setStorageSync('is_grid', res.data.data.user.is_grid);
      wx.setStorageSync('latitude', res.data.data.user.card[1]);
      wx.setStorageSync('longitude', res.data.data.user.card[0]);

      that.setData({
        userName: res.data.data.user.nickname,
        job: res.data.data.user.role_name,
        phone: res.data.data.user.phone,
        pic: res.data.data.user.thumb,
        project: res.data.data.project,
      })


    })
  }

})
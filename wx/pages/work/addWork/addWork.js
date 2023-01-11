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
    question: [],
    img_list: [], //图片列表
    itemList: [], //项目列表
    itemId: 0, //项目id
    workId: 0, //工区id
    work: [], //工区列表
    text_num: 0, //标题字数
    explain_num: 0, //说明字数
    images: [],
    index_: 0,
    project_name: '',
    type: ['安全隐患排查', '未按方案施工'],
    is_submit: ['是', '否'],
    type_item: 0,
    submit_item: 0,
    dwgcList: [], //单位工程列表
    dwgcText: 0, //单位工程文案
    dwgcIndex: '', //单位工程下标
    zynrList: [], // 作业内容列表
    zynrText: '', //作业内容文案
    zynrIndex: 0, // 作业内容下标
    wtmsList: [], // 问题描述列表
    wtmsText: '', // 问题描述文案
    date: '',
    is_show: true,
    is_add: true,
    is_search: false,
    title: '',
    lists: []
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
   * 选择工区
   */
  bindWorkChange: function (e) {
    console.log('picker发送选择改变，携带值为', e.detail.value)
    let index_num = e.detail.value;
    let works = this.data.works;
    let index_ = this.data.index_
    this.setData({
      item: e.detail.value,
      workId: works[index_num].id
    })
  },

  /**
   *是否提交安全科在
   */
  bindSubmitChange: function (e) {
    this.setData({
      submit_item: e.detail.value,
    })
  },
  /**
   *上报类型
   */
  bindTypeChange: function (e) {
    let is_show = true;
    if (e.detail.value == 1) {
      is_show = false;
    }
    this.setData({
      type_item: e.detail.value,
      is_show: is_show
    })
  },
  /**
   * 选择单位工程
   */
  bindDwgc: function (e) {
    let that = this;
    let index_ = e.detail.value;
    let question1 = that.data.question;
    let zynr = [];
    for (let i = 0; i < question1[index_].dept.length; i++) {
      zynr.push(question1[index_].dept[i].name);
    }
    that.setData({
      item1: index_,
      dwgcIndex: index_,
      dwgcText: that.data.question[index_].name,
      zynrList: zynr,
    })
  },
  /**
   * 选择作业内容
   */
  bindZynr: function (e) {
    let that = this;
    let index_ = e.detail.value;
    let question1 = that.data.question;
    let dwgcIndex = that.data.dwgcIndex;
    let wtms = [];
    for (let i = 0; i < question1[dwgcIndex].dept[index_].product.length; i++) {
      wtms.push(question1[dwgcIndex].dept[index_].product[i].name);
    }
    that.setData({
      item2: index_,
      zynrText: that.data.question[dwgcIndex].dept[index_].name,
      wtmsList: wtms,
    })
  },
  /**
   * 选择问题描述
   */
  bindWtms: function (e) {
    let that = this;
    let index_ = e.detail.value;
    let dwgcIndex = that.data.dwgcIndex;
    let zynrIndex = that.data.zynrIndex;
    that.setData({
      item3: index_,
      wtmsText: that.data.wtmsList[index_],
    })
  },

  search: function () {
    this.setData({
      is_search: true,
      is_add: false
    })
    // wx.navigateTo({
    //   url: '../search/search',
    // })
  },
  selected: function (e) {
    console.log(e);
    let id = e.currentTarget.dataset.id;
    let that = this;
    request({
      url: 'search/detail',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id'),
        id: id
      }
    }).then(res => {
      if (res.data.code == 200) {
        that.setData({
          dwgc: res.data.data.dwgc,
          zynr: res.data.data.zync,
          wtms: res.data.data.wtms,
          zgjy: res.data.data.zgjy,
          is_add: true,
          is_search: false
        })
      }
    })
    // wx.navigateTo({
    //   url: '../addWork/addWork?id='+id,
    // })
  },
  // 提交事件
  submitSearch: function (res) {
    console.log(res.detail.value.title);
    let that = this;
    request({
      url: 'search',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id'),
        title: res.detail.value.title
      }
    }).then(res => {
      if (res.data.code == 200) {
        that.setData({
          lists: res.data.data
        })
      } else {}
    })

  },
  bindDateChange: function (e) {
    console.log('picker发送选择改变，携带值为', e.detail.value)
    this.setData({
      date: e.detail.value
    })
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
  // 提交事件
  submitWork: function (res) {
    console.log(res);
    let that = this;
    let r = res;
    if (that.data.wtmsText == "") {
      if (!res.detail.value.msbc) {
        wx.showToast({
          title: '请填写补充说明',
          icon: 'error',
          duration: 2000
        })
        return;
      }
    }

    if (that.data.images.length == 0) {
      wx.showToast({
        title: '请上传现场图片',
        icon: 'error',
        duration: 2000
      })
      return;
    }

    if (!that.data.date) {
      wx.showToast({
        title: '请选择整改时间',
        icon: 'error',
        duration: 1000
      })
      return;
    }
    request({
      url: 'event',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id'),
        project_id: that.data.item_id,
        work_id: that.data.workId,
        images: that.data.images,
        unit_project: r.detail.value.dwgc,
        work_content: r.detail.value.zynr,
        question: r.detail.value.wtms,
        describution: r.detail.value.msbc,
        type: that.data.type_item,
        is_submit_to_safe: that.data.submit_item,
        date: that.data.date,
        subject:r.detail.value.zgjy,
      }
    }).then(res => {
      if (res.data.code == 200) {
        wx.switchTab({
          url: '../../work/work',
        })
      } else {
        wx.showToast({
          title: res.data.message,
          icon: 'error',
          duration: 2000
        })
      }
    })


  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let id = 0;
    if (options.id) {
      id = options.id;
    }

    request({
      url: 'defaultLogin',
      method: 'POST',
      data: {
        user_id: wx.getStorageSync('user_id')
      }
    }).then(res => {
      // 工区
      let arr = [];
      for (let i = 0; i < res.data.data.works.length; i++) {
        arr.push(res.data.data.works[i].name)
      }
      // 单位工程
      let dwgc = [];
      for (let j = 0; j < res.data.data.question.length; j++) {
        dwgc.push(res.data.data.question[j].name)
      }
      that.setData({
        works: res.data.data.works,
        work: arr,
        item_id: res.data.data.user.project_id,
        project_name: res.data.data.user.project_name,
        dwgcList: dwgc, //单位工程列表
        question: res.data.data.question,
      })
      if (id != 0) {

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

  },
})
// component/tabbar/tabbar.js
Component({
  /**
   * 组件的属性列表
   */
  properties: {

  },

  /**
   * 组件的初始数据
   */
  data: {
    selected: 0,
    "color": "#ced8e1",
    "selectedColor": "#179ef4",
    "list": [
      {
        "pagePath": "pages/index/index",
        "text": "首页",
        "iconPath": "../../image/home_.png",
        "selectedIconPath": "../../image/home.png"
      },
      {
        "pagePath": "pages/clock/clock",
        "text": "打卡",
        "iconPath": "../../image/clock_.png",
        "selectedIconPath": "../../image/clock.png"
      },
      {
        "pagePath": "pages/work/work",
        "text": "工单",
        "iconPath": "../../image/events.png",
        "selectedIconPath": "../../image/event.png"
      },
      {
        "pagePath": "pages/mine/mine",
        "text": "我的",
        "iconPath": "../../image/mine_.png",
        "selectedIconPath": "../../image/mine.png"
      }
    ],
  },

  /**
   * 组件的方法列表
   */
  methods: {
    
    switchTab(e) {
      const data = e.currentTarget.dataset
      const url = data.path
      wx.switchTab({url})
      this.setData({
        selected: data.index
      })
    }
   
  },
  
})

// component/top.js
import {requset} from '../../pages/request/index.js'
Component({
  /**
   * 组件的属性列表
   */
  properties: {
    name:String,
    job:String,
    phone:String,
    pic:String,
  },

  /**
   * 组件的初始数据
   */
  data: {
    username:''
  },

  /**
   * 组件的方法列表
   */
  methods: {
    changeName() {
      this.triggerEvent('changeName', {
        name: this.properties.nickname,
        job:this.properties.job,
        phone:this.properties.phone,
        pic:this.properties.pic,
      })
    },
    // 跳转到编辑页面
    edit(){
      wx.navigateTo({
        url: '../../pages/edit/edit',
      })
    }
  },
  
})

//  地里信息位置获取工具类

/**
 * 获取用户当前所在的位置 【适应微信新版本获取地里位置信息，旧getLocation方法频繁调用有性能问题，并且30秒只能获得一次成功】 
 */
export const getLocation = () => {
  return new Promise((resolve, reject) => {
      let _locationChangeFn = (res) => {
          resolve(res) // 回传地里位置信息
          wx.offLocationChange(_locationChangeFn) // 关闭实时定位
          wx.stopLocationUpdate(_locationChangeFn); // 关闭监听 不关闭监听的话，有时获取位置时会非常慢
      }
      wx.startLocationUpdate({
          success: (res) => {
              wx.onLocationChange(_locationChangeFn)
          },
          fail: (err) => {
              // 重新获取位置权限
              wx.openSetting({
                  success(res) {
                      res.authSetting = {
                          "scope.userLocation": true
                      }
                  }
              })
              reject(err)
          }
      })
  })
}


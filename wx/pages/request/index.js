export const request=(params)=>{
  // 定义公共的url
  const baseUrl = "https://safe.61kids.com.cn/";
  // const baseUrl = "https://msafe.61kids.com.cn/";  //测试站
  return new Promise((resolve,reject)=>{
    wx.request({
      ...params,
      url: baseUrl+params.url,
      success:(result)=>{
        resolve(result);
      },
      fail:(err)=>{
        reject(err);
      }
    })
  })
}
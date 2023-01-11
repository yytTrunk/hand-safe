<?php
// +----------------------------------------------------------------------
// | vaeThink [ Programming makes me happy ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.vaeThink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 听雨 < 389625819@qq.com >
// +---------------------------------------------------------------------
namespace app\port\controller;
use app\common\model\Admin;
use app\common\model\AdminGroup;
use app\common\model\AdminGroupAccess;
use app\common\model\Code;
use app\port\model\Project;
use app\port\model\Role;
use app\port\model\User;
use think\Cache;
use think\Db;
use vae\controller\PortControllerBase;

class IndexController extends PortControllerBase
{


   public function index()
   {
       echo  $this->getdistance(118.04397596571181,24.611185438368057,118.050487,24.617169);
//    $this->set();
   }

   public function set() {
       $admin = Db::name('admin')->where(['is_grid' => 1])->select();
       foreach ($admin as $v) {
           $list = explode('/',$v['card']);
           $data = [
             'user_id' => $v['id'],
               'lng' => $list[0],
               'lat' => $list[1],
               'create_time' => time(),
               'update_time' => time()
           ];

           Db::name('admin_lat')->insert($data);
       }
   }

    /**
     * 打卡距离
     * @return \think\response\Json
     */
   public function distance()
   {
       $lng = $this->params()['lng']+0.01;
       $lat = $this->params()['lat']+0.01;
       $user_lng = $this->params()['user_lng'];
       $user_lat = $this->params()['user_lat'];
       $distance = $this->getdistance($lng,$lat,$user_lng,$user_lat);
       $data = [
         'lat' => $lat,
         'lng' => $lng,
         'user_id' => $this->params()['user_id'],
         'create_time' => time(),
         'update_time' => time()
       ];
       Db::name('admin_lat_log')->insert($data);
       return $this->jsonSuccess('打卡距离',$distance);
   }


    /**
     * 计算经纬度间直线距离
     * @param $lng1
     * @param $lat1
     * @param $lng2
     * @param $lat2
     * @return mixed|string
     */
    function getDistance($lng1, $lat1, $lng2, $lat2) {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        $s = explode('.',$s);
        return $s[0];
    }

    /**
     * 默认登录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function defaultLogin ()
    {
        $user = Admin::where(['id' => $this->params()['user_id']])->find();
        $user->thumb = str_replace('\\','/',$user->thumb);
        if (!$user || !isset($user->openId) || empty($user->openId)) {
            return $this->jsonFail('登录信息已过期，请重新登录');
        }
        $user->project_name = null;
        if ($user->grid_type) {
            $user->card =explode('/',$user->card);
            if ($user->grid_type == 1) {
                $user->role_name = '安全员';
            }else{
                $user->role_name = '网格员';
            }

            $user->project_name = Project::where(['id' => $user->project_id])->value('name');
        }else{
            $user->card = [0,0];
            $adminGroupAccess = AdminGroupAccess::where(['uid' => $user->id])->value('group_id');
            $user->role_name = AdminGroup::where(['id' => $adminGroupAccess])->value('title');
        }

        if ($user->project_id){
            $projects = [Project::where(['id' => $user->project_id])->find()];
            $works = Db::name('work_order')->where(['project_id' => $user->project_id,'status' => 1,'type' => $user->grid_type])->select();
        }else{
            $projects =  Project::all(['status' => 1]);
            $works = [];
        }

        //问题清单
        $question_cate = Cache::get('question_cate');
        if (!$question_cate) {
            $question_cate  = Db::name('question_cate')->where(['pid'=> 0])->field('id,title,pid')->select();
            foreach ($question_cate as &$item) {
                $item['child'] = Db::name('question_cate')->where(['pid' => $item['id']])->field('id,title,pid')->select();
                foreach ($item['child'] as &$v){
                    $v['question'] = Db::name('question')->where(['cate_id' => $v['id']])->field('id,describution')->select();
                    foreach ($v['question'] as &$vv){
                        $vv['name'] = $vv['describution'];
                        unset($vv['describution']);
                    }
                    $v['product'] = $v['question'];
                    $v['name'] = $v['title'];
                    unset($v['question']);
                    unset($v['pid']);
                    if (count($v['product']) == 0) {
                        unset($v);
                    }
                }
                $item['dept']  = $item['child'];
                unset($item['child']);
                $item['name'] = $item['title'];
                unset($item['pid']);
                unset($item['title']);

            }
            Cache::set('question_cate',$question_cate,3600*12);
        }
        $is_new_event = 1;
        //打卡点
        $records = Db::name('admin_lat')->where(['user_id' => $this->params()['user_id']])->select();
        $whetherRecords = Db::name('record_whether')->where(['status' =>1,'whether_user_id' => $this->params()['user_id'],'whether_date_time' => strtotime(date('Y-m-d'))])->select();
        foreach ($whetherRecords as $whetherRecord) {
            $whetherUserLats = Db::name('admin_lat')->where(['user_id' => $whetherRecord['user_id']])->select();
            $records = array_merge($records,$whetherUserLats);
        }

        //同一项目的网格员安全员
        $project_safe_grid = [];
        if ($user->is_grid == 1) {
            $project_safe_grid = Db::name('admin')->where(['is_grid' => 1,'project_id' => $user->project_id])->select();
        }

        //积分详情
        $score = [];
        $s_num = 0;
        $a_num = 0;
        if ($user->is_grid == 1) {
            $score = Db::name('admin_score')->where(['user_id' => $this->params()['user_id']])->order('create_time desc')->select();
            foreach ($score as &$v) {
                if ($v['type'] ==0) {
                    $s_num += $v['score'];
                }else{
                    $a_num += $v['score'];
                }
                $v['create_time'] = date('Y-m-d',$v['create_time']);
            }
        }

        return $this->jsonSuccess('OK',[
            'user' => $user,
            'project' => $projects,
            'works' => $works,
            'is_new_event' => $is_new_event,
            'question' => $question_cate,
            'records' => $records,
            'project_safe_grid' => $project_safe_grid,
            'score' => ['s_num' => $s_num,'a_num' => $a_num ,'score' => $score]
        ]);
    }

    /**
     * 登录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
   public function login ()
   {
       $user = Admin::where('phone','=',$this->params()['tel'])->where(['state' => 1])->find();
       if (!$user) {
           return $this->jsonFail('该用户不存在');
       }

       $password = vae_set_password($this->params()['password'],$user->salt);
       if (!$user || $password != $user->pwd) {
           return $this->jsonFail('账号密码错误');
       }

       if (!$user->openId){
           $openId = $this->getOpenid($this->params()['code']);
           if (!$openId) {
               return $this->jsonFail('获取openid失败');
           }

           $user->openId = $openId;
           if (!$user->save()) {
               return $this->jsonFail('保存信息失败');
           }

       }

       return $this->jsonSuccess('登录成功',['user' => $user]);
   }

    /**
     * 获取验证码
     * @return \think\response\Json
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
   public function sendSMS()
   {
       //TODO 随机生成一个6位数
       $authCodeMT = mt_rand(1000,9999);
       if (!$this->sms($this->params()['tel'],$authCodeMT)){
           return $this->jsonFail('验证码异常');
       }

       Cache::set($this->params()['tel'],$authCodeMT,60 * 10);
       return $this->jsonSuccess('OK',$authCodeMT);
   }

    /**
     * 修改密码
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function modifyPassword()
    {
        if (!Cache::get($this->params()['tel']) || Cache::get($this->params()['tel']) != $this->params()['code'] ) {
            return $this->jsonFail('验证码错误');
        }

        if (!$user = Admin::where(['phone' => $this->params()['tel']])->find()) {
            return  $this->jsonFail('该用户暂未注册，请联系系统管理员注册');
        }

        if ($this->params()['password'] !== $this->params()['repassword']) {
            return $this->jsonFail('两次密码不一样');
        }

        $password = vae_set_password($this->params()['password'],$user->salt);
        $user->pwd = $password;
        if (!$user->save()) {
            return $this->jsonFail('修改失败');
        }

        return $this->jsonSuccess('修改成功');
    }

    /**
     * 上传文件
     * @return \think\response\Json
     */
    public function upload() {

        $file = request()->file('file');
        // 上传到本地服务器
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                $fileName =  $info->getSaveName();
                return $this->jsonSuccess('OK',['file' => $fileName]);
            }else{
                return $this->jsonFail('ERR',$file->getError());
            }
        }

        return $this->jsonFail('未检测到上传文件');
    }

    public function search() {
        $title = $this->params()['title'];
        $data = Db::name('question')->where('describution','like',"%$title%")->select();
        return $this->jsonSuccess('OK',$data);
    }

    public function searchDetail() {
        $data = Db::name('question')->where(['id' => $this->params()['id']])->find();
        $zynr = Db::name('question_cate')->where(['id' => $data['cate_id']])->find();
        $dwgc = Db::name('question_cate')->where(['id'=>$zynr['pid']])->find();
        return $this->jsonSuccess('OK',[
            'dwgc' => $dwgc['title'],
            'zync' => $zynr['title'],
            'wtms' => $data['describution'],
            'zgjy' => $data['subject'],
        ]);
    }

    /**
     * 修改信息
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function modifyInfo() {
        if (!$this->params()['user_id']) {
            return $this->jsonFail('参数不全');
        }

        $user = Db::name('admin')->where('id',$this->params()['user_id'])->find();
        if (!$user){
            return $this->jsonFail('该用户不存在');
        }

        $nickname = empty($this->params()['nickname'])? $user['nickname'] : $this->params()['nickname'];
        $phone = empty($this->params()['phone']) ? $user['phone'] : $this->params()['phone'];
        $thumb = empty($this->params()['thumb']) ? $user['thumb'] :$this->params()['thumb'];
        $thumb = str_replace(array("\r\n", "\r", "\n"), "", $thumb);
        $data = [
            'thumb' => $thumb,
            'phone' => $phone,
            'nickname' => $nickname
        ];

        if ($data['thumb'] == $user['thumb'] && $data['phone'] == $user['phone'] && $data['nickname'] == $user['nickname']) {
            return $this->jsonFail('您提交信息与原信息一致，无需修改');
        }

        if (!Db::name('admin')->where(['id'=>$this->params()['user_id']])->update($data)) {
            return $this->jsonFail('修改失败');
        }

        return $this->jsonSuccess('修改成功');
    }


}

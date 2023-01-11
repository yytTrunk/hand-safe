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
namespace app\admin\controller;
use vae\controller\AdminCheckLogin;
use think\Db;
class MainController extends AdminCheckLogin
{
    public function index()
    {
        $adminMainHook = vae_set_hook_one('admin_main');
        if(!empty($adminMainHook)) {
        	return $adminMainHook;
        }

        $admin = vae_get_login_admin();
        $group_id = Db::name('admin_group_access')->where(['uid' => $admin['id']])->value('group_id');
        if ($group_id == 4 || $group_id == 5 || $group_id == 1) {
            $recordCount = Db::name('record')->where(['complete' => 2])->count();
            $troubleCount = Db::name('event')->where(['type' => 1,'state' => 1])->where('status','in',[1,2,5])->count();
            $questionCount = Db::name('event')->where(['type' => 2,'state' => 1])->count();
        }else{
            $recordCount = Db::name('record')->where(['complete' => 2,'project_id' => $admin['project_id']])->count();
            $troubleCount = Db::name('event')->where(['type' => 1,'project_id' => $admin['project_id'],'state' => 1])->where('status','in',[1,2,5])->count();
            $questionCount = Db::name('event')->where(['type' => 2,'project_id' => $admin['project_id'],'state' => 1])->count();
        }
        return $this->fetch('',[
            'recordCount' => $recordCount,
            'troubleCount' => $troubleCount,
            'questionCount' => $questionCount
        ]);
    }
}

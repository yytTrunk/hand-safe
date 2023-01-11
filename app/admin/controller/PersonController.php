<?php

namespace app\admin\controller;
use app\common\model\Admin;
use vae\controller\AdminCheckAuth;
use think\Db;
use think\Exception;

class PersonController extends AdminCheckAuth
{
    /**
     * 项目安全科长
     * @return \think\response\View
     */
    public function index()
    {
        return view('person/index/index');
    }
    public function getContentList()
    {
    	$param = vae_get_param();
        $where = array();
        if(!empty($param['keywords'])) {
            $where['a.id|a.username'] = ['like', '%' . $param['keywords'] . '%'];
        }

        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $content = \think\loader::model('Admin')
                ->field('*')
                ->alias('a')
                ->join('admin_group_access b','a.id=b.uid')
    			->order('a.create_time desc')
                ->where($where)
                ->where(['b.group_id' => 7])
                ->where(['status' => 1,'state' => 1])
    			->paginate($rows,false,['query'=>$param])
                ->each(function ($item,$key){
                    $item->last_login_time = date('Y-m-d H:i:s',$item->last_login_time);
                });

    	return vae_assign_table(0,'',$content);
    }
    public function add()
    {
    	return view('person/index/add');
    }
    public function addSubmit()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.add');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                $param['salt'] = vae_set_salt(20);
                $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                $param['create_time'] = $param['update_time'] = time();
                $param['last_login_time'] = time();
                // 启动事务
                Db::startTrans();
                try{
                    $uid = \think\loader::model('Admin')->strict(false)->field(true)->insertGetId($param);
                    $data[] = ['uid' => $uid ,'group_id' => 7];
                    \think\loader::model('AdminGroupAccess')->strict(false)->field(true)->insertAll($data);
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }
    public function edit()
    {
        return view('person/index/edit',['admin'=>vae_get_admin(vae_get_param('id'))]);
    }
    public function editSubmit()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.edit');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                if(!empty($param['pwd'])) {
                    //重置密码
                    if(empty($param['pwd_confirm']) or $param['pwd_confirm'] !== $param['pwd']) {
                        return vae_assign(0,'两次密码不一致');
                    }
                    $param['salt'] = vae_set_salt(20);
                    $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                    $param['update_time'] = time();
                } else {
                    unset($param['pwd']);
                    unset($param['salt']);
                }

                // 启动事务
                Db::startTrans();
                try{
                    \think\loader::model('Admin')->where(['id'=>$param['id']])->strict(false)->field(true)->update($param);
                    //清除菜单缓存
                    \think\Cache::clear('VAE_ADMIN_MENU');
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }
    public function delete()
    {
        $id    = vae_get_param("id");
        if ($id == 1) {
            return vae_assign(0,"系统拥有者，无法删除！");
        }
        if (Db::name('Admin')->where(['id' => $id])->update(['state' => 0])) {
            return vae_assign(1,"删除成功！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }

    /**
     * 总公司安全科员
     */
    public function safeSmall()
    {
        return view('person/safeSmall/index');
    }
    public function getSafeSmallList()
    {
        $param = vae_get_param();
        $where = array();
        if(!empty($param['keywords'])) {
            $where['a.id|a.username'] = ['like', '%' . $param['keywords'] . '%'];
        }

        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $content = \think\loader::model('Admin')
            ->field('*')
            ->alias('a')
            ->join('admin_group_access b','a.id=b.uid')
            ->order('a.create_time desc')
            ->where($where)
            ->where(['b.group_id' => 4])
            ->where(['status' => 1,'state' => 1])
            ->paginate($rows,false,['query'=>$param])
            ->each(function ($item,$key){
                $item->last_login_time = date('Y-m-d H:i:s',$item->last_login_time);
            });

        return vae_assign_table(0,'',$content);
    }
    public function addSafeSmall()
    {
        return view('person/safeSmall/add');
    }
    public function editSafeSmall()
    {
        return view('person/safeSmall/edit',['admin'=>vae_get_admin(vae_get_param('id'))]);
    }
    public function deleteSafeSmall()
    {
        $id    = vae_get_param("id");
        if ($id == 1) {
            return vae_assign(0,"系统拥有者，无法删除！");
        }
        if (Db::name('Admin')->where(['id' => $id])->update(['state' => 0])) {
            return vae_assign(1,"删除成功！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }
    public function addSubmitSafeSmall()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.add');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                $param['salt'] = vae_set_salt(20);
                $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                $param['create_time'] = $param['update_time'] = time();
                $param['last_login_time'] = time();
                // 启动事务
                Db::startTrans();
                try{
                    $uid = \think\loader::model('Admin')->strict(false)->field(true)->insertGetId($param);
                    $data[] = ['uid' => $uid ,'group_id' => 4];
                    \think\loader::model('AdminGroupAccess')->strict(false)->field(true)->insertAll($data);
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }
    public function editSubmitSafeSmall()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.edit');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                if(!empty($param['pwd'])) {
                    //重置密码
                    if(empty($param['pwd_confirm']) or $param['pwd_confirm'] !== $param['pwd']) {
                        return vae_assign(0,'两次密码不一致');
                    }
                    $param['salt'] = vae_set_salt(20);
                    $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                    $param['update_time'] = time();
                } else {
                    unset($param['pwd']);
                    unset($param['salt']);
                }

                // 启动事务
                Db::startTrans();
                try{
                    \think\loader::model('Admin')->where(['id'=>$param['id']])->strict(false)->field(true)->update($param);
                    //清除菜单缓存
                    \think\Cache::clear('VAE_ADMIN_MENU');
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }

    /**
     * 项目科员管理
     */
    public function projectSafeSmall()
    {
        return view('person/projectSafeSmall/index');
    }
    public function getProjectSafeSmallList()
    {
        $param = vae_get_param();
        $where = array();
        if(!empty($param['keywords'])) {
            $where['a.id|a.username'] = ['like', '%' . $param['keywords'] . '%'];
        }

        $admin = vae_get_login_admin();
        if (!empty($admin['project_id'])) {
            $where['project_id'] =  $admin['project_id'];
        }
        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $content = \think\loader::model('Admin')
            ->field('*')
            ->alias('a')
            ->join('admin_group_access b','a.id=b.uid')
            ->order('a.create_time desc')
            ->where($where)
            ->where(['b.group_id' => 6])
            ->where(['status' => 1,'state' => 1])
            ->paginate($rows,false,['query'=>$param])
            ->each(function ($item,$key){
                $item->last_login_time = date('Y-m-d H:i:s',$item->last_login_time);
            });

        return vae_assign_table(0,'',$content);
    }
    public function addProjectSafeSmall()
    {
        return view('person/projectSafeSmall/add');
    }
    public function editProjectSafeSmall()
    {
        return view('person/projectSafeSmall/edit',['admin'=>vae_get_admin(vae_get_param('id'))]);
    }
    public function deleteProjectSafeSmall()
    {
        $id    = vae_get_param("id");
        if ($id == 1) {
            return vae_assign(0,"系统拥有者，无法删除！");
        }
        $admin = Db::name('admin')->where(['id' => $id])->find();
        $admin['admin_id'] = $admin['id'];
        unset($admin['id']);
        //备份数据
        Db::name('admin_copy')->insertGetId($admin);

        if (Db::name('Admin')->where(['id' => $id])->delete()) {
            return vae_assign(1,"删除成功！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }
    public function addSubmitProjectSafeSmall()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.add');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                $param['salt'] = vae_set_salt(20);
                $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                $param['create_time'] = $param['update_time'] = time();
                $admin = vae_get_login_admin();
                $param['project_id'] = $admin['project_id'];
                $param['last_login_time'] = time();
                // 启动事务
                Db::startTrans();
                try{
                    $uid = \think\loader::model('Admin')->strict(false)->field(true)->insertGetId($param);
                    $data[] = ['uid' => $uid ,'group_id' => 6];
                    \think\loader::model('AdminGroupAccess')->strict(false)->field(true)->insertAll($data);
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }
    public function editSubmitProjectSafeSmall()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.edit');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                if(!empty($param['pwd'])) {
                    //重置密码
                    if(empty($param['pwd_confirm']) or $param['pwd_confirm'] !== $param['pwd']) {
                        return vae_assign(0,'两次密码不一致');
                    }
                    $param['salt'] = vae_set_salt(20);
                    $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                    $param['update_time'] = time();
                } else {
                    unset($param['pwd']);
                    unset($param['salt']);
                }

                // 启动事务
                Db::startTrans();
                try{
                    \think\loader::model('Admin')->where(['id'=>$param['id']])->strict(false)->field(true)->update($param);
                    //清除菜单缓存
                    \think\Cache::clear('VAE_ADMIN_MENU');
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }

    /**
     * 安全员/网格员
     */

    public function safeAndGrid()
    {
        return view('person/safeAndGrid/index');
    }
    public function getSafeAndGridList()
    {
        $param = vae_get_param();
        $where = array();
        if(!empty($param['keywords'])) {
            $where['a.id|a.username'] = ['like', '%' . $param['keywords'] . '%'];
        }

        $admin = vae_get_login_admin();
        if (!empty($admin['project_id'])) {
            $where['project_id'] =  $admin['project_id'];
        }

        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $content = \think\loader::model('Admin')
            ->field('*')
            ->alias('a')
            ->join('admin_group_access b','a.id=b.uid')
            ->order('a.create_time desc')
            ->where($where)
            ->where(['b.group_id' => 10])
            ->where(['state' => 1])
            ->paginate($rows,false,['query'=>$param])
            ->each(function ($item,$key){
                $item->last_login_time = date('Y-m-d H:i:s',$item->last_login_time);
                $item->project = Db::name('project')->where(['id' => $item->project_id])->value('name');
                if ($item->work_status == 1) {
                    $item->work_status = '上岗';
                }else if ($item->work_status == 2){
                    $item->work_status = '预备';
                }
            });

        return vae_assign_table(0,'',$content);
    }
    public function addSafeAndGrid()
    {
        return view('person/safeAndGrid/add');
    }
    public function editSafeAndGrid()
    {
        $admin = vae_get_admin(vae_get_param('id'));
//        $admin['card'] = implode('===',json_decode($admin['card']));
        return view('person/safeAndGrid/edit',['admin'=>$admin]);
    }
    public function deleteSafeAndGrid()
    {
        $id    = vae_get_param("id");
        if ($id == 1) {
            return vae_assign(0,"系统拥有者，无法删除！");
        }
        $admin = Db::name('admin')->where(['id' => $id])->find();
        $admin['admin_id'] = $admin['id'];
        unset($admin['id']);
        //备份数据
        Db::name('admin_copy')->insertGetId($admin);
        if (Db::name('Admin')->where(['id' => $id])->delete()) {
            return vae_assign(1,"删除成功！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }
    public function addSubmitSafeAndGrid()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.add');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                $param['salt'] = vae_set_salt(20);
                $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                $param['create_time'] = $param['update_time'] = time();
                $admin = vae_get_login_admin();
                $param['project_id'] = $admin['project_id'];
                //安全员、网格员禁止登录
                $param['status'] = -1;
                $works = [];
                if (isset($param['group_id'])) {
                    $works = $param['group_id'];
                    unset($param['group_id']);
                }
                $param['is_grid'] = 1;
                // 启动事务
                Db::startTrans();
                try{
                    $uid = \think\loader::model('Admin')->strict(false)->field(true)->insertGetId($param);
                    $data[] = ['uid' => $uid ,'group_id' => 10];
                    \think\loader::model('AdminGroupAccess')->strict(false)->field(true)->insertAll($data);
                    $work_data = [];
                    if ($works) {
                        foreach ($works as $work) {
                            $work_data[] = ['admin_id' => $uid,'work_id' => $work,'update_time' => time(),'create_time' => time(),'project_id' => $admin['project_id']];
                        }
                        \think\loader::model('AdminWork')->strict(false)->field(true)->insertAll($work_data);
                    }

                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }
    public function editSubmitSafeAndGrid()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.edit');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                if(!empty($param['pwd'])) {
                    //重置密码
                    if(empty($param['pwd_confirm']) or $param['pwd_confirm'] !== $param['pwd']) {
                        return vae_assign(0,'两次密码不一致');
                    }
                    $param['salt'] = vae_set_salt(20);
                    $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                    $param['update_time'] = time();
                } else {
                    unset($param['pwd']);
                    unset($param['salt']);
                }

                $admin = vae_get_admin($param['id']);
                $param['status'] = -1;
                $works = [];
                if (isset($param['group_id'])) {
                    $works = $param['group_id'];
                    unset($param['group_id']);
                }
                // 启动事务
                Db::startTrans();
                try{
                    \think\loader::model('Admin')->where(['id'=>$param['id']])->strict(false)->field(true)->update($param);
                    Db::name('AdminWork')->where(['admin_id'=>$param['id']])->delete();

                    $work_data = [];
                    if ($works) {
                        foreach ($works as $work) {
                            $work_data[] = ['admin_id' => $param['id'],'work_id' => $work,'update_time' => time(),'create_time' => time(),'project_id' => $admin['project_id']];
                        }

                        \think\loader::model('AdminWork')->strict(false)->field(true)->insertAll($work_data);
                    }

                    //清除菜单缓存
                    \think\Cache::clear('VAE_ADMIN_MENU');
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }
    public function workStatusSafeAndGrid()
    {
        $id    = vae_get_param("id");
        $admin = Db::name('Admin')->where(['id' => $id])->find();
        $data = [];
        if ($admin['work_status'] == 1) {
            $data['work_status'] = 2;
        }elseif ($admin['work_status'] == 2) {
            $data['work_status'] = 1;
        }

        if (Db::name('Admin')->where(['id' => $id])->update($data)) {
            return vae_assign(1,"切换成功！");
        } else {
            return vae_assign(0,"切换失败！");
        }
    }
    //打卡点管理
    public function safeAndGridRecord()
    {
        $id = vae_get_param('id');
        return view('person/safeAndGrid/record',['id' => $id]);
    }
    public function getSafeAndGridRecordList()
    {
        $param = vae_get_param();
        $where = array();
        if(!empty($param['keywords'])) {
            $where['a.id|a.name'] = ['like', '%' . $param['keywords'] . '%'];
        }

        $admin = vae_get_admin($param['id']);
        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $content = \think\loader::model('AdminLat')
            ->field('*')
            ->alias('a')
            ->order('a.create_time desc')
            ->where($where)
            ->where(['user_id' => $param['id']])
            ->paginate($rows,false,['query'=>$param])
            ->each(function ($item,$key) use ($admin){
                $item->username =  $admin['nickname'];
            });

        return vae_assign_table(0,'',$content);
    }
    public function addSafeAndGridRecord()
    {
        return view('person/safeAndGrid/addRecord',[
            'id' => vae_get_param('id')
        ]);
    }
    public function addSubmitSafeAndGridRecord()
    {
        if($this->request->isPost()) {
            $param = vae_get_param();
            $uid = \think\loader::model('Admin_Lat')->strict(false)->field(true)->insertGetId($param);
            if ($uid) {
                return vae_assign(1,'添加成功');
            }

            return vae_assign(0,'添加失败');
        }
    }
    public function deleteSafeAndGridRecord()
    {
        $id    = vae_get_param("id");
        if (Db::name('admin_lat')->where(['id' => $id])->delete()) {
            return vae_assign(1,"删除成功！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }
    //积分记录
    public function safeAndGridScore()
    {
        $id = vae_get_param('id');
        return view('person/safeAndGrid/score',['id' => $id]);
    }
    public function getSafeAndGridScoreList()
    {
        $param = vae_get_param();
        $where = array();
        if(!empty($param['keywords'])) {
            $where['a.id|a.name'] = ['like', '%' . $param['keywords'] . '%'];
        }

        $admin = vae_get_admin($param['id']);
        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $content = \think\loader::model('AdminScore')
            ->field('*')
            ->alias('a')
            ->order('a.create_time desc')
            ->where($where)
            ->where(['user_id' => $param['id']])
            ->paginate($rows,false,['query'=>$param])
            ->each(function ($item,$key) use ($admin){
                $item->username =  $admin['nickname'];
                if ($item->type == 0) {
                    $item->type = '扣分';
                }else{
                    $item->type = '加分';
                }
            });

        return vae_assign_table(0,'',$content);
    }


    /**
     * 质检科长
     */

    public function quality()
    {
        return view('person/quality/index');
    }
    public function getQualityList()
    {
        $param = vae_get_param();
        $where = array();
        if(!empty($param['keywords'])) {
            $where['a.id|a.username'] = ['like', '%' . $param['keywords'] . '%'];
        }

        $admin = vae_get_login_admin();
        if (!empty($admin['project_id'])) {
            $where['project_id'] =  $admin['project_id'];
        }
        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $content = \think\loader::model('Admin')
            ->field('*')
            ->alias('a')
            ->join('admin_group_access b','a.id=b.uid')
            ->order('a.create_time desc')
            ->where($where)
            ->where(['b.group_id' => 11])
            ->where(['status' => 1,'state' => 1])
            ->paginate($rows,false,['query'=>$param])
            ->each(function ($item,$key){
                $item->last_login_time = date('Y-m-d H:i:s',$item->last_login_time);
            });

        return vae_assign_table(0,'',$content);
    }
    public function addQuality()
    {
        return view('person/quality/add');
    }
    public function editQuality()
    {
        return view('person/quality/edit',['admin'=>vae_get_admin(vae_get_param('id'))]);
    }
    public function deleteQuality()
    {
        $id    = vae_get_param("id");
        if ($id == 1) {
            return vae_assign(0,"系统拥有者，无法删除！");
        }
        if (Db::name('Admin')->where(['id' => $id])->update(['state' => 0])) {
            return vae_assign(1,"删除成功！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }
    public function addSubmitQuality()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.add');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                $param['salt'] = vae_set_salt(20);
                $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                $param['create_time'] = $param['update_time'] = time();
                $admin = vae_get_login_admin();
                $param['project_id'] = $admin['project_id'];
                $param['last_login_time'] = time();
                // 启动事务
                Db::startTrans();
                try{
                    $uid = \think\loader::model('Admin')->strict(false)->field(true)->insertGetId($param);
                    $data[] = ['uid' => $uid ,'group_id' => 11];
                    \think\loader::model('AdminGroupAccess')->strict(false)->field(true)->insertAll($data);
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }
    public function editSubmitQuality()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.edit');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                if(!empty($param['pwd'])) {
                    //重置密码
                    if(empty($param['pwd_confirm']) or $param['pwd_confirm'] !== $param['pwd']) {
                        return vae_assign(0,'两次密码不一致');
                    }
                    $param['salt'] = vae_set_salt(20);
                    $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                    $param['update_time'] = time();
                } else {
                    unset($param['pwd']);
                    unset($param['salt']);
                }

                // 启动事务
                Db::startTrans();
                try{
                    \think\loader::model('Admin')->where(['id'=>$param['id']])->strict(false)->field(true)->update($param);
                    //清除菜单缓存
                    \think\Cache::clear('VAE_ADMIN_MENU');
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }

    /**
     * 工程科
     * Engineering
     */

    public function engineering()
    {
        return view('person/engineering/index');
    }
    public function getEngineeringList()
    {
        $param = vae_get_param();
        $where = array();
        if(!empty($param['keywords'])) {
            $where['a.id|a.username'] = ['like', '%' . $param['keywords'] . '%'];
        }

        $admin = vae_get_login_admin();
        if (!empty($admin['project_id'])) {
            $where['project_id'] =  $admin['project_id'];
        }
        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $content = \think\loader::model('Admin')
            ->field('*')
            ->alias('a')
            ->join('admin_group_access b','a.id=b.uid')
            ->order('a.create_time desc')
            ->where($where)
            ->where(['b.group_id' => 12])
            ->where(['status' => 1,'state' => 1])
            ->paginate($rows,false,['query'=>$param])
            ->each(function ($item,$key){
                $item->last_login_time = date('Y-m-d H:i:s',$item->last_login_time);
            });

        return vae_assign_table(0,'',$content);
    }
    public function addEngineering()
    {
        return view('person/engineering/add');
    }
    public function editEngineering()
    {
        return view('person/engineering/edit',['admin'=>vae_get_admin(vae_get_param('id'))]);
    }
    public function deleteEngineering()
    {
        $id    = vae_get_param("id");
        if ($id == 1) {
            return vae_assign(0,"系统拥有者，无法删除！");
        }
        if (Db::name('Admin')->where(['id' => $id])->update(['state' => 0])) {
            return vae_assign(1,"删除成功！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }
    public function addSubmitEngineering()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.add');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                $param['salt'] = vae_set_salt(20);
                $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                $param['create_time'] = $param['update_time'] = time();
                $admin = vae_get_login_admin();
                $param['project_id'] = $admin['project_id'];
                $param['last_login_time'] = time();
                // 启动事务
                Db::startTrans();
                try{
                    $uid = \think\loader::model('Admin')->strict(false)->field(true)->insertGetId($param);
                    $data[] = ['uid' => $uid ,'group_id' => 12];
                    \think\loader::model('AdminGroupAccess')->strict(false)->field(true)->insertAll($data);
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }
    public function editSubmitEngineering()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.edit');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                if(!empty($param['pwd'])) {
                    //重置密码
                    if(empty($param['pwd_confirm']) or $param['pwd_confirm'] !== $param['pwd']) {
                        return vae_assign(0,'两次密码不一致');
                    }
                    $param['salt'] = vae_set_salt(20);
                    $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                    $param['update_time'] = time();
                } else {
                    unset($param['pwd']);
                    unset($param['salt']);
                }

                // 启动事务
                Db::startTrans();
                try{
                    \think\loader::model('Admin')->where(['id'=>$param['id']])->strict(false)->field(true)->update($param);
                    //清除菜单缓存
                    \think\Cache::clear('VAE_ADMIN_MENU');
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }

    /**
     * 总工程师
     * HeadEngineer
     */
    public function headEngineer()
    {
        return view('person/headEngineer/index');
    }
    public function getHeadEngineerList()
    {
        $param = vae_get_param();
        $where = array();
        if(!empty($param['keywords'])) {
            $where['a.id|a.username'] = ['like', '%' . $param['keywords'] . '%'];
        }

        $admin = vae_get_login_admin();
        if (!empty($admin['project_id'])) {
            $where['project_id'] =  $admin['project_id'];
        }
        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $content = \think\loader::model('Admin')
            ->field('*')
            ->alias('a')
            ->join('admin_group_access b','a.id=b.uid')
            ->order('a.create_time desc')
            ->where($where)
            ->where(['b.group_id' => 9])
            ->where(['status' => 1,'state' => 1])
            ->paginate($rows,false,['query'=>$param])
            ->each(function ($item,$key){
                $item->last_login_time = date('Y-m-d H:i:s',$item->last_login_time);
            });

        return vae_assign_table(0,'',$content);
    }
    public function addHeadEngineer()
    {
        return view('person/headEngineer/add');
    }
    public function editHeadEngineer()
    {
        return view('person/headEngineer/edit',['admin'=>vae_get_admin(vae_get_param('id'))]);
    }
    public function deleteHeadEngineer()
    {
        $id    = vae_get_param("id");
        if ($id == 1) {
            return vae_assign(0,"系统拥有者，无法删除！");
        }
        if (Db::name('Admin')->where(['id' => $id])->update(['state' => 0])) {
            return vae_assign(1,"删除成功！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }
    public function addSubmitHeadEngineer()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.add');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                $param['salt'] = vae_set_salt(20);
                $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                $param['create_time'] = $param['update_time'] = time();
                $admin = vae_get_login_admin();
                $param['project_id'] = $admin['project_id'];
                $param['last_login_time'] = time();
                // 启动事务
                Db::startTrans();
                try{
                    $uid = \think\loader::model('Admin')->strict(false)->field(true)->insertGetId($param);
                    $data[] = ['uid' => $uid ,'group_id' => 9];
                    \think\loader::model('AdminGroupAccess')->strict(false)->field(true)->insertAll($data);
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }
    public function editSubmitHeadEngineer()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.edit');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                if(!empty($param['pwd'])) {
                    //重置密码
                    if(empty($param['pwd_confirm']) or $param['pwd_confirm'] !== $param['pwd']) {
                        return vae_assign(0,'两次密码不一致');
                    }
                    $param['salt'] = vae_set_salt(20);
                    $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                    $param['update_time'] = time();
                } else {
                    unset($param['pwd']);
                    unset($param['salt']);
                }

                // 启动事务
                Db::startTrans();
                try{
                    \think\loader::model('Admin')->where(['id'=>$param['id']])->strict(false)->field(true)->update($param);
                    //清除菜单缓存
                    \think\Cache::clear('VAE_ADMIN_MENU');
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }

    /**
     * 安全总监
     * HeadSafety
     */
    public function headSafety()
    {
        return view('person/headSafety/index');
    }
    public function getHeadSafetyList()
    {
        $param = vae_get_param();
        $where = array();
        if(!empty($param['keywords'])) {
            $where['a.id|a.username'] = ['like', '%' . $param['keywords'] . '%'];
        }

        $admin = vae_get_login_admin();
        if (!empty($admin['project_id'])) {
            $where['project_id'] =  $admin['project_id'];
        }
        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $content = \think\loader::model('Admin')
            ->field('*')
            ->alias('a')
            ->join('admin_group_access b','a.id=b.uid')
            ->order('a.create_time desc')
            ->where($where)
            ->where(['b.group_id' => 13])
            ->where(['status' => 1,'state' => 1])
            ->paginate($rows,false,['query'=>$param])
            ->each(function ($item,$key){
                $item->last_login_time = date('Y-m-d H:i:s',$item->last_login_time);
            });

        return vae_assign_table(0,'',$content);
    }
    public function addHeadSafety()
    {
        return view('person/headSafety/add');
    }
    public function editHeadSafety()
    {
        return view('person/headSafety/edit',['admin'=>vae_get_admin(vae_get_param('id'))]);
    }
    public function deleteHeadSafety()
    {
        $id    = vae_get_param("id");
        if ($id == 1) {
            return vae_assign(0,"系统拥有者，无法删除！");
        }
        if (Db::name('Admin')->where(['id' => $id])->update(['state' => 0])) {
            return vae_assign(1,"删除成功！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }
    public function addSubmitHeadSafety()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.add');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                $param['salt'] = vae_set_salt(20);
                $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                $param['create_time'] = $param['update_time'] = time();
                $admin = vae_get_login_admin();
                $param['project_id'] = $admin['project_id'];
                $param['last_login_time'] = time();
                // 启动事务
                Db::startTrans();
                try{
                    $uid = \think\loader::model('Admin')->strict(false)->field(true)->insertGetId($param);
                    $data[] = ['uid' => $uid ,'group_id' => 13];
                    \think\loader::model('AdminGroupAccess')->strict(false)->field(true)->insertAll($data);
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }
    public function editSubmitHeadSafety()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Person.edit');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                if(!empty($param['pwd'])) {
                    //重置密码
                    if(empty($param['pwd_confirm']) or $param['pwd_confirm'] !== $param['pwd']) {
                        return vae_assign(0,'两次密码不一致');
                    }
                    $param['salt'] = vae_set_salt(20);
                    $param['pwd'] = vae_set_password($param['pwd'],$param['salt']);
                    $param['update_time'] = time();
                } else {
                    unset($param['pwd']);
                    unset($param['salt']);
                }

                // 启动事务
                Db::startTrans();
                try{
                    \think\loader::model('Admin')->where(['id'=>$param['id']])->strict(false)->field(true)->update($param);
                    //清除菜单缓存
                    \think\Cache::clear('VAE_ADMIN_MENU');
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return vae_assign(0,'提交失败:'.$e->getMessage());
                }
                return vae_assign();
            }
        }
    }

}

<?php

namespace app\admin\controller;
use app\port\model\User;
use vae\controller\AdminCheckAuth;
use think\Db;
use app\common\model\Question as ArticleModel;

class ProjectController extends AdminCheckAuth
{
    public function index()
    {
        return view();
    }
    public function getContentList()
    {
        $param = vae_get_param();
        $where = array();
        if(!empty($param['keywords'])) {
            $where['a.id|a.name'] = ['like', '%' . $param['keywords'] . '%'];
        }

        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $content = \think\loader::model('Project')
            ->field('*')
            ->alias('a')
            ->order('a.create_time desc')
            ->where($where)
            ->where(['status' => 1])
            ->paginate($rows,false,['query'=>$param])
            ->each(function ($item,$key){
               $item->project_role = Db::name('user')->where(['id' => $item->project_role])->value('name');
               $item->safe_role = Db::name('admin')->where(['id' => $item->safe_role])->value('nickname');
            });

        return vae_assign_table(0,'',$content);
    }
    public function add()
    {
        return view();
    }
    public function addSubmit()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            if ($param['safe_role'] == 0) {
                return vae_assign(0,'请选择项目安全科长');
            }
            //判断安全科长是否已经绑定项目
            $project_safe = Db::name('admin')->where(['id' => $param['safe_role']])->find();
            if (!empty($project_safe['project_id'])) {
                return vae_assign(0,'该安全科长已绑定项目');
            }

            Db::startTrans();
            $param['create_time'] = $param['update_time'] = time();
            $project_id = \think\loader::model('Project')->strict(false)->field(true)->insertGetId($param);
            //绑定安全科长
            if(!Db::name('admin')->where(['id' => $param['safe_role']])->update(['project_id' => $project_id])){
                Db::rollback();
            }

            Db::commit();
            return vae_assign();
        }
    }
    public function edit()
    {
        $data = Db::name('project')->where(['id' => vae_get_param('id')])->find();
        return view('',['data'=>$data]);
    }
    public function editSubmit()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            \think\loader::model('Project')->where(['id'=>$param['id']])->strict(false)->field(true)->update($param);
            \think\Cache::clear('VAE_ARTICLE_INFO');
            return vae_assign();
        }
    }
    public function delete()
    {
        $id    = vae_get_param("id");
        if (Db::name('Project')->where(['id' => $id])->update(['status' => 0])) {
            return vae_assign(1,"成功放入回收站！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }
}

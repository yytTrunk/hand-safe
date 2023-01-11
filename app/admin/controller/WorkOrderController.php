<?php

namespace app\admin\controller;
use vae\controller\AdminCheckAuth;
use think\Db;
use app\common\model\Question as ArticleModel;

class WorkOrderController extends AdminCheckAuth
{
    protected $type = [
      ['name' => '安全管理片区','id' => 1],
      ['name' => '网格划分片区','id' => 2]
    ];
    public function index()
    {
        return view();
    }

    //列表
    public function getContentList()
    {
        $param = vae_get_param();
        $where = array();
        if(!empty($param['keywords'])) {
            $where['a.id|a.name'] = ['like', '%' . $param['keywords'] . '%'];
        }

        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $admin = vae_get_login_admin();
        $content = \think\loader::model('WorkOrder')
            ->field('*')
            ->alias('a')
            ->order('a.create_time desc')
            ->where($where)
            ->where(['status' => 1,'project_id' => $admin['project_id']])
            ->paginate($rows,false,['query'=>$param])
            ->each(function ($item,$key){
               $item->project = Db::name('project')->where(['id' => $item->project_id])->value('name');
               if ($item->type == 1) {
                   $item->typeName = '安全管理片区';
               }else{
                   $item->typeName = '网格划分片区';
               }

            });

        return vae_assign_table(0,'',$content);
    }

    //添加
    public function add()
    {
        return view('',['type' => $this->type]);
    }

    //提交添加
    public function addSubmit()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $admin = vae_get_login_admin();
            if (!$admin['project_id']) {
                return vae_assign(0,'对不起，您暂未关联项目');
            }
            $param['project_id'] = $admin['project_id'];
            $param['create_time'] = $param['update_time'] = time();
            \think\loader::model('WorkOrder')->strict(false)->field(true)->insert($param);
            return vae_assign();
        }
    }

    //修改
    public function edit()
    {
        $data = Db::name('WorkOrder')->where(['id' => vae_get_param('id')])->find();
        return view('',['data'=>$data,'type' => $this->type]);
    }

    //提交修改
    public function editSubmit()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $param['update_time'] = time();
            \think\loader::model('WorkOrder')->where(['id'=>$param['id']])->strict(false)->field(true)->update($param);
            \think\Cache::clear('VAE_ARTICLE_INFO');
            return vae_assign();
        }
    }


    public function delete()
    {
        $id    = vae_get_param("id");
        if (Db::name('WorkOrder')->where(['id' => $id])->update(['status' => 0])) {
            return vae_assign(1,"成功放入回收站！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }
}

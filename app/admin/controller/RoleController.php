<?php

namespace app\admin\controller;
use vae\controller\AdminCheckAuth;
use think\Db;
use app\common\model\Question as ArticleModel;

class RoleController extends AdminCheckAuth
{
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
        $content = \think\loader::model('Role')
                ->field('*')
                ->alias('a')
    			->order('a.create_time desc')
                ->where($where)
                ->where(['status' => 1])
    			->paginate($rows,false,['query'=>$param]);

    	return vae_assign_table(0,'',$content);
    }

    //添加
    public function add()
    {
    	return view();
    }

    //提交添加
    public function addSubmit()
    {
    	if($this->request->isPost()){
    		$param = vae_get_param();
    		\think\loader::model('Role')->strict(false)->field(true)->insert($param);
    		return vae_assign();
    	}
    }

    //修改
    public function edit()
    {
       $data = Db::name('role')->where(['id' => vae_get_param('id')])->find();
        return view('',['data'=>$data]);
    }

    //提交修改
    public function editSubmit()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            \think\loader::model('Role')->where(['id'=>$param['id']])->strict(false)->field(true)->update($param);
            \think\Cache::clear('VAE_ARTICLE_INFO');
            return vae_assign();
        }
    }


    public function delete()
    {
        $id    = vae_get_param("id");
        if (Db::name('role')->where(['id' => $id])->update(['status' => 0])) {
            return vae_assign(1,"成功放入回收站！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }
}

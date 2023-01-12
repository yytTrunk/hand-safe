<?php

namespace app\admin\controller;
use vae\controller\AdminCheckAuth;
use think\Db;
use app\common\model\Question as ArticleModel;

class RecordController extends AdminCheckAuth
{

    public function index()
    {
        $admin = vae_get_login_admin();
        $group_id = Db::name('admin_group_access')->where(['uid' => $admin['id']])->value('group_id');
        $is_whether = 0;
        if ($group_id == 11) {
            $is_whether = 1;
        }
        return view('',[
            'is_whether'=>$is_whether
        ]);
    }

    //列表
    public function getContentList()
    {
        $param = vae_get_param();
        $where = array();
        $whereDate = array();
        if(!empty($param['keywords'])) {
            // $where['a.id|a.name'] = ['like', '%' . $param['keywords'] . '%'];
            $where['a.project_id'] = Db::name('project')->where(['name' => $param['keywords']])->value('id');
        }

        //开始时间
        if (!empty($param['start_time'])) {
            $condition1  = strtotime($param['start_time']);
            $where['a.create_time'] = array('>', $condition1);
        }

        if (!empty($param['ending_time'])) {
            $condition2  = strtotime($param['ending_time']);
            $whereDate['a.create_time'] = array('<', $condition2);
        }

        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $admin = vae_get_login_admin();
        $group_id = Db::name('admin_group_access')->where(['uid' => $admin['id']])->value('group_id');
        if ($group_id == 4 || $group_id == 5 || $group_id == 1) {
            $w = [];
        }else{
            $w = ['status' => 1,'project_id' => $admin['project_id']];
            $w['whether_work'] = 1;
            if ($group_id == 11) {
                $w['whether_work'] = 2;
            }
        }

        $content = \think\loader::model('Record')
            ->field('*')
            ->alias('a')
            ->order('a.whether_work desc,a.create_time desc')
            ->where($where)
            ->where($whereDate)
            ->where($w)
            ->paginate($rows,false,['query'=>$param])
            ->each(function ($item,$key) use ($group_id){
               $item->project = Db::name('project')->where(['id' => $item->project_id])->value('name');
               $item->work = Db::name('work_order')->where(['id' => $item->work_id])->value('name');
               $item->user = Db::name('admin')->where(['id' => $item->user_id])->value('nickname');
               if ($item->type == 1) {
                   $item->type = '危大工程打卡';
               }else{
                $item->type = '班前会打卡';
               }

               if ($item->complete == 1) {
                   $item->complete = '打卡正常';
               }else{
                   $item->complete = '打卡超出范围';
               }

               if ($item->whether_work == 1) {
                   $item->whether_work_type = '正常工作';
               }else{
                   $item->whether_work_type = '未施工无需打卡';
               }

               $item->group_id = $group_id;
            });

        return vae_assign_table(0,'',$content);
    }



    //修改
    public function status()
    {
        $id    = vae_get_param("id");
        if (Db::name('record')->where(['id' => $id])->update(['whether_work_status' => 1])) {
            return vae_assign(1,"操作成功！");
        } else {
            return vae_assign(0,"审核失败，请联系管理员！");
        }
    }

    public function delete()
    {
        $id    = vae_get_param("id");
        if (Db::name('record')->where(['id' => $id])->delete()) {
            return vae_assign(1,"成功放入回收站！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }


    public function detail() {
        $id    = vae_get_param("id");
        $data = Db::name('record')->where(['id' => $id])->find();
        $data['record_images'] = unserialize($data['record_images']);
        if (is_string($data['record_images'])) {
            $data['record_images'] = [$data['record_images']];
        }

        $data['user'] = Db::name('admin')->where(['id'=>$data['user_id']])->value('nickname');
        return view('',['data' => $data]);
    }
}

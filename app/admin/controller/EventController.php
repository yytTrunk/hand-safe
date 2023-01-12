<?php

namespace app\admin\controller;
use app\port\model\Event;
use vae\controller\AdminCheckAuth;
use think\Db;
use app\common\model\Question as ArticleModel;

class EventController extends AdminCheckAuth
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
            $where['a.id|a.question'] = ['like', '%' . $param['keywords'] . '%'];
        }

        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $admin = vae_get_login_admin();
        $group_id = Db::name('admin_group_access')->where(['uid' => $admin['id']])->value('group_id');
        if ($group_id == 4 || $group_id == 5 || $group_id == 1) {
            $w = [];
        }else{
            $w['project_id'] = $admin['project_id'];
            $w['type'] = 1;
            if ($group_id == 9)  {
                $w['type'] = 2;
            }
        }
        $content = \think\loader::model('Event')
            ->field('*')
            ->alias('a')
            ->order('a.create_time desc')
            ->where($where)
            ->where($w)
            ->where('status','<>',3)
            ->paginate($rows,false,['query'=>$param])
            ->each(function ($item,$key){
               $item->project = Db::name('project')->where(['id' => $item->project_id])->value('name');
               $item->work = Db::name('work_order')->where(['id' => $item->work_id])->value('name');
               $item->user = Db::name('admin')->where(['id' => $item->user_id])->value('nickname');
               $item->end_time = date('Y-m-d',$item->end_time);
               if ($item->status == Event::STATE_NORMAL) {
                   $item->status = '待整改';
               }elseif ($item->status == Event::STATE_PENDING) {
                   $item->status = '待审核';
               }elseif ($item->status == Event::STATE_ENDING) {
                   $item->status = '已结束';
               }elseif ($item->status == Event::STATE_PENDING_SAFE){
                   $item->status = '待确认';
               }
            });

        return vae_assign_table(0,'',$content);
    }



    //确认
    public function status()
    {
        $data = Db::name('event')->where(['id' => vae_get_param('id')])->find();
        $data['user'] = Db::name('admin')->where(['id' => $data['user_id']])->value('nickname');;
        $data['project'] = Db::name('project')->where(['id' => $data['project_id']])->value('name');
        $data['work'] = Db::name('work_order')->where(['id' => $data['work_id']])->value('name') ?? '未选择工区';
        $data['images'] = unserialize($data['images']);
        $data['end_time'] = date('Y-m-d',$data['end_time']);
        return view('',['data'=>$data,]);
    }

    //修改
    public function statusSubmit()
    {
        if($this->request->isPost()) {
            $param = vae_get_param();
            $data['end_time'] = strtotime($param['end_time']);
            $data['update_time'] = time();
            $data['id'] = $param['id'];
            $data['audit_msg'] = $param['audit_msg'];
            if ($param['status']  == 1) {
                $data['status'] = Event::STATE_NORMAL;
            }else{
                $data['status'] = Event::STATE_NORMAL;
            }

            \think\loader::model('Event')->strict(false)->field(true)->update($data);
            return vae_assign();
        }
    }


    public function delete()
    {
        $id    = vae_get_param("id");
        if (Db::name('event')->where(['id' => $id])->update(['state' => 0])) {
            return vae_assign(1,"成功放入回收站！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }

    //修改
    public function detail()
    {
        $data = Db::name('event')->where(['id' => vae_get_param('id')])->find();
        $data['user'] = Db::name('admin')->where(['id' => $data['user_id']])->value('nickname');;
        $data['project'] = Db::name('project')->where(['id' => $data['project_id']])->value('name');
        $data['work'] = Db::name('work_order')->where(['id' => $data['work_id']])->value('name') ?? '未选择工区';
        $data['images'] = unserialize($data['images']);
        if (!empty($data['reform_images'])) {
            $data['reform_images'] = unserialize($data['reform_images']);
        }
        $data['end_time'] = date('Y-m-d',$data['end_time']);

        return view('',['data'=>$data,]);
    }

    //审核得
    public function accept()
    {
        $data = Db::name('event')->where(['id' => vae_get_param('id')])->find();
        $data['user'] = Db::name('admin')->where(['id' => $data['user_id']])->value('nickname');;
        $data['project'] = Db::name('project')->where(['id' => $data['project_id']])->value('name');
        $data['work'] = Db::name('work_order')->where(['id' => $data['work_id']])->value('name') ?? '未选择工区';
        $data['images'] = unserialize($data['images']);
        if (!empty($data['reform_images'])) {
            $data['reform_images'] = unserialize($data['reform_images']);
        }
        $data['end_time'] = date('Y-m-d',$data['end_time']);

        return view('',['data'=>$data,]);
    }

    //修改
    public function acceptSubmit()
    {
        if($this->request->isPost()) {
            $param = vae_get_param();
            $data['end_time'] = strtotime($param['end_time']);
            $data['update_time'] = time();
            $data['id'] = $param['id'];
            $data['reject_reason'] = $param['reject_reason'];
            if ($param['status'] == 1){
                $data['status'] = Event::STATE_ENDING;
            }else{
                $data['status'] = Event::STATE_NORMAL;
            }

            \think\loader::model('Event')->strict(false)->field(true)->update($data);
            return vae_assign();
        }
    }
}

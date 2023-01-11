<?php

namespace app\admin\controller;
use app\common\model\AdminScore;
use app\port\model\Event;
use PhpOffice\PhpSpreadsheet\IOFactory;
use vae\controller\AdminCheckAuth;
use think\Db;
use app\common\model\AdminScoreChange;

class ScoreController extends AdminCheckAuth
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
            $where['id|description'] = ['like', '%' . $param['keywords'] . '%'];
        }

        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $admin = vae_get_login_admin();
        $group_id = Db::name('admin_group_access')->where(['uid' => $admin['id']])->value('group_id');
        if ($group_id == 4 || $group_id == 5 || $group_id == 3) {
            $w = [];
        }else{
            $w['project_id'] = $admin['project_id'];
        }
        $content = \think\loader::model('AdminScoreChange')
            ->field('*')
            ->order('create_time desc')
            ->where($where)
            ->where($w)
            ->paginate($rows,false,['query'=>$param])
            ->each(function ($item,$key){
                $item->is_complete = 0;
                if ($item->status_quality == 1 && $item->status_safe==1 &&$item->status_project==1&&$item->status_project_total==1) {
                    $item->is_complete = 1;
                }

               $item->project = Db::name('project')->where(['id' => $item->project_id])->value('name');
                if ($item->type == 0) {
                    $item->type = '减分';
                    $item->score = '-'.$item->score;
                }else{
                    $item->type = '加分';
                    $item->score = '+'.$item->score;
                }

//                if ($item->status_quality == 0) {
//                    $item->status_quality = '待审核';
//                }else{
//                    $item->status_quality = '已审核';
//                }
//
//                if ($item->status_safe == 0) {
//                    $item->status_safe = '待审核';
//                }else{
//                    $item->status_safe = '已审核';
//                }
//
//                if ($item->status_project == 0) {
//                    $item->status_project = '待审核';
//                }else{
//                    $item->status_project = '已审核';
//                }
//
//                if ($item->status_project_total == 0) {
//                    $item->status_project_total = '待审核';
//                }else{
//                    $item->status_project_total = '已审核';
//                }

//                $item->create_time = date('Y-m-d',$item->create_time);
                $user = Db::name('admin')->where(['id' => $item->user_id])->find();
                $item->user = $user['nickname'].'['.$user['phone'].']';
            });

        return vae_assign_table(0,'',$content);
    }



    //修改
    public function add()
    {
        return view();
    }

    //修改
    public function addSubmit()
    {
        if($this->request->isPost()) {
            $param = vae_get_param();
            $data = [];
            $admin = vae_get_login_admin();
            $user = Db::name('admin')->where(['phone' => $param['phone'],'project_id' => $admin['project_id'],'is_grid' => 1])->find();
            if (!$user) {
                return vae_assign(0,'该用户不存在');
            }
            $data['user_id'] = $user['id'];
            $data['project_id'] = $user['project_id'];
            $data['description'] = $param['description'];
            $data['type'] = $param['type'];
            $data['score'] = $param['score'];
            $data['create_time'] = $data['update_time'] = time();
            if (!Db::name('admin_score_change')->insertGetId($data)) {
                return  vae_assign(0,'创建失败');
            }

            return vae_assign(1,'创建成功');
        }
    }


    public function delete()
    {
        $admin = vae_get_login_admin();
        $group_id = Db::name('admin_group_access')->where(['uid' => $admin['id']])->value('group_id');
        if ($group_id != 7) {
            return vae_assign(0,'您无删除调整积分权限');
        }

        $id    = vae_get_param("id");
        $change = Db::name('admin_score_change')->where(['id' => $id])->find();
        if ($change['status_quality']==1 && $change['status_safe']==1 && $change['status_project'] ==1 && $change['status_project_total']==1) {
            return vae_assign(0,'该审核单已全部审核通过，无法删除');
        }

        if (Db::name('admin_score_change')->where(['id' => $id])->update(['state' => 0])) {
            return vae_assign(1,"删除成功！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }


    //审核
    public function status()
    {
        $id    = vae_get_param("id");
        $change = Db::name('admin_score_change')->where(['id' => $id])->find();
        $admin = vae_get_login_admin();
        $group_id = Db::name('admin_group_access')->where(['uid' => $admin['id']])->value('group_id');
        if ($group_id == 7 || $group_id == 13 || $group_id == 11 || $group_id ==12) {
            $data = [];
            if ($group_id==7) {
                $data = ['status_safe' => 1];
                if ($change['status_safe'] ==1) {
                    return vae_assign(0,'您已审核通过');
                }
            }
            if ($group_id == 13) {
                $data = ['status_project_total' => 1];
                if ($change['status_project_total'] ==1) {
                    return vae_assign(0,'您已审核通过');
                }
            }
            if ($group_id == 11) {
                $data = ['status_quality' => 1];
                if ($change['status_quality'] ==1) {
                    return vae_assign(0,'您已审核通过');
                }
            }

            if ($group_id == 12) {
                $data = ['status_project' => 1];
                if ($change['status_project'] ==1) {
                    return vae_assign(0,'您已审核通过');
                }
            }

            if (Db::name('admin_score_change')->where(['id' => $id])->update($data)){
                Db::startTrans();
                $change = Db::name('admin_score_change')->where(['id' => $id])->find();
                if ($change['status_quality']==1 && $change['status_safe']==1 && $change['status_project'] ==1 && $change['status_project_total']==1) {
                    //执行分数
                    $user = Db::name('admin')->where(['id' => $change['user_id']])->find();
                    if ($change['type'] == 1) {
                        if (Db::name('admin')->where(['id' => $change['user_id']])->inc('score',$change['score'])->update()) {
                            $msg = $change['description'];
                            $data = [
                                'project_id' => $user['project_id'],
                                'work_id' => 0 ,
                                'content' => $msg,
                                'type' => 1,
                                'user_id' => $user['id'],
                                'score' => $change['score'],
                                'update_time' => time(),
                                'create_time' => time(),
                                'ralate_type' => 2,
                                'related_id' => $change['id']
                            ];

                            if (!Db::name('admin_score')->insertGetId($data)) {
                                Db::rollback();
                            }

                        }else{
                            Db::rollback();
                            return vae_assign(0,'加分失败');
                        }

                    }else{
                        if (Db::name('admin')->where(['id' => $change['user_id']])->dec('score',$change['score'])->update()) {
                            $msg = $change['description'];
                            $data = [
                                'project_id' => $user['project_id'],
                                'work_id' => 0 ,
                                'content' => $msg,
                                'type' => 0,
                                'user_id' => $user['id'],
                                'score' => $change['score'],
                                'update_time' => time(),
                                'create_time' => time(),
                                'ralate_type' => 2,
                                'related_id' => $change['id']
                            ];
                            if (!Db::name('admin_score')->insertGetId($data)) {
                                Db::rollback();
                            }

                        }else{
                            Db::rollback();
                            return vae_assign(0,'扣分失败');
                        }
                    }
                }
                Db::commit();
                return vae_assign(1,"审核成功！");
            } else {
                return vae_assign(0,"审核失败！");
            }
        }

        return vae_assign(0,'您无审核调整积分权限');

    }
}

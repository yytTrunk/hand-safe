<?php

namespace app\port\controller;
use app\common\model\Admin;
use app\common\model\Code;
use app\port\model\Event;
use app\port\model\Project;
use app\port\model\Record;
use app\port\model\Role;
use app\port\model\User;
use app\port\model\WorkOrder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use think\Cache;
use think\Db;
use think\Session;
use vae\controller\PortControllerBase;

class MainController extends PortControllerBase
{
    /**
     * 打卡
     * @return \think\response\Json
     */
    public function record()
    {
        if ( !$this->params()['project_id'] ) {
            return $this->jsonFail('参数不全');
        }

        if (!$this->checkProject()){
            return $this->jsonFail('项目/工区未找到');
        }

        $model = new Record();
//        $model->name = $this->params()['name']; //冗余字段，暂时不需要
        $model->user_id = $this->params()['user_id'];

        if ($this->params()['type'] == 1) {
            //日常打卡，调用地图
            $distance = 100;

            if ($this->params()['distance'] > $distance) {
                $model->complete = 2;
            }else{
                $model->complete = 1;
                //打卡积分改为脚本运行
//                Db::name('admin')->where(['id' => $this->params()['user_id']])->setInc('score',1);
            }

            if (!empty($this->params()['record_images'])) {
                $images = [];
                foreach ($this->params()['record_images'] as $img) {
                    $images = str_replace(array("\r\n", "\r", "\n"), "", $img);
                }
                $model->record_images = serialize($images);
                $model->record_type = 1; //打卡不准使用图片打卡
                $model->complete = 1;
            }

            $model->type =1 ;
            $model->address = $this->params()['address'];
            $model->distance = $this->params()['distance'];
            $model->user_lat_id = $this->params()['lat_id'];
        }else{
            $model->type =2 ;
            $images = [];
            foreach ($this->params()['images'] as $img) {
                $images[] = str_replace(array("\r\n", "\r", "\n"), "", $img);
            }

            $model->images = serialize($images);
        }

        $model->project_id = $this->params()['project_id'];
        $model->work_id = $this->params()['work_id'];
        $model->describution = $this->params()['describution'];
        if (!$model->save()) {
            return $this->jsonFail('打卡失败，请联系管理员');
        }

        return $this->jsonSuccess('OK');
    }

    /**
     * 最新打卡
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function record_today()
    {
        $record =   Record::where(['user_id' => $this->params()['user_id']])
//            ->where('create_time','>',strtotime(date('Y-m-d')))
                ->where(['type' =>1 ])
            ->order('create_time','desc')->select();
        if (!$record) {
            return $this->jsonFail('暂无打卡记录');
        }

        foreach ($record as &$v) {
            $v['project_name'] = Db::name('project')->where(['id' => $v['project_id']])->value('name');
            $v['work_name'] = Db::name('work_order')->where(['id' => $v['work_id']])->value('name');
            if ($v['complete'] == 1) {
                $v['desc'] = '打卡正常';
            }else{
                $v['desc'] = '超出范围，打卡异常';
            }
        }


        return $this->jsonSuccess('OK',$record);
    }

    public function whether() {
        //代班
        if ($this->params()['whether'] == 1) {
            $data = [
                'user_id' => $this->params()['user_id'],
                'project_id' => $this->params()['project_id'],
                'whether_user_id' => $this->params()['whether_user_id'],
                'whether_work_reason' => $this->params()['whether_work_reason'],
                'whether_date' => $this->params()['whether_date'],
                'whether_date_time' => strtotime($this->params()['whether_date']),
                'status' => 0,
                'create_time' => time(),
                'update_time' => time(),
            ];

            $pendingWhether = Db::name('record_whether')->where(['status' => 0,'user_id'=>$this->params()['user_id'],'whether_date_time' => strtotime($this->params()['whether_date'])])->find();
            if ($pendingWhether) {
                return $this->jsonFail('你已提交代班申请，请勿重复提交');
            }

            $normalWhether = Db::name('record_whether')->where(['status' => 1,'user_id'=>$this->params()['user_id'],'whether_date_time' => strtotime($this->params()['whether_date'])])->find();
            if($normalWhether) {
                return $this->jsonFail('你的代班申请已通过,请勿重复代班');
            }

            $byNormalWhether = Db::name('record_whether')->where(['status' => 1,'whether_user_id'=>$this->params()['user_id'],'whether_date_time' => strtotime($this->params()['whether_date'])])->find();
            if ($byNormalWhether) {
                return $this->jsonFail('你已接受别人的代班,不能在代班');
            }


            if (!Db::name('record_whether')->insert($data)) {
                return $this->jsonFail('提交失败，请重试');
            }

        }else{
            $data = [
                'user_id' => $this->params()['user_id'],
                'project_id' => $this->params()['project_id'],
                'type' => 1,
                'whether_work' => 2,
                'whether_work_reason' => $this->params()['whether_work_reason'],
                'whether_work_status' => 0,
                 'create_time' => time(),
                'update_time' => time(),
            ];

            if (!Db::name('record')->insert($data)) {
                return $this->jsonFail('提交失败，请重试');
            }
        }

        return $this->jsonSuccess('提交成功，等待审核');
    }

    /**
     * 代班申请
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function clas() {
        $cals = Db::name('record_whether')->where(['whether_user_id' => $this->params()['user_id']])->whereOr(['user_id' => $this->params()['user_id']])->select();
//        $cals = Db::name('record_whether')->where(['user_id' => $this->params()['user_id']])->select();
        foreach ($cals as &$v) {
            $v['user'] = Db::name('admin')->where(['id' => $v['user_id']])->find();
            if ($v['user_id'] == $this->params()['user_id']) {
                $v['is_local'] = 1;
            }else{
                $v['is_local'] = 2;
            }
        }

        return $this->jsonSuccess('OK',$cals);
    }

    public function accept() {
        if (!Db::name('record_whether')->where(['id'=>$this->params()['id']])->update(['status'=>1])){
            return $this->jsonFail('提交失败，请重试');
        }

        return $this->jsonSuccess('提交成功，等待审核');
    }

    public function reject() {
        if (!Db::name('record_whether')->where(['id'=>$this->params()['id']])->update(['status'=>2])){
            return $this->jsonFail('提交失败，请重试');
        }

        return $this->jsonSuccess('提交成功，等待审核');
    }

    /**
     * 上报事件
     * @return \think\response\Json
     */
    public function event()
    {
        if (!$this->params()['images'] ||  !$this->params()['project_id'] || !$this->params()['date']) {
            return $this->jsonFail('参数不全');
        }



        if (!$this->checkProject()){
            return $this->jsonFail('项目/工区未找到');
        }

        $images = [];
        foreach ($this->params()['images'] as $img) {
            $images[] = str_replace(array("\r\n", "\r", "\n"), "", $img);
        }
        $this->params()['images'] = $images;

        $model = new Event();
        $model->user_id = $this->params()['user_id'];
        $model->project_id = $this->params()['project_id'];
        $model->work_id = isset($this->params()['work_id'])?$this->params()['work_id']:0;
        $model->images = serialize($this->params()['images']);
        $model->unit_project = isset($this->params()['unit_project'])?$this->params()['unit_project']:'';
        $model->work_content = isset($this->params()['work_content']) ?$this->params()['work_content']:'';
        $model->subject = isset($this->params()['subject']) ?$this->params()['subject']:'';
        $model->question = isset($this->params()['question'])?$this->params()['question']:'';
        $model->describution = $this->params()['describution'];
        $model->type = (int)$this->params()['type'] + 1;
        $model->state = 1;
        $model->end_time = strtotime($this->params()['date']) + 86400;

        if ($model->type == 1) {
            if ($this->params()['is_submit_to_safe']+1 == 1) {
                $model->status = Event::STATE_PENDING_SAFE;
            }else{
                $model->status = Event::STATE_NORMAL;
            }
        }else{
            $model->status = Event::STATE_NORMAL;
        }




        if (isset($this->params()['question'])) {
            $question = Db::name('question')->where('describution','=',$this->params()['question'])->find();
            if ($question) {
                $model->source = $question['source'];
            }
        }

        if (!$model->save()) {
            return $this->jsonFail('上报失败，请联系管理员');
        }

        return $this->jsonSuccess('OK');
    }

    public function eventSubmit(){
        if (!$this->params()['event_id'] || !$this->params()['image'] || !$this->params()['msg']) {
            return $this->jsonFail('参数不全');
        }


        $images = [];
        foreach ($this->params()['image'] as $img) {
            $images[] = str_replace(array("\r\n", "\r", "\n"), "", $img);
        }
        $this->params()['image'] = $images;

        $event = Event::where(['id' => $this->params()['event_id']])->find();
        $event->reform_images = serialize($this->params()['image']);
        $event->msg = $this->params()['msg'];
        $event->status = Event::STATE_PENDING;
        if (!$event->save()) {
            return $this->jsonFail('提交失败，联系管理员');
        }

        return  $this->jsonSuccess('提交成功');
    }

    /**
     * 撤回事件
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cancelEvent()
    {
        $event = Event::where(['id' => $this->params()['event_id']])->find();
        $event->status = Event::STATE_CANCEL;
        if (!$event->save()) {
            return $this->jsonFail('取消失败');
        }

        return $this->jsonSuccess('取消成功');
    }

    /**
     * 事件列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function eventList()
    {
        $events = Event::where(['state' => 1,'user_id' => $this->params()['user_id']])->order('update_time desc')->select();
        foreach ($events as $event) {
            $event->end_time = date('Y-m-d',$event->end_time);
            if ($event->status == Event::STATE_NORMAL) {
                $event->status = '待整改';
            }elseif($event->status == Event::STATE_CANCEL){
                $event->status = '已撤回';
            }elseif($event->status == Event::STATE_PENDING){
                $event->status = '待审核';
            }elseif ($event->status == Event::STATE_PENDING_SAFE){
                $event->status = '待确认';
            }else{
                $event->status = '已结束';
            }
        }

        return $this->jsonSuccess('OK',$events);
    }

    /**
     * 事件详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function eventDetail()
    {
        $event = Event::where(['id' => $this->params()['event_id']])->find();
        if (!$event) {
            return $this->jsonFail('该项目不存在');
        }

        $event->project = Db::name('project')->where(['id' => $event->project_id])->find();
        $event->work = Db::name('work_order')->where(['id' => $event->work_id])->find();
        $event->end_time = date('Y-m-d',$event->end_time);
        $safe_name = Db::name('admin a')
            ->join('admin_group_access b','a.id=b.uid')
            ->where(['b.group_id' => 7,'a.project_id' => $event->project_id])
            ->value('a.nickname');

        $event->safeRoleAndSmall = $safe_name. '/'. Db::name('admin')->where(['id' => $event->user_id])->value('nickname');
        if (!$event->work) {
            $event->work = ['name' => '未选择工区'];
        }



        $event->images = unserialize($event->images);
        $images = [];
        if ($event->images) {
            foreach ($event->images as $image) {
                $images[] = $this->baseImg.str_replace(array("\r\n", "\r", "\n"), "", $image);
            }
        }

        $event->images = $images;

        $event->reform_images = unserialize($event->reform_images);
        $images = [];
        if ($event->reform_images) {
            foreach ($event->reform_images as $image) {
                $images[] = $this->baseImg.str_replace(array("\r\n", "\r", "\n"), "", $image);
            }
        }


        $event->reform_images = $images;
        return $this->jsonSuccess('OK',$event);
    }

    /**
     * 项目详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function project()
    {
        $project = Project::where(['id' => $this->params()['project_id']])->find();
        if (!$project) {
            return $this->jsonFail('该项目不存在');
        }

        $workOrders = Db::name('work_order')->where(['project_Id' => $project->id,'status' => 1])->select();
        foreach ($workOrders as &$workOrder) {
            $workOrder['project_name'] = $project->name;
            //安全科长
            $workOrder['safe_name'] =  Db::name('admin a')
                                        ->join('admin_group_access b','a.id=b.uid')
                                        ->where(['b.group_id' => 7,'a.project_id' => $project->id])
                                        ->value('a.nickname');

            $data =  Db::name('admin_work a')
                                        ->join('admin b','a.admin_id=b.id')
                                        ->where(['a.project_id' => $project->id ,'b.is_grid' => 1,'a.work_id' => $workOrder['id']])
                                        ->field('b.nickname,b.grid_type')
                                        ->select();
            $workOrder['safe_name_small'] = '';  //安全员
            $workOrder['safe_name_grid'] = '';   //网格员
            foreach ($data as $v) {
                if ($v['grid_type'] == 1) {
                    $workOrder['safe_name_small'] .= $v['nickname'].',';
                }else{
                    $workOrder['safe_name_grid'] .=  $v['nickname'].',';
                }
            }

        }
        $users = Db::name('admin')->where(['state' => 1,'project_id' => $project->id])->field('nickname,thumb')->select();
        foreach ($users as &$user) {
            $user['thumb'] = str_replace('\\','/',$user['thumb']);
        }
        $project->projectUser = $users;

        $project->workOrder = $workOrders;
//        $s = $project->create_time;
        return $this->jsonSuccess('OK',$project);
    }

    /**
     * 项目列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function projectList()
    {
        $projects = Project::where(['status' => 1])->select();
        return $this->jsonSuccess('OK',$projects);
    }


    public function checkProject()
    {
        if (!Project::where(['id' => $this->params()['project_id']])->find()) {
            return false;
        }

//        if (!WorkOrder::where(['id' => $this->params()['work_id']])->find()) {
//            return false;
//        }

        return true;
    }

}

<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use \think\facade\Db;

class CheckExitsDayRecord extends Command
{
    public $startTime;
    public $endTime;
    protected function configure()
    {

        $this->startTime = strtotime(date('Y-m-d',time()));
//        $this->startTime = 1656691200;
        $this->endTime = (int) $this->startTime + 86400;
        // 指令配置
        $this->setName('check-exits-day-record')
            ->setDescription('每日打卡积分检测');
    }

    protected function execute(Input $input, Output $output)
    {
        $startTime = $this->startTime;
        $endTime = $this->endTime;
        $gridLists = Db::name('admin')->where(['is_grid' => 1,'status' => -1,'state' => 1,'work_status' => 1])->select();
        foreach ($gridLists as $grid) {

            $whether = Db::name('record_whether')
                ->where('whether_date_time','>=',$startTime)
                ->where('whether_date_time','<',$endTime)
                ->where(['status' => 1,'user_id' => $grid['id']])
                ->find();

            //是否申请代班
            if ($whether) {
                //带班人数据
                $grid_replace = Db::name('admin')->where(['is_grid' => 1,'status' => -1,'state' => 1])->where(['id' => $whether['whether_user_id']])->find();
                $this->record_replace($grid,$grid_replace);
            }else{
                $this->record($grid);
            }

        }
    }

    public function record($grid) {
        var_dump($grid['id']);
        var_dump($grid['nickname']);
        $startTime = $this->startTime;
        $endTime = $this->endTime;
        $user_records = Db::name('admin_lat')->where(['user_id' => $grid['id']])->select();
        //特殊原因不打卡，不进行积分检测
        $record_whether = Db::name('record')->where(['user_id' => $grid['id'],'type' => 1,'whether_work' => 2,'whether_work_status' => 1])->where('create_time','>=',$startTime)->where('create_time','<',$endTime)->find();
        if (!$record_whether) {
            foreach ($user_records  as $user_record) {
                //检测是否有正常打卡
//                $record = Db::name('record')->where(['user_id' => $grid['id'],'type' => 1,'user_lat_id' => $user_record['id'],'complete' => 1])->where('create_time','>',$startTime)->where('create_time','<',$endTime)->find();
                $record = Db::name('record')->where(['user_id' => $grid['id'],'type' => 1,'complete' => 1])->where('create_time','>=',$startTime)->where('create_time','<',$endTime)->find();
                var_dump($record);
                if (!$record) {
                    Db::startTrans();
                    //未打卡积分-1
                    if (!Db::name('admin')->where(['id' => $grid['id']])->dec('score',1)->update()) {
                        Db::rollback();
                        continue;
                    }

                    $msg = date('Y-m-d',$this->startTime).'未打卡'.'打卡点:'.$user_record['name'];
                    $data = [
                        'project_id' => $grid['project_id'],
                        'work_id' => $user_record['work_id'] ?? 0 ,
                        'content' => $msg,
                        'type' => 0,
                        'user_id' => $grid['id'],
                        'score' => 1,
                        'update_time' => time(),
                        'create_time' => time(),
                        'ralate_type' => 1,
                        'related_id' => $user_record['id']
                    ];
                    if (!Db::name('admin_score')->insertGetId($data)) {
                        Db::rollback();
                        continue;
                    }

                    Db::commit();
                }
            }
        }
    }

    public function record_replace($grid,$grid_replace) {
        $startTime = $this->startTime;
        $endTime = $this->endTime;
        $user_records = Db::name('admin_lat')->where(['user_id' => $grid['id']])->select();
        //特殊原因不打卡，不进行积分检测
        $record_whether = Db::name('record')->where(['user_id' => $grid['id'],'type' => 1,'whether_work' => 2])->where('create_time','>=',$startTime)->where('create_time','<',$endTime)->find();
        if (!$record_whether) {
            foreach ($user_records  as $user_record) {
                //检测是否有正常打卡
                $record = Db::name('record')->where(['user_id' => $grid_replace['id'],'type' => 1,'user_lat_id' => $user_record['id'],'complete' => 1])->where('create_time','>=',$startTime)->where('create_time','<',$endTime)->find();
                if (!$record) {
                    Db::startTrans();
                    //未打卡积分-1
                    if (!Db::name('admin')->where(['id' => $grid_replace['id']])->dec('score',1)->update()) {
                        Db::rollback();
                        continue;
                    }

                    $msg = date('Y-m-d',$this->startTime).'未打卡'.'打卡点:'.$user_record['name'].'[代替打卡'.$grid['nickname'].']';
                    $data = [
                        'project_id' => $grid['project_id'],
                        'work_id' => $user_record['work_id'] ?? 0,
                        'content' => $msg,
                        'type' => 0,
                        'user_id' => $grid_replace['id'],
                        'score' => 1,
                        'update_time' => time(),
                        'create_time' => time(),
                        'ralate_type' => 1,
                        'related_id' => $user_record['id']
                    ];
                    if (!Db::name('admin_score')->insertGetId($data)) {
                        Db::rollback();
                        continue;
                    }
                    Db::commit();
                }
            }
        }
    }
}

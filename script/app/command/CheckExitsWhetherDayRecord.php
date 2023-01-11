<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\Db;

class CheckExitsWhetherDayRecord extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('check-exits-day-record')
            ->setDescription('每日打卡积分检测');
    }

    protected function execute(Input $input, Output $output)
    {
        $startTime = strtotime(date('Y-m-d',time()));
        $endTime = (int) $startTime + 86400;
        $gridLists = Db::name('admin')->where(['is_grid' => 1,'status' => -1,'state' => 1,'work_status' => 1])->select();
        //代班情况
        foreach ($gridLists as $grid) {
            $user_records = Db::name('admin_lat')->where(['user_id' => $grid['id']])->select();
            //特殊原因不打卡，不进行积分检测
            $record_whether = Db::name('record')->where(['user_id' => $grid['id'],'type' => 1,'whether' => 2])->where('create_time','>=',$startTime)->where('create_time','<',$endTime)->find();
            if (!$record_whether) {
                foreach ($user_records  as $user_record) {
                    //检测是否有正常打卡
                    $record = Db::name('record')->where(['user_id' => $grid['id'],'type' => 1,'user_lat_id' => $user_record['id'],'complete' => 1])->where('create_time','>',$startTime)->where('create_time','<',$endTime)->find();
                    if (!$record) {
                        Db::startTrans();
                        //未打卡积分-1
                        if (!Db::name('admin')->where(['id' => $grid['id']])->setDec('score',1)) {
                            Db::rollback();
                        }

                        $msg = date('Y-m-d',time()).'未打卡'.'打卡点:'.$user_record['name'];
                        $data = [
                            'project_id' => $grid['project_id'],
                            'work_id' => $user_record['work_id'],
                            'content' => $msg,
                            'type' => 0,
                            'user_id' => $grid['id'],
                            'score' => 1,
                            'update_time' => time(),
                            'create_time' => time()
                        ];
                        if (!Db::name('admin_score')->insertGetId($data)) {
                            Db::rollback();
                        }

                        Db::commit();
                    }
                }
            }

            //代班情况
            $whether = Db::name('record_whether')->where('whether_date_time','>',$startTime)->where('whether_date_time','<',$endTime)->where(['status' => 1])->select();
//            foreach ($whether)
        }
    }
}

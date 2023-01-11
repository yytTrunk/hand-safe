<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use \think\facade\Db;

class CheckExitsRecord extends Command
{
    public $start_date;
    public $end_date;
    protected function configure()
    {
        $this->start_date = strtotime(date('Y-m-d',time()));
        $this->end_date = (int)$this->start_date + 86400;
        $this->start_date = (int)$this->end_date - 86400*7;
        // 指令配置
        $this->setName('check-exits-record')
            ->setDescription('每周是否班前会打卡');
    }

    protected function execute(Input $input, Output $output)
    {
        // 每周班前会是否打卡脚本
        Db::startTrans();
        $gridLists = Db::name('admin')->where(['is_grid' => 1,'status' => -1,'state' => 1,'work_status' => 1])->select();
        foreach ($gridLists as $grid) {
            $record = Db::name('record')->where(['type' => 2,'user_id' => $grid['id']])->where('create_time','>',$this->start_date)->where('create_time','<',$this->end_date)->find();
            if (!$record) {
                if (!Db::name('admin')->where(['id' => $grid['id']])->dec('score',1)->update()) {
                    Db::rollback();
                    $output->writeln('扣分失败1');
                    return;
                }
                $msg = '未按时进行班前会打卡';
                $data = [
                    'project_id' => $grid['project_id'],
                    'work_id' => $grid['work_id'] ?? 0 ,
                    'content' => $msg,
                    'type' => 0,
                    'user_id' => $grid['id'],
                    'score' => 1,
                    'update_time' => time(),
                    'create_time' => time(),
                    'ralate_type' => 4,
                ];
                if (!Db::name('admin_score')->insertGetId($data)) {
                    Db::rollback();
                    $output->writeln('扣分失败2');
                    return;
                }
            }
        }

        Db::commit();
    }
}

<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;

class CheckOrderEndingDay extends Command
{
    public $start_date;
    public $end_date;
    protected function configure()
    {
        $this->start_date = strtotime(date('Y-m-d',time()));
//        $this->start_date = 1656950400;
        $this->end_date = (int)$this->start_date + 86400;
        // 指令配置
        $this->setName('check-order-ending-day')
            ->setDescription('每日工单检测');
    }

    protected function execute(Input $input, Output $output)
    {

        $data = Db::name('event')->where(['state' => 1])->where('end_time','>=',$this->start_date)->where('end_time','<',$this->end_date)->select();
        Db::startTrans();
        foreach ($data as $item) {
            if ($item['status'] != 4) {
                //为按时完成工单，
                if (!Db::name('admin')->where(['id' => $item['user_id']])->dec('score',1)->update()) {
                    Db::rollback();
                    $output->writeln('扣分失败1');
                    return;
                }

                $msg = '未按时完成工单，工单id:'.$item['id'].'时间：'.date('Y-m-d',$item['end_time']);
                $data = [
                    'project_id' => $item['project_id'],
                    'work_id' => $item['work_id'] ?? 0 ,
                    'content' => $msg,
                    'type' => 0,
                    'user_id' => $item['user_id'],
                    'score' => 1,
                    'update_time' => time(),
                    'create_time' => time(),
                    'ralate_type' => 3,
                    'related_id' => $item['id']
                ];

                if (!Db::name('admin_score')->insertGetId($data)) {
                    Db::rollback();
                    $output->writeln('扣分失败2');
                    return;
                }
            }
        }

        Db::commit();
        // 指令输出
        $outTime = date('Y-m-d',$this->start_date);
        $output->writeln($outTime.'检测每日工单成功');
    }
}

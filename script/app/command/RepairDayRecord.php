<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;

class RepairDayRecord extends Command
{
    public $start_date;
    public $end_date;
    protected function configure()
    {
        $date = '2022-05-06';
        $this->start_date = strtotime($date);
        $this->end_date = (int)$this->start_date + 86400;
        // 指令配置
        $this->setName('repair-day-record')
            ->setDescription('修复数据');
    }

    protected function execute(Input $input, Output $output)
    {
        $data = Db::name('admin_score')->where('create_time','>=',$this->start_date)->where('create_time','<',$this->end_date)->select();
        if (!$data->count()) {
            $output->writeln('无可修复数据，请确认日期是否正确');
            return;
        }

        Db::startTrans();
        foreach ($data as $v){
            if (!Db::name('admin')->where(['id' => $v['user_id']])->inc('score',1)->update()) {
                Db::rollback();
                $output->writeln('修复失败1');
                return;
            }

            if (!Db::name('admin_score')->where(['id' => $v['id']])->delete()) {
                Db::rollback();
                $output->writeln('修复失败2');
                return;
            }
        }
        Db::commit();
        // 指令输出
        $output->writeln('修复成功');
    }
}

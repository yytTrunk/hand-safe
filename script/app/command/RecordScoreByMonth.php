<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;

class RecordScoreByMonth extends Command
{
    public $month;
    public $year;
    public $quarter = 0;
    protected function configure()
    {
        $this->year = date('Y',time());
        $this->month = date('m',time());

        if ($this->month == '03') {
            $this->quarter = 1;
        }

        if ($this->month == '06') {
            $this->quarter = 2;
        }

        if ($this->month == '09') {
            $this->quarter = 3;
        }

        if ($this->month == '12') {
            $this->quarter = 4;
        }
        // 指令配置
        $this->setName('record-score-by-month')
            ->setDescription('每月记录分数-季度记录平均分数');
    }

    protected function execute(Input $input, Output $output)
    {
        $lastDay = date( 't');
        $day = date('d');

        if ($lastDay != $day) {
            $output->writeln('不是月末最后一天');
            return  false;
        }

        $gridLists = Db::name('admin')->where(['is_grid' => 1,'status' => -1,'state' => 1])->select();
        Db::startTrans();
        foreach ($gridLists as $grid) {
            $data = [
                'year' => $this->year,
                'month' => $this->month,
                'user_id' => $grid['id'],
                'score' => $grid['score'],
                'update_time' => time(),
                'create_time' => time(),
            ];

            if (Db::name('admin_score_record')->where(['year' => $this->year,'month' => $this->month,'user_id' => $grid['id']])->find()) {
                Db::rollback();
                $output->writeln('该月份已记录分数');
                return false;
            }

            if (!Db::name('admin_score_record')->insertGetId($data)) {
                Db::rollback();
                $output->writeln('记录分数失败');
                return false;
            }

            if (!Db::name('admin')->where(['id' => $grid['id']])->update(['score' => 100,'update_time'=>time()])) {
                Db::rollback();
                $output->writeln('重置积分失败');
                return false;
            }
        }

        Db::commit();
        $output->writeln('记录积分成功');

        if ($this->quarter != 0) {
            Db::startTrans();
            switch ($this->quarter) {
                case 1:
                    $month = ['01','02','03'];
                    break;
                case 2:
                    $month = ['04','05','06'];
                    break;
                case 3:
                    $month = ['07','08','09'];
                    break;
                case 4:
                    $month = ['10','11','12'];
                    break;
                default:
                    $month = [];
                    break;
            }
            foreach ($gridLists as $grid) {
                $scoreRecords = Db::name('admin_score_record')->where(['year' => $this->year,'user_id' => $grid['id']])->where('month','in',$month)->select();
                if (count($scoreRecords) != 3) {
                    Db::rollback();
                    $output->writeln('计算季度平均分错误');
                    return false;
                }

                $score = 0;
                foreach ($scoreRecords as $scoreRecord) {
                    $score += $scoreRecord['score'];
                }

                $score = bcdiv((string)$score,(string)3,2);
                $data = [
                    'user_id' => $grid['id'],
                    'year' => $this->year,
                    'quarter' => $this->quarter,
                    'average_score' => $score,
                    'create_time' => time(),
                    'update_time' => time()
                ];

                if (!Db::name('admin_score_average')->insertGetId($data)) {
                    Db::rollback();
                    $output->writeln('写入计算季度平均分错误');
                    return false;
                }
            }
            Db::commit();
            $output->writeln('季度平均分操作成功');
        }
    }
}

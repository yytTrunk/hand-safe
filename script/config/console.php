<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        'check-exits-record' => 'app\command\CheckExitsRecord',
        'check-exits-day-record' => 'app\command\CheckExitsDayRecord',
        'repair-day-record' => 'app\command\RepairDayRecord',
        'check-order-ending-day' => 'app\command\CheckOrderEndingDay',
        'record-score-by-month' => 'app\command\RecordScoreByMonth',
    ],
];

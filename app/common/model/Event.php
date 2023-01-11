<?php
namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Event extends Model
{
    const STATE_NORMAL = 1;
    const STATE_PENDING = 2;
    const STATE_CANCEL = 3;
    const STATE_ENDING = 4;
}

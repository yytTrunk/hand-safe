<?php
namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Record extends Model
{
    public function user()
    {
        return $this->belongsTo('user');
    }
}

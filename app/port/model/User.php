<?php
namespace app\port\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class User extends Model
{
    public function role()
    {
       return $this->belongsTo('role');
    }
}

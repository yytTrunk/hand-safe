<?php
namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Project extends Model
{
    public function workOrder()
    {
        return $this->hasMany('work_order');
    }

    public function projectUser()
    {
        return $this->hasMany('project_user');
    }
}

<?php


namespace app\common\validate;


use think\Validate;

class Crontab extends Validate
{
    protected $rule = [
        'cmd|命令'        => 'require',
        'time|任务时间'    => 'require'
    ];


    # 添加分类 场景验证
    public function sceneAdd() {
        return $this->only(['cmd', 'time']);
    }

}
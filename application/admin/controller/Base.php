<?php

namespace app\admin\controller;

use think\Controller;

class Base extends Controller
{
    # 检测是否已登录
    public function initialize()
    {
        # session中没有id则进入登录页
        if (!session('?admin.id')) {
            $this->redirect('admin/index/login');
        }
    }
}

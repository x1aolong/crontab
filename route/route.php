<?php

Route::group('index', function ()
{
    // 前台路由
});

Route::group('admin', function ()
{
    // 后台路由
    Route::rule('/', 'admin/index/login', 'get|post');                      // Login页
    Route::rule('index', 'admin/home/index', 'get');                        // 后台首页
    Route::rule('signout', 'admin/home/signout', 'post');                   // 退出账户
    Route::rule('crontabadd', 'admin/crontab/add', 'get|post');             // 添加定时任务
    Route::rule('crontablist/[:keyword]', 'admin/crontab/list', 'get|post');   // 定时任务列表
    Route::rule('api', 'admin/api/getAllCrontabList', 'get');               // 接口获取所有db中的定时任务
    Route::rule('exec', 'admin/api/exec', 'get');                           // 脚本文件

    Route::rule('crontabedit/[:id]', 'admin/crontab/edit', 'get|post'); // todo 编辑退出账户
    Route::rule('crontabdel', 'admin/crontab/del', 'post');             // todo 删除退出账户

    Route::rule('register', 'admin/index/register', 'get|post');        // 未测 管理员注册
    Route::rule('forget', 'admin/index/forget', 'get|post');            // 未测 忘记管理员密码
    Route::rule('reset', 'admin/index/reset', 'post');                  // 未测 重置管理员密码


});
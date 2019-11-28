<?php

Route::group('index', function ()
{
    // 前台路由
});

Route::group('admin', function ()
{
    // 后台路由
    //Route::rule('index', 'admin/home/index', 'get');                                    // 后台首页
    Route::rule('/', 'admin/index/login', 'get|post');                                  // Login页
    Route::rule('signout', 'admin/home/signout', 'post');                               // 账号登出
    Route::rule('crontabadd', 'admin/crontab/add', 'get|post');                         // 添加定时任务
    Route::rule('crontablist/[:keyword]', 'admin/crontab/list', 'get|post');            // 定时任务列表
    Route::rule('crontabedit/[:id]', 'admin/crontab/edit', 'get|post');                 // 编辑任务
    Route::rule('crontabdel', 'admin/crontab/del', 'post');                             // 暂停任务
    Route::rule('crontabstop/[:keyword]', 'admin/crontab/stop_list', 'get|post');       // 暂停任务列表

    Route::rule('register', 'admin/index/register', 'get|post');        // 未测 管理员注册
    Route::rule('forget', 'admin/index/forget', 'get|post');            // 未测 忘记管理员密码
    Route::rule('reset', 'admin/index/reset', 'post');                  // 未测 重置管理员密码


});
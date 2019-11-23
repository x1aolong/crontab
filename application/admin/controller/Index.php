<?php

namespace app\admin\controller;

use think\facade\Config;

class Index extends Base
{
    # 初始判断是否存在sessionId, 有则直接进入首页
    public function initialize()
    {
        if (session('?admin.id')) {
            $this->redirect('admin/home/index');
        }
    }

    # 后台登录
    public function login() {
        # 提交数据是ajax的方式，进行数据接收
        if(request()->isAjax()){
            $data = [
                'username' => input('post.username'),
                'password' => input('post.password')
            ];
            $result = model('Admin')->login($data);
            # 判断返回结果
            if ($result == 1) {
                $this->success('登录成功', 'admin/crontab/list');
            } else {
                $this->error($result);
            }
        }
        return view();
    }

    # 后台注册
    public function register() {
        if(request()->isAjax()){
            $data = [
                'username'  => input('post.username'),
                'password'  => input('post.password'),
                'conpass'   => input('post.conpass'),
                'nickname'  => input('post.nickname'),
                'email'     => input('post.email')
            ];
            $result = model('Admin')->register($data);
            # 判断返回结果
            if ($result == 1) {
                $this->success('注册成功', 'admin/index/login');
            } else {
                $this->error($result);
            }
        }
        return view();
    }

    # 忘记密码 发送验证码
    public function forget() {
        # 是ajax请求时接受数据
        if(request()->isAjax()) {
            # 接收email数据
            $data = [
                'email' => input('post.email')
            ];
            # 调用模型的忘记密码func
            $check = model('Admin')->forget($data);
            #
            if(is_string($check)) {
                $this->error($check);
            } else {
                # 生成验证码
                $code = mt_rand(000000, 999999);
                # 验证码存入session
                session('code', $code);
                // session('createCodeTime', time());
                # todo session 是否需要被销毁 同一时间段内请求次数是否需要限制（1min/1次）
                # 发送邮件
                $result = sendEmail($data['email'], Config::get('emailGeneral.ResetTitle'), Config::get('emailGeneral.ResetBody'). $code);
                # 根据邮件发送结果进行提示
                if ($result) {
                    $this->success('验证码已发送');
                } else {
                    $this->error('验证码发送失败');
                }
            }
        }
        return view();
    }

    # 忘记密码 重置密码
    public function reset() {
        if (request()->isAjax()) {
            $data = [
                'code' => input('post.code'),
                'email' => input('post.email')
            ];
            # 验证code真实性
            if ($data['code'] != session('code')) {
                $this->error('验证码不合法');
            }
            $check = model('Admin')->reset($data);
            if ($check == 1) {
                $this->success('新的密码已发送至您的邮箱,请查看', 'admin/index/login');
            } else {
                $this->error('重置密码失败');
            }
        }
    }
}

<?php


namespace app\common\validate;


use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'username|管理员账户' => 'require',
        'password|密码' => 'require',
        'conpass|确认密码' => 'require|confirm:password',
        'nickname|昵称' => 'require',
        'email|邮箱' => 'require|email',
        'code|验证码' => 'require|max:6'
    ];

    # 登录场景 验证
    public function sceneLogin() {
        # 验证对应的字段
        return $this->only(['username', 'password']);
    }

    # 注册场景 验证
    public function sceneRegister() {
        return $this->only(['username', 'password', 'conpass', 'nickname', 'email'])
                ->append('username', 'unique:admin|min:8')
                ->append('email', 'unique:admin')
                ->append('nickname', 'unique:admin')
                ->append('password', 'min:6');
    }

    # 忘记密码 验证
    public function sceneForget() {
        return $this->only(['email']);
    }

    # 重置密码 验证
    public function sceneReset() {
        return $this->only(['code']);
    }

    # 更新信息 验证
    public function sceneEdit()
    {
        return $this->only(['nickname', 'email'])
            ->append('email', 'unique:admin')
            ->append('nickname', 'unique:admin');
    }

    # 添加信息 验证
    public function sceneAdd()
    {
        return $this->only(['username', 'password', 'conpass', 'nickname', 'email'])
            ->append('username', 'unique:admin|min:8')
            ->append('email', 'unique:admin')
            ->append('nickname', 'unique:admin')
            ->append('password', 'min:6');
    }

}
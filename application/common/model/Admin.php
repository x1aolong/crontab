<?php

namespace app\common\model;

use think\facade\Config;
use think\Model;
use think\model\concern\SoftDelete;

class Admin extends Model
{
    # 软删除
    use SoftDelete;

    # 登录验证
    public function login($data) {
        # 调用框架内部验证器
        $validate = new \app\common\validate\Admin();
        if (!$validate->scene('login')->check($data)) {
            # 如果验证不通过，返回错误信息
            return $validate->getError();
        }
        # 验证通过去查库
        $result = $this->where($data)->find();
        # 检查账户状态
        if ($result != null && $result['status'] != '1') {
            # 查到该用户但账号被ban的情况
            return '此账户被禁用';
        } elseif ( $result == null ) {
            # 查不到该用户的情况
            return '账户名或者密码错误';
        } else {
            # 将用户信息存储与session中
            $sessionData = [
                'id' => $result['id'],
                'nickname' => $result['nickname'],
                'is_super' => $result['is_super'],
                'email' => $result['email'],
            ];
            session('admin', $sessionData);
            # 除开上述两种情况
            $status = $result ? 1 : 0;
            return $status;
        }
    }

    # 注册验证
    public function register($data) {
        $validate = new \app\common\validate\Admin();
        if (!$validate->scene('register')->check($data)) {
            # 如果验证不通过，返回错误信息
            return $validate->getError();
        }
        # 验证通过去插库
        $result = $this->allowField(true)->save($data);
        if ($result) {
            # 注册成功 发送邮件
            sendEmail($data['email'], Config::get('emailGeneral.EmailTitle'), Config::get('emailGeneral.EmailContent'). "<br/>" . "<a>" . Config::get('emailGeneral.EmailLink') . "</a>");
            return 1;
        } else {
            return '注册失败!';
        }
    }

    # 忘记密码
    public function forget($data) {
        $validate = new \app\common\validate\Admin();
        if (!$validate->scene('forget')->check($data)) {
            return $validate->getError();
        }
        $result = $this->where(['email'=>$data['email']])->find();
        if ($result) {
            return $result;
        } else {
            return '您输入的邮箱未注册';
        }
    }

    # 重置密码
    public function reset($data) {
        $validate = new \app\common\validate\Admin();
        if (!$validate->scene('reset')->check($data)) {
            return $validate->getError();
        }
        # 这里使用先查询后更新操作, 手册中说明这种方式最佳
        $adminInfo = $this->where(['email' => $data['email']])->find();
        # 进行更新密码操作
        $newPass = mt_rand(000000, 999999);
        $adminInfo->password = $newPass;
        $result = $adminInfo->save();
        if ($result) {
            # 更新成功 发邮件告知新密码
            $content = $adminInfo['username'].'您的管理员账户密码重置成功! 新的密码为: '. $newPass;
            sendEmail($data['email'], Config::get('emailGeneral.ResetSuccessTitle'), $content);
            return 1;
        } else {
            return '重置密码失败';
        }
    }

    # 编辑Admin信息
    public function edit($data)
    {
        $validate = new \app\common\validate\Admin();
        if (!$validate->scene('edit')->check($data)) {
            return $validate->getError();
        }

        if ($data['status'] == null && $data['is_super'] == null)
        {
            unset($data['status'], $data['is_super']);
        }

        if ($data['username'] == null)
        {
            unset($data['username']);
        }

        $adminInfo = $this->find($data['id']);
        $res = $adminInfo->allowField(true)->save($data);
        # todo 缺少更新完nickname直接刷新session功能, 需要退出重新登录才能生效
        if ($res) {
            return 1;
        } else {
            return '更新失败';
        }
    }

    # 添加Admin信息
    public function add($data)
    {
        $validate = new \app\common\validate\Admin();
        if (!$validate->scene('add')->check($data)) {
            return $validate->getError();
        }
        $result = $this->allowField(true)->save($data);
        if ($result) {
            # 注册成功 发送邮件
            sendEmail($data['email'], Config::get('emailGeneral.EmailTitle'), Config::get('emailGeneral.EmailContent'). "<br/>" . "<a>" . Config::get('emailGeneral.EmailLink') . "</a>");
            return 1;
        } else {
            return '注册失败!';
        }
    }

    // todo pwd md5();
# CLASS IS END
}

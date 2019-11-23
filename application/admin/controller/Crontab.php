<?php

namespace app\admin\controller;


use think\facade\Config;

class Crontab extends Base
{
    public function list ()
    {
        $crontabList = model('Crontab')->order('run_time', 'asc')->paginate(10);
        $viewData = [
            'crontabList' => $crontabList
        ];
        $this->assign($viewData);
        return view();
    }

    public function checkAndSave($data, $id = 0)
    {
        $command = $data['command'];
        $info    = $data['info'];
        $lock    = $data['lock'];

        // 验证时间格式正确性
        $slices = preg_split("/[\s]+/", $command, 6);
        if (count($slices) !== 6) {
            $this->error('命令参数缺失');
        } else {
            $file = array_pop($slices);
            $filetype = explode('.', $file); // 截取命令判断类型
        }
        // 检测时间是否合法
        $time = implode(' ', $slices);
        if (!preg_match(Config::get('crontabGeneral.Preg'), trim($time))) {
            $this->error(Config::get('crontabGeneral.TimeError'));
        }
        $parameter = '';
        // php文件处理
        if (strstr($filetype[1], 'php'))
        {
            // 判断这个文件是否存在于服务器上 path => /data/wwwroot/spider/yyjg_php/scr/xxx.php
            if (Config::get('crontabGeneral.projectLocation') != 'local') {
                $file_existence = file_exists(Config::get('crontabGeneral.PhpFiles') . $filetype[0] . '.php');
                if (!$file_existence) {
                    $this->error('脚本文件' . $filetype[0] . '.php不存在');
                }
            }
            $ext = '.php';
            if (strlen($filetype[1]) == 3) {
                $insertStr = $time . Config::get('crontabGeneral.PhpEnablePath') . $filetype[0] . '.php'; // 不带参数的php脚本
            } elseif(strlen($filetype[1]) > 3) {
                $res = explode(' ', $filetype[1]); // 携带参数 截取
                $parameter = $res[1]; // 参数
                $insertStr = $time . Config::get('crontabGeneral.PhpEnablePath') . $filetype[0] . '.php ' . $parameter;
            } else {
                $this->error(Config::get('crontabGeneral.FileError'));
            }
        }
        // python文件处理
        if (strstr($filetype[1], 'py'))
        {
            // 判断这个文件是否存在于服务器上 path => /data/wwwroot/spider/xxx.py
            if (Config::get('crontabGeneral.projectLocation') != 'local') {
                $file_existence = file_exists('/data/wwwroot/spider/'.$filetype[0].'.py');
                if (!$file_existence) {
                    $this->error('脚本文件'.$filetype[0].'.py不存在');
                }
            }
            $ext = '.py';
            if (strlen($filetype[1]) == 2) {
                $insertStr = $time . Config::get('crontabGeneral.PythonEnablePath') . $filetype[0] . '.py';
            } elseif(strlen($filetype[1]) > 2) {
                $res = explode(' ', $filetype[1]);
                $parameter = $res[1];
                $insertStr = $time . Config::get('crontabGeneral.PythonEnablePath') . $filetype[0] . '.py ' . $parameter;
            } else {
                $this->error(Config::get('crontabGeneral.FileError'));
            }
        }

        // 检查info
        if ($info == '') {
            $this->error(Config::get('crontabGeneral.InfoError'));
        }

        // 检测锁状态
        if ($lock == 1) // 有锁操作
        {
            // */1 * * * * /usr/bin/flock -xn /root/addtype.lock -c "cd /data/wwwroot/spider/yyjh_php/scr&&/usr/bin/php ./addreleasetype.php"
            $needCutStr         = $insertStr;
            $getCommandString   = explode(' ', $needCutStr, 6);
            $fileAndCommand     = array_pop($getCommandString);
            $insertStr          =  $time . Config::get('crontabGeneral.setLockCommand') . $filetype[0] . Config::get('crontabGeneral.setLockCommandLast') . $fileAndCommand . Config::get('crontabGeneral.setLockCommandOver');
        }
        // todo shell脚本暂时不考虑处理机制

        $insertData = array (
            'cmd'       => $insertStr,
            'time'      => $time,
            'ip'        => $_SERVER['SERVER_ADDR'],
            'shell_file'=> $filetype[0].$ext,
            'parameter' => $parameter,
            'info'      => $info,
            'is_lock'   => $lock
        );
        if ($id == 0) {
            // add
            $result = model('Crontab')->allowField(true)->save($insertData);
        } else {
            // edit
            $crontabInfo = model('Crontab')->find($id);
            // 如暂停数据被编辑更新则直接变为任务启动状态
            if ($crontabInfo['status'] == 1) {
                $newStatus = ['status' => '0'];
                $editData = array_merge($insertData, $newStatus);
            } else {
                $editData = $insertData;
            }
            $result = $crontabInfo->allowField(true)->save($editData);
        }
        return $result;
    }

    public function add ()
    {
        if (request()->isAjax()) {
            $command = [
                'command' => trim(input('post.command')),
                'info' => trim(input('post.info')),
                'lock' => input('post.lock')
            ];
            $result  = $this->checkAndSave($command);
            if ($result) {
                $this->success(Config::get('crontabGeneral.addSuccessMsg'), 'admin/crontab/list');
            } else {
                $this->error(Config::get('crontabGeneral.addErrorMsg'));
            }
        }
        return view();
    }

    public function edit ()
    {
        $crontabInfo = model('Crontab')->field(['id', 'time', 'shell_file', 'parameter', 'status', 'is_lock', 'info'])->where(['id' => input('id')])->find();
        $viewData = [
            'crontabInfo' => $crontabInfo
        ];
        $this->assign($viewData);

        if (request()->isAjax()) {
            $command = [
                'command' => trim(input('post.command')),
                'info' => trim(input('post.info')),
                'lock' => input('post.lock')
            ];
            $result = $this->checkAndSave($command, input('post.id'));
            // 如果是暂停任务 更新后变为启用状态
            if ($result) {
                $this->success(Config::get('crontabGeneral.editSuccessMsg'), 'admin/crontab/list');
            } else {
                $this->error(Config::get('crontabGeneral.editErrorMsg'));
            }
        }
        return view();
    }

    // 暂停任务
    public function del()
    {
        if (request()->isAjax())
        {
            $crontabInfo = model('Crontab')->find(input('post.id'));
            if ($crontabInfo['status'] == 1){
                $data = ['status' => 0];
            } else {
                $data = ['status' => 1];
            }
            $res = $crontabInfo->save($data);
            if ($res) {
                $this->success('任务状态变更成功', 'admin/crontab/list');
            } else {
                $this->error('任务状态变更成功失败');
            }
        }
    }

    // 搜索
    public function search()
    {
        if (request()->isAjax()){
            $keyword = input('post.info');
            $crontabList = model('Crontab')->where('info', 'like', '%'.$keyword.'%')->order('run_time', 'asc')->paginate(10);
            $viewData = [
                'crontabList' => $crontabList
            ];
            $this->assign($viewData);
        }

    }


}

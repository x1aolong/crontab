<?php

namespace app\admin\controller;


use think\facade\Config;

class Crontab extends Base
{
    public function list ()
    {
        $keyword = input('keyword');

        if ($keyword == '') {
            $crontabList = model('Crontab')->where(['status' => 0])->order('run_time', 'asc')->paginate(10);
            $viewData = [
                'crontabList' => $crontabList,
                'normal' => 0
            ];
        } else {
            $searchTableList = model('Crontab')->where('info', 'like', '%'.$keyword.'%')->where(['status' => 0])->order('run_time', 'asc')->paginate(100);
            $viewData = [
                'crontabList' => $searchTableList,
                'normal' => 1
            ];
        }
        $this->assign($viewData);
        return view();
    }

    public function stop_list(){
        $keyword = input('keyword');

        if ($keyword == '') {
            $crontabList = model('Crontab')->where(['status' => 1])->order('run_time', 'asc')->paginate(10);
            $viewData = [
                'crontabList' => $crontabList,
                'normal' => 0
            ];
        } else {
            $searchTableList = model('Crontab')->where('info', 'like', '%'.$keyword.'%')->where(['status' => 1])->order('run_time', 'asc')->paginate(100);
            $viewData = [
                'crontabList' => $searchTableList,
                'normal' => 1
            ];
        }
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
            $file       = array_pop($slices);
            $filetype   = explode(' ', $file);
        }
        // 检测命令参数书写是否合法
        if (count($filetype) > 3) {
            $this->error('命令不合法, 请检查是否有多余空格');
        }
        // 没写文件路径
        if ($file == $filetype[0]) {
            $this->error('请填写文件路径');
        }
        // 拆分参数
        $path           = $filetype[0];
        $file           = $filetype[1];
        $params         = count($filetype) == 3 ? $filetype[2] : '';
        $pathArr        = explode('/', $path);
        $startUpFile    = array_pop($pathArr);
        $findPath       = implode($pathArr, '/');
        $findPath       = $findPath . '/';
        $time           = implode(' ', $slices);
        $getExt         = explode('.', $file);
        $fileExt        = $getExt[1];
        // 检测时间是否合法
        if (!preg_match(Config::get('crontabGeneral.Preg'), trim($time))) {
            $this->error(Config::get('crontabGeneral.TimeError'));
        }
        // 线上环境需要检测脚本文件是否存在
        if (Config::get('crontabGeneral.projectLocation') != 'local') {
            // 存在启动文件的情况 检查路径下启动文件是否存在
            if ($startUpFile != '') {
                if (!file_exists($path)) {
                    $this->error('启动文件' . $startUpFile . '不存在');
                }
            }
            // 检查命令中脚本文件是否存在
            if (file_exists($findPath . $file)){
                $this->error('脚本文件' . $file . '不存在');
            }
        }
        // 获取启动文件的后缀名
        if ($startUpFile != '') {
            if (!strstr($startUpFile, '.')) {
                $this->error('命令路径有误');
            }
            $gettartUpFileExt = explode('.', $startUpFile);
            if(count($gettartUpFileExt) == 1){
                $this->error(Config::get('路径缺失'));
            }
            $startUpFileExt   = $gettartUpFileExt[1];
        }
        // 根据文件类型进行数据处理
        if ($fileExt == 'php') {
            // 针对shell文件启动php脚本的情况
            if ($startUpFile != '') {
                // 带启动文件的命令
                if ($startUpFileExt == 'sh') {
                    $findPath = $path;
                    $insertStr = $time . ' /bin/bash ' . $findPath . ' ' . $file . ' ' . $params;
                } else {
                    $this->error('启动器文件只支持shell文件');
                }
            } else {
                // 检查命令带参数情况
                if ($params != '') {
                    $insertStr = $time . ' cd ' . $findPath . '&&/usr/bin/php ' . $file . ' ' . $params;
                } else {
                    $insertStr = $time . ' cd ' . $findPath . '&&/usr/bin/php ' . $file;
                }
            }
        } else if ($fileExt == 'py') {
            // python不存在shell启动文件的情况
            if ($params != '') {
                $insertStr = $time . ' cd ' . $findPath . '&&/usr/bin/python ' . $file . ' ' . $params;
            } else {
                $insertStr = $time . ' cd ' . $findPath . '&&/usr/bin/python ' . $file;
            }
        } else {
            $this->error('脚本类型不支持');
        }
        // 检查info信息
        if ($info == '') {
            $this->error(Config::get('crontabGeneral.InfoError'));
        }
        // 文件加锁操作
        if ($lock == 1) {
            // */1 * * * * /usr/bin/flock -xn /root/addtype.lock -c "cd /data/wwwroot/spider/yyjh_php/scr&&/usr/bin/php ./addreleasetype.php"
            $lockNameArr = explode('.', $file);
            $lockNameStr = $lockNameArr[0];
            $ext = $fileExt == 'py' ? 'python' : 'php';
            $params = $params == '' ? '' : ' '.$params;
            $insertStr = $time . ' /usr.bin/flock -xn /root/' . $lockNameStr.'.lock -c "cd ' . $findPath . '&&/usr/bin/' . $ext . ' ' . $file . $params .'"';
        }
        // 封装入库数据
        $insertData = array (
            'cmd'       => $insertStr,
            'time'      => $time,
            'ip'        => $_SERVER['SERVER_ADDR'],
            'shell_file'=> $file,
            'parameter' => $params,
            'info'      => $info,
            'is_lock'   => $lock
        );
        // 根据参数id存在判断是否为update操作
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
        $crontabInfo = model('Crontab')->field(['id', 'cmd', 'time', 'shell_file', 'parameter', 'status', 'is_lock', 'info'])->where(['id' => input('id')])->find();
        $slices     = preg_split("/[\s]+/", $crontabInfo['cmd'], 6);
        $file       = array_pop($slices);
        if ($crontabInfo['is_lock'] == 1) {
            // 带锁文件
            $fileStr  = explode(' ', $file);
            $fileName = array_pop($fileStr);
            $filePath = array_pop($fileStr);
            $filetype = explode('&&', $filePath);
            $quotation= explode('"',$fileName); // 切除双引号
            $fileName = $quotation[0];
            $crontabInfo['cmd'] = implode(' ', $slices) . ' ' . $filetype[0] . ' ' . $fileName;
        } else {
            // 无锁文件
            $filetype   = explode('&&', $file);
            $path       = explode(' ', $filetype[0]);
            $fileStr    = explode(' ', $filetype[1]);
            if ($crontabInfo['parameter'] == '') {
                // 不带参 不带锁
                $crontabInfo['cmd'] = implode(' ', $slices) . ' ' . $path[1] . ' ' . $fileStr[1];
            } else {
                // 带参不带锁
                $crontabInfo['cmd'] = implode(' ', $slices) . ' ' . $path[1] . ' ' . $fileStr[1] . ' ' . $crontabInfo['parameter'];
            }
        }
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

    // 搜索 todo 待删除
    public function search()
    {
        if (request()->isAjax()){
            $keyword = input('post.info');
            // paginate(10);
            $searchTableList = model('Crontab')->where('info', 'like', '%'.$keyword.'%')->order('run_time', 'asc')->paginate(10);
            if ($searchTableList){
                $res = ["code" => 1, "searchTableList" => $searchTableList ];
                return json($res);
            }
        }
    }


}

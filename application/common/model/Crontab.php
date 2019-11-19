<?php

namespace app\common\model;

use think\Model;

class Crontab extends Model
{
    // 待作废
    public function add($data)
    {
        //$originData = $data;
        $cmd = array_pop($data);
        // 检查时间控制是否缺失
        foreach ($data as $k => $v) {
            if (strlen(trim($v)) == 0) {
                return '时间数据不合法';
            } else {
                $timeArr[$k] = trim($v);
            }
        }
        // todo 入库数据合法性 min 0-59什么的
        // 合法的时间控制数据格式化
        $time = implode(' ', $timeArr);

        if(!preg_match('/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i',trim($time))){
            return '时间数据不合法';
        }
        // 服务器php文件路径 启用脚本必须 ( 暂仅支持php脚本
        //$phpBinPath = ' /usr/local/bin/php ';
        $phpBinPath = '&&/usr/bin/php ';
        $localPath = ' cd /Applications/MAMP/htdocs/crontab/public/static/admin/shellfiles';
        $serverPath = ' cd /data/wwwroot/crontab/public/static/admin/shellfiles';

        // format data
        $saveData = [
            'cmd'   => $time . $serverPath.$phpBinPath . $cmd,
            'time'  => $time,
            'ip'    => $_SERVER['SERVER_ADDR']
        ];
        // 启用验证器
        $validate = new \app\common\validate\Crontab();
        if (!$validate->scene('add')->check($saveData))
        {
            return $validate->getError();
        }
        // db save
        $res = $this->allowField(true)->save($saveData);
        if ($res) {
            // chmod
            //recurDir('/data/wwwroot/crontab/public/static/admin/shellfiles',777);
            return 1;
        } else {
            return '定时任务创建失败';
        }
    }





    public function edit($data)
    {
        $validate = new \app\common\validate\Contract();
        if (!$validate->scene('edit')->check($data))
        {
            return $validate->getError();
        }
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);

        if ($data['start_time'] >= $data['end_time'])
        {
            return '合同结束时间必须晚于合同开始时间';
        }
        // DBsearch -> DBsave
        $contractInfo = $this->find($data['id']);
        $res = $contractInfo->allowField(true)->save($data);
        if ($res) {
            return 1;
        } else {
            return '更新合同失败';
        }
    }

}

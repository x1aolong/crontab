<?php

namespace app\admin\controller;

use think\Controller;

class Api extends Controller
{
    # Todo 作废

    // 获取所有的定时任务
    public function getAllCrontabList()
    {
       $crontabList = model('Crontab')->column('cmd');
       return $crontabList;
    }

    // 对比时间 如任务时间与now一致 执行任务
    public function exec()
    {
        $crontabList = $this->getAllCrontabList();
        $now = $_SERVER['REQUEST_TIME'];
        $i = 0;

        foreach ( $crontabList as $cron ) {
            $slices = preg_split("/[\s]+/", $cron, 6);
            if( count($slices) !== 6 ) {
                continue;
            }
            $cmd       = array_pop($slices);
            $cron_time = implode(' ', $slices);
            $crontabClass = new \Crontab();
            $next_time = $crontabClass::parse($cron_time, $now);
            //var_dump(date("Y-m-d H:i", $next_time));exit;

            if ( $next_time !== $now ) {
                continue;
            } else {
                # todo执行命令的脚本文件 ()
                exec($cmd, $result, $status);
                # 根据返回的状态值输出
                if ($status) {
                    echo date('Y-m-d H:i:s', time()).' 命令 --> '.$cmd.' 执行失败'.'['.$i.']'."\n";
                } else {
                    echo date('Y-m-d H:i:s', time()).' 命令 --> '.$cmd.' 执行成功'.'['.$i.']'."\n";
                }
                /*
                    $pid = pcntl_fork();
                    if ($pid == -1) {
                        die('could not fork');
                    } else if ($pid) {
                        // we are the parent
                        pcntl_wait($status, WNOHANG); //Protect against Zombie children
                    } else {
                          // we are the child
                        `$cmd`;
                        exit;
                    }
                */
            }
            $i++;
        }
    }


}

<?php

/** 此文件为定时任务的一些常用参数 **/

return [
    'projectLocation'   => 'local',     // 本地开发打开我
//    'projectLocation'   => 'online',  // 线上打开我
    'Preg'              => '/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i',
    'TimeError'         => '时间数据不合法',
    'FileError'         => '上传脚本文件不符合规范，目前暂支持.py和.php文件脚本',
    'PhpFiles'          => '/data/wwwroot/spider/yyjh_php/scr/',
    'PhpEnablePath'     => ' cd /data/wwwroot/spider/yyjh_php/scr&&/usr/bin/php ',
    'PythonEnablePath'  => ' cd /data/wwwroot/spider/&&/usr/bin/python ',
    'addSuccessMsg'     => '定时任务添加成功',
    'addErrorMsg'       => '定时任务添加失败',
    'editSuccessMsg'    => '定时任务更新成功',
    'editErrorMsg'      => '定时任务更新失败',
    'InfoError'         => '为便于查看，建议您填写任务描述',
    'setLockCommand'    => ' /usr/bin/flock -xn /root/',
    'setLockCommandLast'=> '.lock -c "',
    'setLockCommandOver'=> '"',
    'unsetLockCommand'  => ''
];
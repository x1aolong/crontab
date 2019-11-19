![](https://box.kancloud.cn/5a0aaa69a5ff42657b5c4715f3d49221) 

===============

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/top-think/framework/badges/quality-score.png?b=5.1)](https://scrutinizer-ci.com/g/top-think/framework/?branch=5.1)
[![Build Status](https://travis-ci.org/top-think/framework.svg?branch=master)](https://travis-ci.org/top-think/framework)
[![Total Downloads](https://poser.pugx.org/topthink/framework/downloads)](https://packagist.org/packages/topthink/framework)
[![Latest Stable Version](https://poser.pugx.org/topthink/framework/v/stable)](https://packagist.org/packages/topthink/framework)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D5.6-8892BF.svg)](http://www.php.net/)
[![License](https://poser.pugx.org/topthink/framework/license)](https://packagist.org/packages/topthink/framework)

ThinkPHP5.1对底层架构做了进一步的改进，减少依赖，其主要特性包括：

 + 采用容器统一管理对象
 + 支持Facade
 + 注解路由支持
 + 路由跨域请求支持
 + 配置和路由目录独立
 + 取消系统常量
 + 助手函数增强
 + 类库别名机制
 + 增加条件查询
 + 改进查询机制
 + 配置采用二级
 + 依赖注入完善
 + 支持`PSR-3`日志规范
 + 中间件支持（V5.1.6+）
 + Swoole/Workerman支持（V5.1.18+）


> ThinkPHP5的运行环境要求PHP5.6以上。

## 安装

+ 配置 host文件 /etc/hosts 
~~~
    127.0.0.1   dev.crontab.com
~~~
+ 配置 mysql数据库文件
~~~
    将 .sql 文件导入数据库中
~~~
+ 使用 composer安装必要组建
~~~
    composer install
~~~
+ 配置 nginx
~~~
    server {
        listen  80;
        server_name dev.crontab.com;    # 虚拟域名
        index   index.php index.html;
        root   /data/wwwroot/crontab/public;    # 项目路径

        access_log  /data/logs/nginx/crontab_access.log main;
        error_log   /data/logs/nginx/crontab_error.log;

            location / {

             if (!-e $request_filename){
                #rewrite ^/(.*) /index.php?r=$1 last;   # yii2
                rewrite ^(.*)$ /index.php?s=/$1 last;   # tp
            }
        }

        location ~ /\.(svn|git|hg|ht|bzr|cvs)(/|$) {
             return 403;
        }

        location ~ \.php$ {
                try_files  $uri = 404;
                include  fastcgi_params;
                fastcgi_pass   h5web;
                fastcgi_index  index.php;
        }
}
~~~
+ 控制文件
~~~
    vendor/autorun/crontab_active.php
~~~

## 在线手册

+ [完全开发手册](https://www.kancloud.cn/manual/thinkphp5_1/content)
+ [升级指导](https://www.kancloud.cn/manual/thinkphp5_1/354155) 



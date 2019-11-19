/*
 Navicat MySQL Data Transfer

 Source Server         : 127.0.0.1_3306
 Source Server Type    : MySQL
 Source Server Version : 50725
 Source Host           : 127.0.0.1:3306
 Source Schema         : crontab_task

 Target Server Type    : MySQL
 Target Server Version : 50725
 File Encoding         : 65001

 Date: 19/11/2019 13:53:35
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for yx_admin
-- ----------------------------
DROP TABLE IF EXISTS `yx_admin`;
CREATE TABLE `yx_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '管理员账户',
  `password` varchar(50) NOT NULL,
  `nickname` varchar(50) NOT NULL COMMENT '昵称',
  `email` varchar(50) NOT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '0=disable | 1=enable ',
  `is_super` enum('0','1') NOT NULL DEFAULT '0' COMMENT '0=普通管理员 | 1=超管',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yx_crontab
-- ----------------------------
DROP TABLE IF EXISTS `yx_crontab`;
CREATE TABLE `yx_crontab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cmd` varchar(255) DEFAULT NULL COMMENT '命令详情',
  `time` varchar(255) DEFAULT NULL COMMENT '命令执行时间',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `status` enum('1','0') DEFAULT '0' COMMENT '0=执行任务/1=暂停任务',
  `ip` char(20) DEFAULT NULL COMMENT 'ip地址',
  `shell_file` varchar(255) DEFAULT NULL COMMENT '脚本文件名',
  `parameter` varchar(50) DEFAULT NULL COMMENT '脚本参数',
  `is_lock` enum('1','0') DEFAULT '0' COMMENT '0=无锁/1=有锁',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yx_result
-- ----------------------------
DROP TABLE IF EXISTS `yx_result`;
CREATE TABLE `yx_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cmd` varchar(255) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;

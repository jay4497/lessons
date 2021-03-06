DROP TABLE IF EXISTS `apl_user`;
CREATE TABLE `apl_user` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `user_name` VARCHAR(100) NOT NULL COMMENT '用户名',
  `password` VARCHAR(255) NOT NULL COMMENT '密码',
  `avatar` VARCHAR(255) NULL DEFAULT '' COMMENT '头像',
  `email` VARCHAR(255) NULL DEFAULT '' COMMENT '电子邮箱',
  `phone` VARCHAR(20) NULL DEFAULT '' COMMENT '手机号',
  `type` TINYINT(1) DEFAULT 0 COMMENT '用户类型。0 普通用户，1 管理员',
  `status` ENUM('normal', 'forbiden') DEFAULT 'normal' COMMENT '状态',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX (`user_name`)
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COMMENT '用户表';

INSERT INTO `apl_user` VALUES (1, 'apple', '$2y$10$8P5p65zWpFz.cMgdehiEK.C5YJ8qompeZia6ZrJoP92lc4yWngj4q', '', '', '', 1, 'normal', '2020-04-01 10:30:22', '2020-04-01 10:30:22');

DROP TABLE IF EXISTS `apl_group`;
CREATE TABLE `apl_group` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL COMMENT '名称',
  `display_name` VARCHAR(150) NULL DEFAULT '' COMMENT '显示名称',
  `description` VARCHAR(255) NULL DEFAULT '' COMMENT '描述',
  `icon` VARCHAR(255) NULL DEFAULT '' COMMENT '标识图片',
  `pid` INT NULL DEFAULT 0 COMMENT '父级id',
  `status` ENUM('normal', 'comming', 'abandon') DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COMMENT '权限组别表';

DROP TABLE IF EXISTS `apl_group_user`;
CREATE TABLE `apl_group_user` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT NOT NULL COMMENT '用户id',
  `group_id` INT NOT NULL COMMENT '组别id',
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX (`user_id`),
  INDEX (`group_id`)
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COMMENT '用户与权限组别枢纽表';

DROP TABLE IF EXISTS `apl_video`;
CREATE TABLE `apl_video` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `group_id` INT NOT NULL COMMENT '组别id',
  `name` VARCHAR(100) NOT NULL COMMENT '名称',
  `path` TEXT NULL COMMENT '地址',
  `ext` VARCHAR(10) NULL DEFAULT '' COMMENT '扩展名',
  `filesize` VARCHAR(20) NULL DEFAULT 0 COMMENT '文件大小 byte',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX (`group_id`)
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COMMENT '视频表';

ALTER TABLE `apl_group`
  ADD COLUMN `uqid` VARCHAR(50) NOT NULL COMMENT '唯一id' AFTER `id`,
  MODIFY `pid` VARCHAR(50) NULL DEFAULT 0 COMMENT '父级uqid';

DROP TABLE IF EXISTS `apl_options`;
CREATE TABLE `apl_options` (
  `id` TINYINT(1) NOT NULL AUTO_INCREMENT,
  `site_name` VARCHAR(100) NOT NULL COMMENT '网站名称',
  `logo` TEXT NULL COMMENT '网站LOGO',
  `status` ENUM('running', 'pause', 'stop') DEFAULT 'running' COMMENT '网站状态',
  PRIMARY KEY (`id`)
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COMMENT '系统设置表';
INSERT INTO `apl_options` VALUES (1, '网站名称', '', 'running');

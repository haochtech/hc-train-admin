<?php

function upsql()
{
    if (!pdo_tableexists("xc_train_userinfo")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_userinfo` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '用户id',\r\n  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',\r\n  `nick` varchar(255) DEFAULT NULL,\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `createtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',\r\n  `shop` int(11) DEFAULT '-1' COMMENT '管理中心绑定',\r\n  `shop_id` int(11) DEFAULT NULL COMMENT '分校id',\r\n  `share` varchar(50) DEFAULT NULL COMMENT '推荐人',\r\n  `share_fee` decimal(10,2) DEFAULT '0.00' COMMENT '佣金',\r\n  `share_code` varchar(255) DEFAULT NULL COMMENT '分销二维码',\r\n  `score` int(11) DEFAULT '0' COMMENT '积分',\r\n  `team_fee` decimal(10,2) DEFAULT '0.00' COMMENT '团长佣金',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户信息';";
        pdo_run($sql);
    }
    if (!pdo_fieldexists("xc_train_video", "link")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_video") . " ADD `link` varchar(255) DEFAULT NULL COMMENT '链接'");
    }
    if (!pdo_fieldexists("xc_train_video", "vid")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_video") . " ADD `vid` varchar(50) DEFAULT NULL");
    }
    if (!pdo_fieldexists("xc_train_video", "type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_video") . " ADD `type` int(11) DEFAULT '1' COMMENT '类型'");
    }
    if (!pdo_fieldexists("xc_train_school", "sms")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_school") . " ADD `sms` varchar(50) DEFAULT NULL COMMENT '接收短信'");
    }
    if (!pdo_fieldexists("xc_train_userinfo", "shop_id")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_userinfo") . " ADD `shop_id` int(11) DEFAULT NULL COMMENT '分校id'");
    }
    if (!pdo_tableexists("xc_train_nav")) {
        $sql = "CREATE TABLE `ims_xc_train_nav` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `name` varchar(50) DEFAULT NULL COMMENT '名称',\r\n  `simg` varchar(255) DEFAULT NULL COMMENT '图片',\r\n  `link` varchar(255) DEFAULT NULL COMMENT '链接',\r\n  `sort` int(11) DEFAULT '0' COMMENT '排序',\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `createtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`sort`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='自定义导航';";
        pdo_run($sql);
    }
    if (!pdo_fieldexists("xc_train_prize", "type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_prize") . " ADD `type` int(11) DEFAULT '1'");
    }
    if (!pdo_fieldexists("xc_train_prize", "pid")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_prize") . " ADD `pid` int(11) DEFAULT NULL COMMENT '奖品id'");
    }
    if (!pdo_tableexists("xc_train_gua")) {
        $sql = "CREATE TABLE `ims_xc_train_gua` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `name` varchar(50) DEFAULT NULL COMMENT '名称',\r\n  `bimg` varchar(255) DEFAULT NULL COMMENT '图片',\r\n  `times` int(11) DEFAULT NULL COMMENT '概率',\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `createtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',\r\n  PRIMARY KEY (`id`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='奖品';";
        pdo_run($sql);
    }
    if (!pdo_fieldexists("xc_train_active", "gua_img")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_active") . " ADD `gua_img` varchar(255) DEFAULT NULL COMMENT '刮刮卡图片'");
    }
    if (!pdo_fieldexists("xc_train_active", "list")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_active") . " ADD `list` longtext COMMENT '奖品'");
    }
    if (!pdo_fieldexists("xc_train_active", "type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_active") . " ADD `type` int(11) DEFAULT '1' COMMENT '类型（1集卡2刮刮卡）'");
    }
    if (!pdo_fieldexists("xc_train_service_team", "type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_service_team") . " ADD `type` int(11) DEFAULT '1' COMMENT '类型'");
    }
    if (!pdo_fieldexists("xc_train_order", "can_use")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `can_use` int(11) DEFAULT '1' COMMENT '核销次数'");
    }
    if (!pdo_fieldexists("xc_train_order", "is_use")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `is_use` int(11) DEFAULT '0' COMMENT '已核销次数'");
    }
    if (!pdo_fieldexists("xc_train_order", "use_time")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `use_time` longtext COMMENT '核销时间'");
    }
    if (!pdo_fieldexists("xc_train_article", "link_type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_article") . " ADD `link_type` int(11) DEFAULT '1' COMMENT '模式'");
    }
    if (!pdo_fieldexists("xc_train_teacher", "content2")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_service") . " ADD `content2` longtext COMMENT '内容2'");
    }
    if (!pdo_fieldexists("xc_train_teacher", "content_type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_service") . " ADD `content_type` int(11) DEFAULT '1' COMMENT '课程模式'");
    }
    if (!pdo_fieldexists("xc_train_service", "can_use")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_service") . " ADD `can_use` int(11) DEFAULT '1' COMMENT '核销次数'");
    }
    if (!pdo_fieldexists("xc_train_service", "content2")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_service") . " ADD `content2` longtext COMMENT '内容2'");
    }
    if (!pdo_fieldexists("xc_train_service", "content_type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_service") . " ADD `content_type` int(11) DEFAULT '1' COMMENT '课程模式'");
    }
    if (!pdo_fieldexists("xc_train_discuss", "type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_discuss") . " ADD `type` int(11) DEFAULT '1' COMMENT '类型（1课程2视频）'");
    }
    if (!pdo_fieldexists("xc_train_active", "share_type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_active") . " ADD `share_type` int(11) DEFAULT '1' COMMENT '分享类型（1分享2分享点击）'");
    }
    if (!pdo_tableexists("xc_train_video")) {
        $sql = "CREATE TABLE `ims_xc_train_video` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `name` varchar(50) DEFAULT NULL COMMENT '名称',\r\n  `video` varchar(255) DEFAULT NULL COMMENT '视频',\r\n  `bimg` varchar(255) DEFAULT NULL COMMENT '封面',\r\n  `price` decimal(10,2) DEFAULT NULL COMMENT '价格',\r\n  `pid` int(11) DEFAULT NULL COMMENT '课程id',\r\n  `cid` int(11) DEFAULT NULL COMMENT '分类',\r\n  `teacher_id` int(11) DEFAULT NULL COMMENT '主讲教师',\r\n  `teacher_name` varchar(50) DEFAULT NULL COMMENT '教师姓名',\r\n  `sort` int(11) DEFAULT '0' COMMENT '排序',\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `createtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`pid`,`sort`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='视频';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_video_class")) {
        $sql = "CREATE TABLE `ims_xc_train_video_class` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `name` varchar(50) DEFAULT NULL COMMENT '名称',\r\n  `sort` int(11) DEFAULT '0' COMMENT '排序',\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `createtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`sort`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='视频分来';";
        pdo_run($sql);
    }
    if (!pdo_fieldexists("xc_train_order", "store")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `store` int(11) DEFAULT NULL COMMENT '校区'");
    }
    if (!pdo_fieldexists("xc_train_order", "content")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `content` longtext COMMENT '备注'");
    }
    if (!pdo_fieldexists("xc_train_school", "longitude")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_school") . " ADD `longitude` decimal(10,7) DEFAULT NULL COMMENT '经度'");
    }
    if (!pdo_fieldexists("xc_train_school", "latitude")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_school") . " ADD `latitude` decimal(10,7) DEFAULT NULL COMMENT '纬度'");
    }
    if (!pdo_tableexists("xc_train_login_log")) {
        $sql = "CREATE TABLE `ims_xc_train_login_log` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` varchar(255) DEFAULT NULL,\r\n  `openid` varchar(255) DEFAULT NULL COMMENT '用户id',\r\n  `plan_date` varchar(50) DEFAULT NULL COMMENT '日期',\r\n  `createtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',\r\n  PRIMARY KEY (`id`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='登录日志';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_cut")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_cut` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `pid` int(11) DEFAULT NULL COMMENT '课程id',\r\n  `mark` varchar(255) DEFAULT NULL COMMENT '标记',\r\n  `end_time` datetime DEFAULT NULL COMMENT '结束时间',\r\n  `is_member` int(11) DEFAULT '0' COMMENT '已有人数',\r\n  `member` int(11) DEFAULT NULL COMMENT '人数',\r\n  `join_member` int(11) DEFAULT '0' COMMENT '参与人数',\r\n  `price` decimal(10,2) DEFAULT NULL COMMENT '价格',\r\n  `cut_price` decimal(10,2) DEFAULT NULL COMMENT '最低价',\r\n  `max_price` decimal(10,2) DEFAULT NULL COMMENT '砍价区间',\r\n  `min_price` decimal(10,2) DEFAULT NULL COMMENT '砍价区间',\r\n  `sort` int(11) DEFAULT '0' COMMENT '排序',\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `createtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`pid`,`sort`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='砍价';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_cut_log")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_cut_log` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '用户id',\r\n  `cid` int(11) DEFAULT NULL,\r\n  `price` decimal(10,2) DEFAULT NULL COMMENT '砍去的价格',\r\n  `cut_openid` varchar(50) DEFAULT NULL COMMENT '帮砍的用户id',\r\n  `createtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`cid`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='砍价记录';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_cut_order")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_cut_order` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '用户id',\r\n  `cid` int(11) DEFAULT NULL,\r\n  `price` decimal(10,2) DEFAULT NULL COMMENT '价格',\r\n  `is_min` int(11) DEFAULT '-1' COMMENT '最低价',\r\n  `status` int(11) DEFAULT '-1' COMMENT '购买状态',\r\n  `createtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`is_min`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='砍价订单';";
        pdo_run($sql);
    }
    if (!pdo_fieldexists("xc_train_order", "cut_status")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `cut_status` int(11) DEFAULT '-1' COMMENT '砍价'");
    }
    if (!pdo_fieldexists("xc_train_service_class", "type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_service_class") . " ADD `type` int(11) DEFAULT '1' COMMENT '类型（1课程2名师）'");
    }
    if (!pdo_fieldexists("xc_train_teacher", "cid")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_teacher") . " ADD `cid` int(11) DEFAULT NULL COMMENT '分类'");
    }
    if (!pdo_tableexists("xc_train_address")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_address` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` varchar(50) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '用户id',\r\n  `name` varchar(50) DEFAULT NULL COMMENT '姓名',\r\n  `mobile` varchar(50) DEFAULT NULL COMMENT '手机号',\r\n  `sex` int(11) DEFAULT '1' COMMENT '性别',\r\n  `address` varchar(255) DEFAULT NULL COMMENT '地址',\r\n  `latitude` decimal(10,7) DEFAULT NULL COMMENT '经度',\r\n  `longitude` decimal(10,7) DEFAULT NULL COMMENT '纬度',\r\n  `content` longtext COMMENT '详情',\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `createtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`openid`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='地址';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_mall")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_mall` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `name` varchar(255) DEFAULT NULL COMMENT '名称',\r\n  `title` varchar(255) DEFAULT NULL COMMENT '副标题',\r\n  `cid` int(11) DEFAULT NULL COMMENT '分类',\r\n  `simg` varchar(255) DEFAULT NULL COMMENT '封面',\r\n  `bimg` longtext,\r\n  `price` decimal(10,2) DEFAULT NULL COMMENT '价格',\r\n  `format` longtext COMMENT '多规格',\r\n  `sold` int(11) DEFAULT '0',\r\n  `sort` int(11) DEFAULT '0' COMMENT '排序',\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `content` longtext COMMENT '详情',\r\n  `createtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',\r\n  PRIMARY KEY (`id`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商城';";
        pdo_run($sql);
    }
    if (!pdo_fieldexists("xc_train_order", "userinfo")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `userinfo` longtext COMMENT '用户信息'");
    }
    if (!pdo_fieldexists("xc_train_order", "format")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `format` varchar(255) DEFAULT NULL COMMENT '规格'");
    }
    if (!pdo_fieldexists("xc_train_order", "order_status")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `order_status` int(11) DEFAULT '-1' COMMENT '-1未发货1未收货2完成'");
    }
    if (!pdo_fieldexists("xc_train_service", "code")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_service") . " ADD `code` varchar(255) DEFAULT NULL COMMENT '二维码'");
    }
    if (!pdo_fieldexists("xc_train_order", "tui_status")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `tui_status` int(11) DEFAULT '-1' COMMENT '退款状态（-1未退款1退款）'");
    }
    if (!pdo_fieldexists("xc_train_order", "tui_content")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `tui_content` longtext COMMENT '退款原因'");
    }
    if (!pdo_fieldexists("xc_train_cut", "link")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_cut") . " ADD `link` text COMMENT '虚拟人数'");
    }
    if (!pdo_fieldexists("xc_train_mall", "type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_mall") . " ADD `type` int(11) DEFAULT '1' COMMENT '类型（1无2团购3限时抢购）'");
    }
    if (!pdo_fieldexists("xc_train_mall", "start_time")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_mall") . " ADD `start_time` varchar(50) DEFAULT NULL COMMENT '开始时间'");
    }
    if (!pdo_fieldexists("xc_train_mall", "end_time")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_mall") . " ADD `end_time` varchar(50) DEFAULT NULL COMMENT '结束时间'");
    }
    if (!pdo_fieldexists("xc_train_mall", "group_member")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_mall") . " ADD `group_member` int(11) DEFAULT NULL COMMENT '团购人数'");
    }
    if (!pdo_fieldexists("xc_train_mall", "group_fail")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_mall") . " ADD `group_fail` int(11) DEFAULT NULL COMMENT '团购失败时间'");
    }
    if (!pdo_fieldexists("xc_train_order", "mall_type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `mall_type` int(11) DEFAULT '1' COMMENT '商城订单类型（1无2团购3限时）'");
    }
    if (!pdo_fieldexists("xc_train_order", "group_id")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `group_id` int(11) DEFAULT NULL COMMENT '团购id'");
    }
    if (!pdo_fieldexists("xc_train_order", "group_status")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `group_status` int(11) DEFAULT '-1' COMMENT '团购状态（-1拼团中1成功2失败）'");
    }
    if (!pdo_tableexists("xc_train_group")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_group` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '团长',\r\n  `service` int(11) DEFAULT NULL COMMENT '产品id',\r\n  `is_member` int(11) DEFAULT '0' COMMENT '已有人数',\r\n  `member` int(11) DEFAULT NULL COMMENT '所需人数',\r\n  `createtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '开团时间',\r\n  `failtime` datetime DEFAULT NULL COMMENT '结束时间',\r\n  `status` int(11) DEFAULT '-1' COMMENT '状态（-1拼团中1拼团成功2已失败）',\r\n  `group` longtext COMMENT '团成员',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`status`,`createtime`,`failtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='团购';";
        pdo_run($sql);
    }
    if (!pdo_fieldexists("xc_train_order", "tui")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `tui` varchar(255) DEFAULT NULL COMMENT '推荐人'");
    }
    if (!pdo_fieldexists("xc_train_mall", "index")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_mall") . " ADD `index` int(11) DEFAULT '-1' COMMENT '首页显示'");
    }
    if (!pdo_fieldexists("xc_train_video", "click")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_mall") . " ADD `click` int(11) DEFAULT '0' COMMENT '人气'");
    }
    if (!pdo_fieldexists("xc_train_order", "pei_type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `pei_type` int(11) DEFAULT '1' COMMENT '配送方式（1商家配送2自提）'");
    }
    if (!pdo_fieldexists("xc_train_order", "fee")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `fee` varchar(50) DEFAULT NULL COMMENT '运费'");
    }
    if (!pdo_tableexists("xc_train_moban_user")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_moban_user` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL,\r\n  `nickname` varchar(500) DEFAULT NULL COMMENT '呢称',\r\n  `status` int(11) DEFAULT '-1' COMMENT '-1未使用  1已使用',\r\n  `createtime` int(11) DEFAULT NULL COMMENT '发布日期',\r\n  `ident` varchar(50) DEFAULT NULL COMMENT '标识',\r\n  `headimgurl` varchar(500) DEFAULT NULL COMMENT '头像',\r\n  PRIMARY KEY (`id`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='绑定模版消息用户';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_online")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_online` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '用户id',\r\n  `member` int(11) DEFAULT NULL COMMENT '未读条数',\r\n  `type` int(11) DEFAULT NULL COMMENT '类型',\r\n  `content` longtext COMMENT '内容',\r\n  `updatetime` varchar(50) DEFAULT NULL COMMENT '更新时间',\r\n  `createtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`createtime`,`member`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='客服';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_online_log")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_online_log` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `pid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '发送者用户id',\r\n  `type` int(11) DEFAULT NULL COMMENT '类型1文本2图片',\r\n  `content` longtext COMMENT '内容',\r\n  `createtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',\r\n  `duty` int(11) DEFAULT '1' COMMENT '身份1客户2客服',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`type`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='客服记录';";
        pdo_run($sql);
    }
    if (!pdo_fieldexists("xc_train_video_class", "type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_video_class") . " ADD `type` int(11) DEFAULT '1' COMMENT '类型（1视频2音频）'");
    }
    if (!pdo_tableexists("xc_train_audio")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_audio` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `name` varchar(255) DEFAULT NULL COMMENT '标题',\r\n  `cid` int(11) DEFAULT NULL COMMENT '分类',\r\n  `simg` varchar(255) DEFAULT NULL COMMENT '封面',\r\n  `price` decimal(10,2) DEFAULT '0.00' COMMENT '价格',\r\n  `member` int(11) DEFAULT NULL COMMENT '集数',\r\n  `sold` int(11) DEFAULT '0' COMMENT '销售数',\r\n  `mark` int(11) DEFAULT '0' COMMENT '收藏人数',\r\n  `code` varchar(255) DEFAULT NULL COMMENT '二维码',\r\n  `content` longtext COMMENT '详情',\r\n  `sort` int(11) DEFAULT '0' COMMENT '排序',\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `createtime` datetime DEFAULT NULL COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`cid`,`sort`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='音频';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_audio_item")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_audio_item` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `name` varchar(255) DEFAULT NULL COMMENT '名称',\r\n  `pid` int(11) DEFAULT NULL COMMENT '课程id',\r\n  `simg` varchar(255) DEFAULT NULL COMMENT '封面',\r\n  `audio` varchar(255) DEFAULT NULL COMMENT '音频',\r\n  `click` int(11) DEFAULT '0' COMMENT '点击数',\r\n  `try` int(11) DEFAULT '-1' COMMENT '试听',\r\n  `sort` int(11) DEFAULT '0' COMMENT '排序',\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `createtime` datetime DEFAULT NULL COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`pid`,`sort`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='音频';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_history")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_history` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '用户id',\r\n  `pid` int(11) DEFAULT NULL,\r\n  `status` int(11) DEFAULT NULL COMMENT '状态',\r\n  `createtime` datetime DEFAULT NULL COMMENT '时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`pid`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='历史';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_mark")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_mark` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '用户id',\r\n  `pid` int(11) DEFAULT NULL,\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `createtime` datetime DEFAULT NULL COMMENT '时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`pid`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='收藏';";
        pdo_run($sql);
    }
    if (!pdo_fieldexists("xc_train_order", "sign")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `sign` longtext");
    }
    if (!pdo_fieldexists("xc_train_discuss", "reply_status")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_discuss") . " ADD `reply_status` int(11) DEFAULT '-1' COMMENT '回复状态'");
    }
    if (!pdo_fieldexists("xc_train_discuss", "reply_content")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_discuss") . " ADD `reply_content` longtext COMMENT '回复内容'");
    }
    if (!pdo_fieldexists("xc_train_history", "type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_history") . " ADD `type` int(11) DEFAULT '1' COMMENT '类型（1音频2视频）'");
    }
    if (!pdo_fieldexists("xc_train_nav", "type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_nav") . " ADD `type` int(11) DEFAULT '1' COMMENT '类型（1链接2客服）'");
    }
    if (!pdo_fieldexists("xc_train_news", "index")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_news") . " ADD `index` int(11) DEFAULT '-1' COMMENT '首页显示'");
    }
    if (!pdo_fieldexists("xc_train_news", "cid")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_news") . " ADD `cid` int(11) DEFAULT NULL COMMENT '分类'");
    }
    if (!pdo_tableexists("xc_train_apply")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_apply` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '用户id',\r\n  `name` varchar(50) DEFAULT NULL COMMENT '姓名',\r\n  `mobile` varchar(50) DEFAULT NULL COMMENT '电话',\r\n  `status` int(11) DEFAULT '-1' COMMENT '状态（-1审核中1审核通过2失败3失败已阅）',\r\n  `createtime` datetime DEFAULT NULL COMMENT '申请时间',\r\n  `applytime` datetime DEFAULT NULL COMMENT '处理时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`status`,`createtime`,`applytime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='审核';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_share_withdraw")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_share_withdraw` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '用户id',\r\n  `out_trade_no` varchar(50) DEFAULT NULL COMMENT '订单号',\r\n  `type` int(11) DEFAULT NULL COMMENT '提现方式（1微信2支付宝）',\r\n  `amount` decimal(10,2) DEFAULT NULL COMMENT '提现金额',\r\n  `username` varchar(50) DEFAULT NULL COMMENT '账号',\r\n  `name` varchar(50) DEFAULT NULL COMMENT '姓名',\r\n  `mobile` varchar(50) DEFAULT NULL COMMENT '手机号',\r\n  `code` varchar(255) DEFAULT NULL COMMENT '收款码',\r\n  `status` int(11) DEFAULT '-1' COMMENT '状态（-1待处理1成功2失败）',\r\n  `createtime` datetime DEFAULT NULL COMMENT '申请时间',\r\n  `applytime` datetime DEFAULT NULL COMMENT '处理时间',\r\n  `fee` decimal(10,2) DEFAULT '0.00' COMMENT '手续费',\r\n  `order_type` int(11) DEFAULT '1' COMMENT '类型（1分销2团长）',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`type`,`out_trade_no`,`status`,`createtime`,`applytime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分销提现';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_share_order")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_share_order` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '用户id',\r\n  `out_trade_no` varchar(50) DEFAULT NULL COMMENT '订单号',\r\n  `type` int(11) DEFAULT NULL COMMENT '分销等级',\r\n  `amount` decimal(10,2) DEFAULT NULL COMMENT '佣金',\r\n  `order_amount` decimal(10,2) DEFAULT NULL COMMENT '订单金额',\r\n  `status` int(11) DEFAULT '-1' COMMENT '状态（-1待结算1已结算2失效）',\r\n  `share` varchar(50) DEFAULT NULL COMMENT '分销用户id',\r\n  `createtime` datetime DEFAULT NULL COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='佣金订单';";
        pdo_run($sql);
    }
    if (!pdo_fieldexists("xc_train_userinfo", "share")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_userinfo") . " ADD `share` varchar(50) DEFAULT NULL COMMENT '推荐人'");
    }
    if (!pdo_fieldexists("xc_train_userinfo", "share_fee")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_userinfo") . " ADD `share_fee` decimal(10,2) DEFAULT '0.00' COMMENT '佣金'");
    }
    if (!pdo_fieldexists("xc_train_userinfo", "share_code")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_userinfo") . " ADD `share_code` varchar(255) DEFAULT NULL COMMENT '分销二维码'");
    }
    if (!pdo_fieldexists("xc_train_service", "share_one")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_service") . " ADD `share_one` varchar(50) DEFAULT NULL COMMENT '一级分销'");
    }
    if (!pdo_fieldexists("xc_train_service", "share_two")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_service") . " ADD `share_two` varchar(50) DEFAULT NULL COMMENT '二级分销'");
    }
    if (!pdo_fieldexists("xc_train_service", "share_three")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_service") . " ADD `share_three` varchar(50) DEFAULT NULL COMMENT '三级分销'");
    }
    if (!pdo_fieldexists("xc_train_order", "share_one_openid")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `share_one_openid` varchar(50) DEFAULT NULL COMMENT '一级分销用户'");
    }
    if (!pdo_fieldexists("xc_train_order", "share_one_fee")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `share_one_fee` varchar(50) DEFAULT NULL COMMENT '一级分销佣金'");
    }
    if (!pdo_fieldexists("xc_train_order", "share_two_openid")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `share_two_openid` varchar(50) DEFAULT NULL COMMENT '二级分销用户'");
    }
    if (!pdo_fieldexists("xc_train_order", "share_two_fee")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `share_two_fee` varchar(50) DEFAULT NULL COMMENT '二级分销佣金'");
    }
    if (!pdo_fieldexists("xc_train_order", "share_three_openid")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `share_three_openid` varchar(50) DEFAULT NULL COMMENT '三级分销用户'");
    }
    if (!pdo_fieldexists("xc_train_order", "share_three_fee")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `share_three_fee` varchar(50) DEFAULT NULL COMMENT '三级分销佣金'");
    }
    if (!pdo_fieldexists("xc_train_order", "line_name")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `line_name` varchar(50) DEFAULT NULL COMMENT '礼包名称'");
    }
    if (!pdo_fieldexists("xc_train_order", "line_img")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `line_img` varchar(255) DEFAULT NULL COMMENT '礼包图片'");
    }
    if (!pdo_fieldexists("xc_train_order", "line_data")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `line_data` longtext COMMENT '礼包数据'");
    }
    if (!pdo_fieldexists("xc_train_mall", "share_one")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_mall") . " ADD `share_one` varchar(50) DEFAULT NULL COMMENT '一级分销'");
    }
    if (!pdo_fieldexists("xc_train_mall", "share_two")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_mall") . " ADD `share_two` varchar(50) DEFAULT NULL COMMENT '二级分销'");
    }
    if (!pdo_fieldexists("xc_train_mall", "share_three")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_mall") . " ADD `share_three` varchar(50) DEFAULT NULL COMMENT '三级分销'");
    }
    if (!pdo_fieldexists("xc_train_video", "share_one")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_video") . " ADD `share_one` varchar(50) DEFAULT NULL COMMENT '一级分销'");
    }
    if (!pdo_fieldexists("xc_train_video", "share_two")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_video") . " ADD `share_two` varchar(50) DEFAULT NULL COMMENT '二级分销'");
    }
    if (!pdo_fieldexists("xc_train_video", "share_three")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_video") . " ADD `share_three` varchar(50) DEFAULT NULL COMMENT '三级分销'");
    }
    if (!pdo_fieldexists("xc_train_audio", "share_one")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_audio") . " ADD `share_one` varchar(50) DEFAULT NULL COMMENT '一级分销'");
    }
    if (!pdo_fieldexists("xc_train_audio", "share_two")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_audio") . " ADD `share_two` varchar(50) DEFAULT NULL COMMENT '二级分销'");
    }
    if (!pdo_fieldexists("xc_train_audio", "share_three")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_audio") . " ADD `share_three` varchar(50) DEFAULT NULL COMMENT '三级分销'");
    }
    if (!pdo_fieldexists("xc_train_zan", "type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_zan") . " ADD `type` int(11) DEFAULT '1' COMMENT '类型（1课程2礼包）'");
    }
    if (!pdo_tableexists("xc_train_line")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_line` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `name` varchar(50) DEFAULT NULL COMMENT '名称',\r\n  `cid` int(11) DEFAULT NULL COMMENT '分类',\r\n  `simg` varchar(255) DEFAULT NULL COMMENT '图片',\r\n  `price` decimal(10,2) DEFAULT '0.00' COMMENT '价格',\r\n  `start_time` datetime DEFAULT NULL COMMENT '开始时间',\r\n  `end_time` datetime DEFAULT NULL COMMENT '结束时间',\r\n  `click` int(11) DEFAULT '0' COMMENT '浏览量',\r\n  `video` longtext COMMENT '视频',\r\n  `audio` longtext COMMENT '音频',\r\n  `sort` int(11) DEFAULT '0' COMMENT '排序',\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `content` longtext COMMENT '详情',\r\n  `share_one` varchar(50) DEFAULT NULL COMMENT '一级分销',\r\n  `share_two` varchar(50) DEFAULT NULL COMMENT '二级分销',\r\n  `share_three` varchar(50) DEFAULT NULL COMMENT '三级分销',\r\n  `createtime` datetime DEFAULT NULL COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`sort`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='礼包';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_line_order")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_line_order` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '用户id',\r\n  `out_trade_no` varchar(50) DEFAULT NULL COMMENT '订单号',\r\n  `line` int(11) DEFAULT NULL COMMENT '礼包id',\r\n  `type` int(11) DEFAULT NULL COMMENT '类型（1视频2音频）',\r\n  `pid` int(11) DEFAULT NULL,\r\n  `createtime` datetime DEFAULT NULL COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`type`,`pid`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='购买记录';";
        pdo_run($sql);
    }
    if (!pdo_fieldexists("xc_train_order", "group_member")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `group_member` int(11) DEFAULT NULL COMMENT '团购人数'");
    }
    if (!pdo_fieldexists("xc_train_order", "group_price")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `group_price` varchar(50) DEFAULT NULL COMMENT '团购价格'");
    }
    if (!pdo_fieldexists("xc_train_order", "group_end")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `group_end` datetime DEFAULT NULL COMMENT '团购结束时间'");
    }
    if (!pdo_fieldexists("xc_train_order", "group_data")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `group_data` longtext COMMENT '团购数据'");
    }
    if (!pdo_tableexists("xc_train_service_group")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_service_group` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `pid` int(11) DEFAULT NULL COMMENT '课程',\r\n  `mark` varchar(50) DEFAULT NULL COMMENT '标识',\r\n  `simg` varchar(255) DEFAULT NULL COMMENT '封面',\r\n  `bimg` longtext COMMENT '轮播图',\r\n  `price` varchar(50) DEFAULT NULL COMMENT '原价',\r\n  `format` longtext COMMENT '规格',\r\n  `sold` int(11) DEFAULT '0' COMMENT '已团',\r\n  `group_times` int(11) DEFAULT NULL COMMENT '团购时间',\r\n  `member_on` int(11) DEFAULT '0' COMMENT '已有人数',\r\n  `member` int(11) DEFAULT '0' COMMENT '人数',\r\n  `end_time` datetime DEFAULT NULL COMMENT '截止时间',\r\n  `click` int(11) DEFAULT '0' COMMENT '点击量',\r\n  `sort` int(11) DEFAULT '0' COMMENT '排序',\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `content` longtext COMMENT '详情',\r\n  `createtime` datetime DEFAULT NULL COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`pid`,`sort`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='团购';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_group_service")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_group_service` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '用户id',\r\n  `service` int(11) DEFAULT NULL COMMENT '课程id',\r\n  `member_on` int(11) DEFAULT '0' COMMENT '已有人数',\r\n  `member` int(11) DEFAULT '0' COMMENT '人数',\r\n  `group_price` decimal(10,2) DEFAULT NULL COMMENT '价格',\r\n  `failtime` datetime DEFAULT NULL COMMENT '失败时间',\r\n  `status` int(11) DEFAULT '-1' COMMENT '状态（-1拼团中1成功2失败）',\r\n  `createtime` datetime DEFAULT NULL COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='团购课程';";
        pdo_run($sql);
    }
    if (!pdo_fieldexists("xc_train_share_withdraw", "fee")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_share_withdraw") . " ADD `fee` decimal(10,2) DEFAULT '0.00' COMMENT '手续费'");
    }
    if (!pdo_fieldexists("xc_train_userinfo", "score")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_userinfo") . " ADD `score` int(11) DEFAULT '0' COMMENT '积分'");
    }
    if (!pdo_tableexists("xc_train_score_mall")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_score_mall` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `name` varchar(50) DEFAULT NULL COMMENT '名称',\r\n  `simg` varchar(255) DEFAULT NULL COMMENT '封面',\r\n  `bimg` longtext,\r\n  `score` int(11) DEFAULT '0' COMMENT '兑换积分',\r\n  `kucun` int(11) DEFAULT '0' COMMENT '库存',\r\n  `sold` int(11) DEFAULT '0' COMMENT '已兑',\r\n  `sort` int(11) DEFAULT '0' COMMENT '排序',\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `content` longtext COMMENT '详情',\r\n  `createtime` datetime DEFAULT NULL COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`sort`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分商城';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_score_check")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_score_check` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '用户id',\r\n  `plan_date` varchar(50) DEFAULT NULL COMMENT '签到日期',\r\n  `times` int(11) DEFAULT NULL COMMENT '签到天数',\r\n  `createtime` datetime DEFAULT NULL COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='签到记录';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_score_record")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_score_record` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `name` varchar(50) DEFAULT NULL COMMENT '名称',\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '用户id',\r\n  `pid` varchar(50) DEFAULT NULL,\r\n  `type` int(11) DEFAULT NULL COMMENT '类型（1收入2支出）',\r\n  `score` int(11) DEFAULT '0' COMMENT '积分',\r\n  `createtime` datetime DEFAULT NULL COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`type`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分明细';";
        pdo_run($sql);
    }
    if (!pdo_fieldexists("xc_train_order", "address")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `address` varchar(255) DEFAULT NULL COMMENT '地址'");
    }
    if (!pdo_fieldexists("xc_train_order", "score")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `score` int(11) DEFAULT NULL COMMENT '积分'");
    }
    if (!pdo_fieldexists("xc_train_order", "service_name")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `service_name` varchar(50) DEFAULT NULL COMMENT '名称'");
    }
    if (!pdo_fieldexists("xc_train_order", "service_data")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `service_data` longtext COMMENT '数据'");
    }
    if (!pdo_fieldexists("xc_train_order", "store_name")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `store_name` varchar(50) DEFAULT NULL COMMENT '名称'");
    }
    if (!pdo_fieldexists("xc_train_order", "store_data")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `store_data` longtext COMMENT '数据'");
    }
    if (!pdo_tableexists("xc_train_move")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_move` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `name` varchar(50) DEFAULT NULL COMMENT '名称',\r\n  `store` int(11) DEFAULT NULL COMMENT '学校',\r\n  `simg` varchar(255) DEFAULT NULL COMMENT '封面',\r\n  `bimg` longtext COMMENT '图片',\r\n  `member` int(11) DEFAULT '0' COMMENT '人数',\r\n  `member_on` int(11) DEFAULT '0' COMMENT '已有人数',\r\n  `start_time` datetime DEFAULT NULL COMMENT '开始时间',\r\n  `end_time` datetime DEFAULT NULL COMMENT '结束时间',\r\n  `mobile` varchar(50) DEFAULT NULL COMMENT '电话',\r\n  `format` longtext COMMENT '组别',\r\n  `rules` longtext COMMENT '活动规则',\r\n  `sort` int(11) DEFAULT '0' COMMENT '排序',\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `content` longtext COMMENT '详情',\r\n  `share_one` varchar(50) DEFAULT NULL COMMENT '一级分销佣金',\r\n  `share_two` varchar(50) DEFAULT NULL COMMENT '二级分销佣金',\r\n  `share_three` varchar(50) DEFAULT NULL COMMENT '三级分销佣金',\r\n  `createtime` datetime DEFAULT NULL COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`sort`,`status`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动报名';";
        pdo_run($sql);
    }
    if (!pdo_fieldexists("xc_train_order", "start_time")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `start_time` datetime DEFAULT NULL COMMENT '开始时间'");
    }
    if (!pdo_fieldexists("xc_train_order", "end_time")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `end_time` datetime DEFAULT NULL COMMENT '结束时间'");
    }
    if (!pdo_fieldexists("xc_train_order", "service_price")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `service_price` varchar(50) DEFAULT NULL COMMENT '价格'");
    }
    if (!pdo_fieldexists("xc_train_order", "group_openid")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `group_openid` varchar(50) DEFAULT NULL COMMENT '团长用户id'");
    }
    if (!pdo_fieldexists("xc_train_order", "group_fee")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `group_fee` varchar(50) DEFAULT NULL COMMENT '团长佣金'");
    }
    if (!pdo_fieldexists("xc_train_order", "group_sale")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `group_sale` varchar(50) DEFAULT NULL COMMENT '团内优惠'");
    }
    if (!pdo_tableexists("xc_train_mall_team")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_mall_team` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `service` int(11) DEFAULT NULL COMMENT '商品',\r\n  `name` varchar(50) DEFAULT NULL COMMENT '名称',\r\n  `simg` varchar(255) DEFAULT NULL COMMENT '封面',\r\n  `bimg` longtext COMMENT '图片',\r\n  `sold` int(11) DEFAULT '0' COMMENT '已团',\r\n  `kucun` int(11) DEFAULT '0' COMMENT '库存',\r\n  `start_time` datetime DEFAULT NULL COMMENT '添加时间',\r\n  `end_time` datetime DEFAULT NULL COMMENT '添加时间',\r\n  `fee` decimal(10,2) DEFAULT NULL COMMENT '团长佣金',\r\n  `user_limit` varchar(50) DEFAULT NULL COMMENT '每人限购',\r\n  `member_join` varchar(50) DEFAULT NULL COMMENT '人数',\r\n  `member_sale` varchar(50) DEFAULT NULL COMMENT '折扣',\r\n  `sort` int(11) DEFAULT '0' COMMENT '排序',\r\n  `status` int(11) DEFAULT '1' COMMENT '状态',\r\n  `content` longtext COMMENT '详情',\r\n  `code` varchar(255) DEFAULT NULL COMMENT '二维码',\r\n  `createtime` datetime DEFAULT NULL COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`status`,`sort`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='接龙团';";
        pdo_run($sql);
    }
    if (!pdo_tableexists("xc_train_team_group")) {
        $sql = "CREATE TABLE IF NOT EXISTS `ims_xc_train_team_group` (\r\n  `id` int(11) NOT NULL AUTO_INCREMENT,\r\n  `uniacid` int(11) DEFAULT NULL,\r\n  `openid` varchar(50) DEFAULT NULL COMMENT '团长',\r\n  `service` int(11) DEFAULT NULL COMMENT '接龙团',\r\n  `member` int(11) DEFAULT '0' COMMENT '人数',\r\n  `start_time` datetime DEFAULT NULL COMMENT '开始时间',\r\n  `end_time` datetime DEFAULT NULL COMMENT '结束时间',\r\n  `createtime` datetime DEFAULT NULL COMMENT '添加时间',\r\n  PRIMARY KEY (`id`),\r\n  KEY `uniacid` (`uniacid`,`createtime`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='接龙团';";
        pdo_run($sql);
    }
    if (!pdo_fieldexists("xc_train_userinfo", "team_fee")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_userinfo") . " ADD `team_fee` decimal(10,2) DEFAULT '0.00' COMMENT '团长佣金'");
    }
    if (!pdo_fieldexists("xc_train_share_withdraw", "order_type")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_share_withdraw") . " ADD `order_type` int(11) DEFAULT '1' COMMENT '类型（1分销2团长）'");
    }
    if (!pdo_fieldexists("xc_train_order", "code")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_order") . " ADD `code` varchar(255) DEFAULT NULL COMMENT '二维码'");
    }
    if (!pdo_fieldexists("xc_train_cut_order", "code")) {
        pdo_query("ALTER TABLE " . tablename("xc_train_cut_order") . " ADD `code` varchar(255) DEFAULT NULL COMMENT '二维码'");
    }
    $json = array("status" => 1, "msg" => "更新成功");
    echo json_encode($json);
}
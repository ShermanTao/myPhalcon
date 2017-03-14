
--
-- 表的结构 `sherman_accounts`
--

CREATE TABLE IF NOT EXISTS `sherman_accounts` (
  `id` int(11)  AUTO_INCREMENT unsigned NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '账号名称',
  `password` varchar(255) NOT NULL COMMENT '账号密码',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态: 1正常 2禁用',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '账号添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='账号表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------
--
-- 转存表中的数据 `sherman_accounts`
--

INSERT INTO `sherman_accounts` (`id`, `name`, `password`, `status`, `add_time`) VALUES
(1, 'admin', '$2y$08$HRdrGZcLVWqNhltez61vAeIUCA.4/0A3rDzApH4I/NJ/UKlnBo0v.', 1, '2017-03-13 11:57:31'),
(2, 'test', '$2y$08$CmZURvIpY4DxZojFdG8dwen7bRtvmU/eD7OKt6R5i2ATiDrhk2Ika', 1, '2017-03-13 17:32:50');


-- --------------------------------------------------------
--
-- 表的结构 `sherman_users`
--

CREATE TABLE IF NOT EXISTS `sherman_users` (
  `id` int(11) AUTO_INCREMENT unsigned NOT NULL,
  `nickname` varchar(255) NOT NULL COMMENT '昵称',
  `mobile` varchar(30) DEFAULT '' COMMENT '手机',
  `status` tinyint(1) DEFAULT '1' COMMENT '用户状态 1：正常 2：禁用',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- 表的结构 `sherman_app_cards`
--
CREATE TABLE IF NOT EXISTS `sherman_app_cards` (
  `id` int(11) AUTO_INCREMENT unsigned NOT NULL,
  `account_id` int(11) NOT NULL COMMENT '账号ID',
  `status` int(5) NOT NULL DEFAULT '1',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '卡券名称',
  `pic` varchar(255) NOT NULL COMMENT '卡券图片',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  `start_time` int(11) DEFAULT '0' COMMENT '开始时间，为空为没有限制',
  `end_time` int(11) DEFAULT '0' COMMENT '截止时间，为空为没有限制',
  `content` text COMMENT '卡券描述',
  `qr_code` varchar(255) DEFAULT '' COMMENT '二维码图片',
  `exchange_limit` int(5) DEFAULT '0' COMMENT '每人限领数量',
  `stock_num` int(9) unsigned DEFAULT '0' COMMENT '库存数量',
  `total_num` int(9) unsigned DEFAULT '0' COMMENT '总数量',
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='卡券模块表' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- 表的结构 `sherman_coupon_lists`
--
CREATE TABLE IF NOT EXISTS `sherman_card_lists` (
  `id` bigint(20) AUTO_INCREMENT unsigned NOT NULL,
  `coupon_id` int(11) unsigned NOT NULL COMMENT '优惠券ID',
  `account_id` int(11) unsigned DEFAULT '0' COMMENT '账号ID',
  `user_id` int(11) unsigned DEFAULT '0' COMMENT '领取用户ID',
  `sn_number` varchar(100) NOT NULL COMMENT 'SN码',
  `status` int(1) NOT NULL DEFAULT '1' COMMENT '状态 1.未核销 2.已核销,3.已锁定',
  `send_time` int(11) DEFAULT 0 COMMENT '领取时间',
  `use_time` int(11) DEFAULT 0 COMMENT '核销时间',
  `explain` varchar(255) DEFAULT '' COMMENT '优惠券领取说明',
  PRIMARY KEY (`id`),
  KEY `coupon_id` (`coupon_id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='优惠券领取明细表' AUTO_INCREMENT=1 ;

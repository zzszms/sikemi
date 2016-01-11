<?php

/**
 *      [Discuz! X1.5] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $author : CongYuShuai(Max.Cong) Date:2010-09-01$
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$sql=<<<EOF
-- --------------------------------------------------------

--
-- 表的结构 `pre_xbtit_files`
--

CREATE TABLE IF NOT EXISTS `pre_xbtit_files` (
  `info_hash` varchar(40) NOT NULL DEFAULT '',
  `tid` int(8) NOT NULL,
  `filename` varchar(250) NOT NULL DEFAULT '',
  `url` varchar(250) NOT NULL DEFAULT '',
  `data` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `size` bigint(20) NOT NULL DEFAULT '0',
  `anonymous` enum('true','false') NOT NULL DEFAULT 'false',
  `dlbytes` bigint(20) unsigned NOT NULL DEFAULT '0',
  `seeds` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '种子数',
  `leechers` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下载中',
  `finished` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成数',
  `lastactive` int(10) NOT NULL DEFAULT '0' COMMENT '最近活动时间',
  PRIMARY KEY (`info_hash`),
  KEY `filename` (`filename`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `pre_xbtit_history`
--

CREATE TABLE IF NOT EXISTS `pre_xbtit_history` (
  `uid` int(10) DEFAULT NULL,
  `infohash` varchar(40) NOT NULL DEFAULT '',
  `date` int(10) DEFAULT NULL COMMENT '最近更新时间',
  `uploaded` bigint(20) NOT NULL DEFAULT '0' COMMENT '计算上的已上传',
  `downloaded` bigint(20) NOT NULL DEFAULT '0' COMMENT '计算上的已下载',
  `active` enum('yes','no') NOT NULL DEFAULT 'no',
  `agent` varchar(30) NOT NULL DEFAULT '',
  `makedate` int(10) DEFAULT NULL COMMENT '创建时间',
  `realdown` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '真实下载量',
  `realup` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '真实上传量',
  `tid` int(10) NOT NULL DEFAULT '0',
  UNIQUE KEY `uid` (`uid`,`infohash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `pre_xbtit_peers`
--

CREATE TABLE IF NOT EXISTS `pre_xbtit_peers` (
  `uid` int(10) NOT NULL,
  `infohash` varchar(40) NOT NULL DEFAULT '',
  `tid` int(9) NOT NULL,
  `ip` varchar(50) NOT NULL DEFAULT 'error.x',
  `port` smallint(5) unsigned NOT NULL DEFAULT '0',
  `status` enum('leecher','seeder') NOT NULL DEFAULT 'leecher',
  `lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
  `client` varchar(60) NOT NULL DEFAULT '',
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `pre_xbtit_users`
--

CREATE TABLE IF NOT EXISTS `pre_xbtit_users` (
  `uid` int(10) unsigned NOT NULL,
  `downloaded` bigint(20) DEFAULT '0',
  `uploaded` bigint(20) DEFAULT '0',
  `pid` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
EOF;
runquery($sql);

$sql = <<<EOF
CREATE TRIGGER `after_peers_insert` AFTER INSERT ON `pre_xbtit_peers` FOR EACH ROW BEGIN update  pre_xbtit_files set leechers=( select count(*) from pre_xbtit_peers where status='leecher' and infohash=new.infohash),seeds=( select count(*) from pre_xbtit_peers where status='seeder' and infohash=new.infohash) where info_hash=new.infohash;END
EOF;
runquery($sql);

$sql = <<<EOF
CREATE TRIGGER `after_peers_update` AFTER UPDATE ON `pre_xbtit_peers` FOR EACH ROW BEGIN update  pre_xbtit_files set leechers=( select count(*) from pre_xbtit_peers where status='leecher' and infohash=new.infohash),seeds=( select count(*) from pre_xbtit_peers where status='seeder' and infohash=new.infohash) where info_hash=new.infohash;END
EOF;
runquery($sql);

$sql = <<<EOF
CREATE TRIGGER `after_peers_delete` AFTER DELETE ON `pre_xbtit_peers` FOR EACH ROW BEGIN update  pre_xbtit_files set leechers=( select count(*) from pre_xbtit_peers where status='leecher' and infohash=old.infohash),seeds=( select count(*) from pre_xbtit_peers where status='seeder' and infohash=old.infohash) where info_hash=old.infohash;END
EOF;
runquery($sql);
$finish = TRUE;

?>

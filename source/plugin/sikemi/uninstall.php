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
$sql = <<<EOF
DROP TABLE pre_xbtit_users;
DROP TABLE pre_xbtit_peers;
DROP TABLE pre_xbtit_history;
DROP TABLE pre_xbtit_files;
EOF;
runquery($sql);
$finish = TRUE;
?>
<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_onlinetime_monthly.php 18493 2010-11-25 01:15:52Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

DB::query("DELETE from  ".DB::table('xbtit_peers')." where lastupdate<UNIX_TIMESTAMP()-60*60");

?>

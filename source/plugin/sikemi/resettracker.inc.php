<?php
if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
} 

include('./xbt/config.inc.php');
if(isset($_POST['changepid'])){
		$pid=md5(uniqid(rand(),true));	
		DB::query("update ".DB::table('xbtit_users')." set pid='{$pid}' where uid={$_G['uid']}");
}
$tracker=get_tracker();
template('resettracker', "sikemi","source/plugin/sikemi/templates");
function get_tracker(){
	global $_G,$tracker_address;
	$query=DB::query("select pid from ".DB::table('xbtit_users')." where uid={$_G['uid']} limit 1");
	$results=DB::fetch($query);
	if(empty($results)){
		$pid=md5(uniqid(rand(),true));	
		DB::query("insert into ".DB::table('xbtit_users')." (uid,pid) values ({$_G['uid']},'{$pid}')");
	}else{
		$pid=$results['pid'];
	}
	return $tracker_address.$pid;
}
?>
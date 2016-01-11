<?php
if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
} 
if(!isset($_GET['tid'])){
	showmessage("未指定帖子");
}else{
	$tid=$_GET['tid'];
}
$tid=$_GET['tid'];
$query=DB::query("select uid from ".DB::table('xbtit_history')." where tid={$tid} limit 12");
// $results=DB::fetch($query);
$users=array();
while($row=DB::fetch($query)){
	$users[]=$row['uid'];
}
if(empty($users)){
	$msg="<h4>你是第一个下载此资源的人哦！</h4>";
}else{
	foreach($users as $v=>$uid){
		include "xbt/config.inc.php";
		$msg.="<a href='home.php?mod=space&uid={$uid}&do=profile' target='_blank'><img src='{$ucenter_url}avatar.php?uid={$uid}&size=small'/ style='padding-left:10px;float:left;'></a>";
	}
}

include template('downloaded_users_ajax', "sikemi","source/plugin/sikemi/templates");
?>
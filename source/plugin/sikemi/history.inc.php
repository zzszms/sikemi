<?php
if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
} 
$query=DB::query("select count(*) as count from ".DB::table('xbtit_history')." where uid={$_G['uid']} ");
$totals=DB::fetch($query);
$total=$totals['count'];
$ppp=10;//每页显示条数
$current_page=isset($_GET['page'])?$_GET['page']:1;
$start_limit = ($current_page - 1) * $ppp;
if($_G['adminid']!=1){
	$uid=isset($_GET['uid'])?$_GET['uid']:$_G['uid'];
	$query=DB::query("select a.*,b.subject from ".DB::table('xbtit_history')." a left join ".DB::table('forum_thread')." b on a.tid=b.tid where a.uid={$uid} limit $start_limit ,$ppp");
}else{
	$query=DB::query("select a.*,b.subject from ".DB::table('xbtit_history')." a left join ".DB::table('forum_thread')." b on a.tid=b.tid where a.uid={$_G['uid']} order by a.makedate desc limit $start_limit ,$ppp ");
}
$i=0;
while($history=DB::fetch($query)){
	if($i%2==1){
		$historys[$i]['color']="#F2F2F2";
	}else{
		$historys[$i]['color']="#FFF";
	}
	$historys[$i]['subject']=$history['subject']== "" ? "该资源已经被删除" : $history['subject'] ;
	$historys[$i]['tid']=$history['tid'];
	$historys[$i]['makedate']=dgmdate($history['makedate'],"u");
	$historys[$i]['date']=dgmdate($history['date'],"u");
	$historys[$i]['uploaded']=sizecount($history['uploaded']);
	$historys[$i]['realup']=sizecount($history['realup']);
	$historys[$i]['downloaded']=sizecount($history['downloaded']);
	$historys[$i]['realdown']=sizecount($history['realdown']);
	$i++;
}
// $no=$historys[0]['count'];
$turnpage= multi($total, $ppp, $current_page, "home.php?mod=spacecp&ac=plugin&id=sikemi:history");
template('history', "sikemi","source/plugin/sikemi/templates");
?>

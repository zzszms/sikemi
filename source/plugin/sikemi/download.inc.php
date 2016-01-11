<?php
if(!defined('IN_DISCUZ')){
	exit('Access Denied');
}
require_once ("source/plugin/sikemi/BDecode.php");
require_once ("source/plugin/sikemi/BEncode.php");
if(!(isset($_G['gp_source']) && $_G['gp_source']=='rss')){
    if(!$_G['uid']>0){
        showmessage('您还没有登录，请登录后下载', '');
    }
}
if(!isset($_GET['tid']) || !$_GET['tid']>0){
	showmessage('参数错误', '');
}else{
	$tid=$_GET['tid'];
}

$query=DB::query("select * from ".DB::table('xbtit_files')." where tid={$tid} limit 1");
$results=DB::fetch($query);

//老种子数据修正
if(preg_match('/^day_.*\.torrent$/',$results['url'])){
	$results['url'] = 'data/attachment/forum/'.$results['url'];
}
//修正结束
if(empty($results) || !file_exists($results['url'])){
	showmessage('该种子不存在', '');
	$filepath="";
}else{
	$filepath=$results['url'];
}
$f=urldecode(iconv('GBK','GB2312',"[hkdvd]".$results['filename']));
$fd = fopen($filepath, "rb");
$alltorrent = fread($fd, filesize($filepath));
$array = BDecode($alltorrent);
fclose($fd);
$query=DB::query("select pid from ".DB::table('xbtit_users')." where uid={$_G['uid']} limit 1");
$results=DB::fetch($query);
if(empty($results)){
	$pid=md5(uniqid(rand(),true));	
	DB::query("insert into ".DB::table('xbtit_users')." (uid,pid) values ({$_G['uid']},'{$pid}')");
}else{
	$pid=$results['pid'];
}
$array["announce"] = $_G['setting']['discuzurl']."/xbt/announce.php?pid=$pid";
if (isset($array["announce-list"]) && is_array($array["announce-list"])){
	for ($i=0;$i<count($array["announce-list"]);$i++){
		if ($i==0)
			$array["announce-list"][$i][0] = "http://".$_G['setting']['discuzurl']."/xbt/announce.php?pid=$pid";
		else
			$array["announce-list"][$i][0] = "";
	}
}

$alltorrent=BEncode($array);

header("Content-Type: application/x-bittorrent");
header('Content-Disposition: attachment; filename="'.$f.'"');
print($alltorrent);
?>

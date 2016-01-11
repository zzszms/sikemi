<?php

/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: homegrids.class.php 20541 2009-10-09 00:34:37Z monkey $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class threadplugin_sikemi {

	var $name = '发布资源';			//主题类型名称
	var $iconfile = 'source/plugin/sikemi/images/sikemi.gif';		//images/icons/ 目录下新增的主题类型图片文件名
	var $buttontext = '发布资源';		//发帖时按钮文字

	function newthread($fid, $tid) {
		global $_G;
		$query=DB::query("select pid from ".DB::table('xbtit_users')." where uid={$_G['uid']} limit 1");
		$results=DB::fetch($query);
		if(empty($results)){
			$pid=md5(uniqid(rand(),true));	
			DB::query("insert into ".DB::table('xbtit_users')." (uid,pid) values ({$_G['uid']},'{$pid}')");
		}else{
			$pid=$results['pid'];
		}
		include('./xbt/config.inc.php');
		return <<<EOB
		<div style="border:dashed 4px #ccc;padding:10px 10px 10px 10px;margin-bottom:10px">
		<p>您的tracker地址为：<span style="color:#F00;">{$tracker_url}{$pid}</span>
		<span class="xw0 xs1 xg1">
		<a title="复制tracker地址" href="javascript:setCopy('{$tracker_url}{$pid}', '复制tracker地址成功');">[复制链接]</a></span>
		</p><p style="margin-top:10px;">
		不会做种？<a href="{$help_url}" target="_blank" style="color:#00F;">这里有教程</a>&nbsp;种子文件:&nbsp;<input type='file' name='torrent' size='30' /></p>
		</div>
EOB;
	}

	function newthread_submit($fid, $tid) {
		if($_FILES['torrent']['size'] == 0){
			showmessage('未选择种子文件');
		}elseif(substr($_FILES['torrent']['name'],-7)!='torrent'){
			showmessage('选择文件不合法');
		}
	}

	function newthread_submit_end($fid,$tid) {
		include('./source/plugin/sikemi/upload.inc.php');
	}

	function editpost($fid, $tid) {
		global $_G;
		$query=DB::query("select pid from ".DB::table('xbtit_users')." where uid={$_G['uid']} limit 1");
		$results=DB::fetch($query);
		if(empty($results)){
			$pid=md5(uniqid(rand(),true));	
			DB::query("insert into ".DB::table('xbtit_users')." (uid,pid) values ({$_G['uid']},'{$pid}')");
		}else{
			$pid=$results['pid'];
		}
		include('./xbt/config.inc.php');
		return <<<EOB
		<div style="border:dashed 4px #ccc;padding:10px 10px 10px 10px;margin-bottom:10px">
		<p>您的tracker地址为：<span style="color:#F00;">{$tracker_url}{$pid}</span>
		<span class="xw0 xs1 xg1">
		<a title="复制tracker地址" href="javascript:setCopy('{$tracker_url}{$pid}', '复制tracker地址成功');">[复制链接]</a></span>
		</p><p style="margin-top:10px;">
		不会做种？<a href="{$help_url}" target="_blank" style="color:#00F;">这里有教程</a>&nbsp;种子文件:&nbsp;<input type='file' name='torrent' size='30' />(留空为不修改种子！)</p>
		<input type='hidden' name='edittorrent' value=1 />
		</div>
EOB;
	}

	function editpost_submit($fid, $tid) {

	}

	function editpost_submit_end($fid, $tid) {
		if($_FILES['torrent']['size'] != 0){
			include('./source/plugin/sikemi/upload.inc.php');
		}
	}

	function newreply_submit_end($fid, $tid) {

	}
	
	function viewthread($tid) {
		global $_G;
		$query=DB::query("select * from ".DB::table('xbtit_files')." where tid={$tid} limit 1");
		$results=DB::fetch($query);
		$filename=cutstr($results['filename'],40,"...");
		$lastactive=dgmdate($results['lastactive'],"u");
		$size=sizecount($results['size']);
		
		include('./xbt/config.inc.php');
		$query=DB::query("select displayorder,highlight,digest from ".DB::table('forum_thread')." where tid={$tid} limit 1");
		$rs=DB::fetch($query);
		if($rs['displayorder']>0){
			$up_image="up".$upload_weight['top'].".gif";
			$down_image="down".$down_weight['top'].".gif";
		}elseif($rs['digest']>0){
			$up_image="up".$upload_weight['digest'].".gif";
			$down_image="down".$down_weight['digest'].".gif";
		}elseif($rs['highlight']>0){
			$up_image="up".$upload_weight['highlight'].".gif";
			$down_image="down".$down_weight['highlight'].".gif";
		}else{
			$up_image="up".$upload_weight['normal'].".gif";
			$down_image="down".$down_weight['normal'].".gif";
		}
		if($_G['uid']>0){
            if($results['seeds']=='0') {
                return <<<EOB
                <div style="border:dashed 4px #ccc;padding-bottom:10px;margin-bottom:20px;">
                <span style="font-family: 微软雅黑;margin-top:10px;padding-left:10px;">
                    种子: <span style="color: red;">{$results['seeds']}</span>
                    下载中: <span style="color: red;">{$results['leechers']}</span>
                    完成: <span style="color: red;">{$results['finished']}</span>
                    大小: <span style="color: red;">{$size}</span>
                    最近活动时间: <span style="color: red;">{$lastactive}</span>
                    <img title="下载权值" src="source/plugin/sikemi/images/{$down_image}" alt="normal" align="absmiddle"></span>
                    <img title="上传权值" src="source/plugin/sikemi/images/{$up_image}" alt="normal" align="absmiddle">
                    
                </span><br/>
                <span style="padding-left:10px;">
                    <a href="http://www.utorrent.com/intl/zh_cn/" target="_blank"><img title="请使用utorrent打开种子文件" src="source/plugin/sikemi/images/torrent.gif"align="absmiddle"></a>
                    <a onclick="if(!confirm('此资源种子数为0，下载可能没有速度，是否继续下载？（你也可以联系上传者和或已下载的会员哦！）')){return false;}" style="font-weight: bold;color:#09C" title="{$results['filename']}" href="plugin.php?id=sikemi:download&tid={$tid}">{$filename}</a>&nbsp;(<a href="plugin.php?id=sikemi:torr_info&tid={$tid}" style="color:#09C" onclick="showWindow('torrentinfo', this.href);return false;">查看种子详情</a>)&nbsp;
                        <a href="plugin.php?id=sikemi:downloaded_users&tid={$tid}" class="xw0 xs1 xg1" onclick="showWindow('torrentinfo', this.href);return false;" title="如果没有种子，可以联系他们哦！">[哪些人下载过]</a>
                </span>
                </div>
EOB;
            } else {
                return <<<EOB
                    <div style="border:dashed 4px #ccc;padding-bottom:10px;margin-bottom:20px;">
                    <span style="font-family: 微软雅黑;margin-top:10px;padding-left:10px;">
                        种子: <span style="color: red;">{$results['seeds']}</span>
                        下载中: <span style="color: red;">{$results['leechers']}</span>
                        完成: <span style="color: red;">{$results['finished']}</span>
                        大小: <span style="color: red;">{$size}</span>
                        最近活动时间: <span style="color: red;">{$lastactive}</span>
                        <img title="下载权值" src="source/plugin/sikemi/images/{$down_image}" alt="normal" align="absmiddle"></span>
                        <img title="上传权值" src="source/plugin/sikemi/images/{$up_image}" alt="normal" align="absmiddle">
                        
                    </span><br/>
                    <span style="padding-left:10px;">
                        <a href="http://www.utorrent.com/intl/zh_cn/" target="_blank"><img title="请使用utorrent打开种子文件" src="source/plugin/sikemi/images/torrent.gif"align="absmiddle"></a>
                        <a style="font-weight: bold;color:#09C" title="{$results['filename']}" href="plugin.php?id=sikemi:download&tid={$tid}">{$filename}</a>&nbsp; 
(<a href="plugin.php?id=sikemi:torr_info&tid={$tid}" style="color:#09C" onclick="showWindow('torrentinfo', this.href);return false;">查看种子详情</a>)&nbsp;
                        <a href="plugin.php?id=sikemi:downloaded_users&tid={$tid}" class="xw0 xs1 xg1" onclick="showWindow('torrentinfo', this.href);return false;" title="如果没有种子，可以联系他们哦！">[哪些人下载过]</a>
                    </span>
                    </div>
EOB;
            }
		}else{
			return <<<EOB
			<div style="border:dashed 4px #ccc;padding-bottom:10px;margin-bottom:20px;">
			<span style="font-family: 微软雅黑;margin-top:10px;padding-left:10px;">
				种子: <span style="color: red;">{$results['seeds']}</span>
				下载中: <span style="color: red;">{$results['leechers']}</span>
				完成: <span style="color: red;">{$results['finished']}</span>
				大小: <span style="color: red;">{$size}</span>
				最近活动时间: <span style="color: red;">{$lastactive}</span>
				<img title="下载权值" src="source/plugin/sikemi/images/{$down_image}" alt="normal" align="absmiddle"></span>
				<img title="上传权值" src="source/plugin/sikemi/images/{$up_image}" alt="normal" align="absmiddle">
				
			</span><br/>
			<span style="padding-left:10px;">
				您还没有登录，登陆后才可以下载哦！<a href="member.php?mod=logging&amp;action=login" onclick="showWindow('login', this.href)" class="xi2">登录</a>&nbsp;|
				<a href="member.php?mod=register" class="xi2">立即注册</a>
			</span>
			</div>
EOB;
		}
	}
}

?>

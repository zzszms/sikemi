<?php
if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
} 
require_once DISCUZ_ROOT."./source/plugin/sikemi/BDecode.php";
require_once DISCUZ_ROOT."./source/plugin/sikemi/BEncode.php";
if(!isset($_GET['tid'])){
	showmessage("该种子可能已被删除");
}else{
	$tid=$_GET['tid'];
}
$tid=$_GET['tid'];
$query=DB::query("select url from ".DB::table('xbtit_files')." where tid={$tid} limit 1");
$results=DB::fetch($query);
if(empty($results)){
	showmessage("该种子可能已被删除");
}
$btfilename=$results['url'];

$fd = fopen($btfilename, "rb");
if (!$fd) {
	showmessage("该种子可能已被删除");
}
$alltorrent = fread($fd, filesize($btfilename));
fclose($fd);
$array = BDecode($alltorrent);

if (isset($array["info"])){
		if (isset($array["creation date"])) {
			if (is_numeric($array["creation date"]))
				$data=date("Y 年 m 月 d 日", $array["creation date"]);
			else
				$data=$array["creation date"];
		}
		
		$info = $array["info"]; 
		$a_files="";
		if (isset($info["files"])) {//多个文件时
			// print_r($info["files"]);
			foreach ($info["files"] as $file) {
				if (isset($file["path"][1])) {
					$files.=$file["path"][0];
					for ($i=1; isset($file["path"][$i]); $i++){
						$files.="/".$file["path"][$i];
						$a_files.="<tr><td>".$file["path"][0]."/".$file["path"][$i]."</td>";
						}
				}
				else {
					$files.=$file["path"][0];
					$a_files.="<tr><td>".$file["path"][0]."</td>";
				}
				$files.= "<font color=''>(".sizecount($file["length"]).")</font><br />";
				$a_files.="<td>".sizecount($file["length"])."</td></tr>";
				$allsize=$allsize+$file["length"];
			}
		}
		else {//单个文件时cutstr($results['filename'],40,"...");
			$files=$info["name"]."&nbsp;&nbsp;&nbsp;<font color='blue'>(".sizecount($info["length"]).")</font>";
			$a_files.="<tr><td>".cutstr($info["name"],20)."</td><td>".sizecount($info["length"])."</td><tr>";
			$info["name"]="";
			$allsize=$info["length"];
		}

	$allsize=sizecount($allsize);
}
$a_file=urldecode(iconv('utf-8','GB2312',$a_files));
$infoname=urldecode(iconv('utf-8','GB2312', $info["name"]));
$a_date=dgmdate($array["creation date"],"Y-m-d H:i",8);
include template('torr_info_ajax', "sikemi","source/plugin/sikemi/templates");
?>

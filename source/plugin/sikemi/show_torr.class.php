<?php

//全局嵌入点类（必须存在）
class plugin_sikemi{
}
//全局脚本嵌入点类
class plugin_sikemi_forum extends plugin_sikemi{
	function forumdisplay_thread_output() {
		global $_G;
		$tids="";
		$arr=array();
		$j=0;
		foreach($_G['forum_threadlist'] as $v){
			$arr[$j]['tid']=$v['tid'];
			$tids.=",".$v['tid'];
			$j++;
		}
		$tids=substr($tids,1);
		if($tids=="")$tids="0";
		$query=DB::query("select tid,seeds,leechers,finished,size from ".DB::table('xbtit_files')." where tid in ({$tids})");
		while($row=DB::fetch($query)){
			for($i=0;$i<count($arr);$i++){
				if($row['tid']==$arr[$i]['tid']){
					$arr[$i]['seeds']=$row['seeds'];
					$arr[$i]['leechers']=$row['leechers'];
					$arr[$i]['finished']=$row['finished'];
					$arr[$i]['size']=$row['size'];
					break;
				}
			}
		}
		
		foreach($arr as $row){
			if($row['seeds']==""){
				$return[]="";
			}else{
		                $size=sizecount($row['size']);
				$return[]="<p style='float:right;'><span style='color:#F00;'>↑{$row['seeds']}</span>&nbsp;&nbsp;&nbsp;&nbsp;<span style='color:#00F;'>↓{$row['leechers']}</span>&nbsp;&nbsp;&nbsp;&nbsp;<span style='color:#0C3;'>{$row['finished']}</span>&nbsp;&nbsp;&nbsp;&nbsp;<span style='color:#8F2A90;'>{$size}</span></p>";
			}
		}
		return $return;
}	function forumdisplay_thread_subject_output() {
		global $_G;
		$tids="";
		$arr=array();
		$j=0;
		foreach($_G['forum_threadlist'] as $v){
			$arr[$j]['tid']=$v['tid'];
			$tids.=",".$v['tid'];
			$j++;
		}
		$tids=substr($tids,1);
		if($tids=="")$tids="0";
		$query=DB::query("select tid,seeds,leechers,finished,size from ".DB::table('xbtit_files')." where tid in ({$tids})");
		while($row=DB::fetch($query)){
			for($i=0;$i<count($arr);$i++){
				if($row['tid']==$arr[$i]['tid']){
					$arr[$i]['seeds']=$row['seeds'];
					break;
				}
			}
		}
		
		foreach($arr as $row){
			if($row['seeds']==""){
				$return[]="";
			}else{
		$size=sizecount($row['size']);
		include('./xbt/config.inc.php');
                $tid=$row['tid'];
		$query=DB::query("select displayorder,highlight,digest from ".DB::table('forum_thread')." where tid= ({$tid})");
		$rs=DB::fetch($query);
                if($rs['displayorder']>0 or $rs['digest']>0 or $rs['highlight']>0){
		if($rs['displayorder']>0){
			$up=$upload_weight['top'];
			$down=$down_weight['top']*100;
		}elseif($rs['digest']>0){
			$up=$upload_weight['digest'];
			$down=$down_weight['digest']*100;
		}else{
			$up=$upload_weight['highlight'];
			$down=$down_weight['highlight']*100;
                }       $return[]="[<span style='color:#F00;'>↓{$down}%</span>&nbsp;&nbsp;<span style='color:#00F;'>↑{$up}x</span>]";
		}else{
		        $return[]="";
		}
	}
}		return $return;
}
}
?>
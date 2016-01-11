<?php
//加注释版本
/*
1、检测传来的各种参数是否合法
2、记录到历史，转换积分
3、对各种动作进行处理
*/
if (!preg_match("/^uTorrent|^μTorrent|^BitTorrent|^transmission/i", $_SERVER["HTTP_USER_AGENT"])){
    header("HTTP/1.0 500 Bad Request");
    die("This a a bittorrent application and can't be loaded into a browser");
}
include './config.inc.php';
include './include/db_mysql.class.php';
ignore_user_abort(1);		//忽略与用户的断开
error_reporting(E_ALL ^ E_NOTICE);
if (isset ($_GET["pid"]))
    $pid = $_GET["pid"];
else
    $pid = "";
if (get_magic_quotes_gpc()){
    $info_hash = bin2hex(stripslashes($_GET["info_hash"]));
}
else{
    $info_hash = bin2hex($_GET["info_hash"]);
}
$iscompact=(isset($_GET["compact"])?$_GET["compact"]=='1':false);
// 检测是否所有数据客户端都发送了
if (!isset($_GET["port"]) || !isset($_GET["downloaded"]) || !isset($_GET["uploaded"]) || !isset($_GET["left"]))
    show_error("BT客户端发送了错误的数据。");
$downloaded = (float)($_GET["downloaded"]);
$uploaded = (float)($_GET["uploaded"]);
$left = (float)($_GET["left"]);
$port = $_GET["port"];
$ip = getip();
$pid = AddSlashes(StripSlashes($pid));
if ($pid=="" || !$pid)
   show_error("请重新下载种子，种子的tracker是不合法的。");
// connect to db 连接数据库
$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
// connection is done ok 连接完成

$agent = mysql_real_escape_string($_SERVER["HTTP_USER_AGENT"]);
$respid = $db->query("SELECT pid,uid FROM {$tablepre}xbtit_users  WHERE pid='".$pid."' LIMIT 1");
if (!$respid || mysql_num_rows($respid)!=1)
	show_error("错误的pid值，用户不存在。请重新下载。");
$rowpid=mysql_fetch_assoc($respid);
$pid=$rowpid["pid"];
$uid=$rowpid["uid"];

$res_tor =$db->query("SELECT * FROM {$tablepre}xbtit_files WHERE info_hash='".$info_hash."' limit 1");
if (mysql_num_rows($res_tor)==0){
   show_error("种子还未上传到服务器，请到论坛重新上传。");//种子不在服务器上面
}else{
	$results=mysql_fetch_assoc($res_tor);
	$tid=$results['tid'];
}
//获取事件
if (isset($_GET["event"]))
    $event = $_GET["event"];
else
    $event = "";
if (!is_numeric($port) || !is_numeric($downloaded) || !is_numeric($uploaded) || !is_numeric($left))
    show_error("下载客户端发送了错误的参数！");//数据字段发送错误
//获取种子类型，用于统计流量
$rstype=$db->query("SELECT highlight,displayorder,digest FROM {$tablepre}forum_thread WHERE tid={$tid} LIMIT 1");
$typearray=mysql_fetch_assoc($rstype);
$type=$typearray['displayorder']>0 ? "top" : ($typearray['digest']>0 ? "digest" : ($typearray['highlight']>0 ? "highlight":"normal"));
// controll if client can handle gzip 如果客户端支持Gzip
if (true){
    if (stristr($_SERVER["HTTP_ACCEPT_ENCODING"],"gzip") && extension_loaded('zlib') && ini_get("zlib.output_compression") == 0){
        if (ini_get('output_handler')!='ob_gzhandler' && !$iscompact){
            ob_start("ob_gzhandler");
        }
        else{
            ob_start();
        }
    }
    else{
        ob_start();
    }
}
// end gzip controll
header("Content-type: text/plain");
header("Pragma: no-cache");
// 记录到历史，转换积分
$resstat=$db->query("SELECT realup,realdown FROM {$tablepre}xbtit_history WHERE uid={$uid} AND infohash=\"$info_hash\"");
//初始化 
if ($resstat){
	if(mysql_num_rows($resstat)>0){
		$livestat=mysql_fetch_assoc($resstat);
	}else{
		$livestat=array("realdown"=>0,"realup"=>0);
	}
	$new_download_true=max(0,$downloaded-$livestat["realdown"]);
	$new_upload_true=max(0,$uploaded-$livestat["realup"]);
	$new_download=$new_download_true*$down_weight[$type];
	$new_upload=$new_upload_true*$upload_weight[$type];
	//添加上传的积分记录
	if($new_upload>0){
		addtraffic($uid,$new_upload/1073741824,$upload_credit);
	}
	//添加下载的积分记录
	if($new_download>0){
		addtraffic($uid,$new_download/1073741824,$down_credit);
	}
}
mysql_free_result($resstat);
// begin history - 历史记录
$resu=$db->query("SELECT uid,realdown FROM {$tablepre}xbtit_history WHERE uid={$uid} AND infohash='$info_hash' limit 1");
if (mysql_num_rows($resu)==0){
	$db->query("INSERT INTO {$tablepre}xbtit_history (uid,infohash,active,agent,makedate,tid) VALUES ($uid,'$info_hash','yes','$agent',UNIX_TIMESTAMP(),{$tid})");
}
$db->query("UPDATE {$tablepre}xbtit_history set uploaded=IFNULL(uploaded,0)+$new_upload,realup=IFNULL(realup,0)+$new_upload_true,downloaded=IFNULL(downloaded,0)+$new_download,realdown=IFNULL(realdown,0)+$new_download_true,date=UNIX_TIMESTAMP(),tid={$tid} WHERE uid={$uid} AND infohash='$info_hash'");
mysql_free_result($resu);
// end history  
// 记录到peers
$db->query("UPDATE {$tablepre}xbtit_history set realup={$uploaded},realdown={$downloaded} WHERE uid={$uid} AND infohash='$info_hash'"); 
//更新活动时间
$db->query("UPDATE {$tablepre}xbtit_files set lastactive=UNIX_TIMESTAMP() WHERE info_hash='$info_hash'");
$db->query("UPDATE {$tablepre}xbtit_history set date=UNIX_TIMESTAMP() WHERE uid={$uid} and infohash='$info_hash'");
switch ($event){
    case "started":
		$start = start($info_hash, $ip, $port,$uid,$tid);
		sendRandomPeers($info_hash);
		break;
	case "stopped":
			killPeer($uid, $info_hash);
			sendRandomPeers($info_hash);
    break;
    case "completed":
        $peer_exists = getPeerInfo($uid, $info_hash);
        if (!is_array($peer_exists)) {
            start($info_hash, $ip, $port, $uid, $tid);
        }
        else {
            $db->query("UPDATE {$tablepre}xbtit_peers SET status=\"seeder\", lastupdate=UNIX_TIMESTAMP() WHERE uid={$uid} AND infohash=\"$info_hash\"");
            if (mysql_affected_rows() == 1){
				add_finished($info_hash);
            }
        }
        sendRandomPeers($info_hash);
    break;
	case "":
        $peer_exists = getPeerInfo($uid, $info_hash);
         if (!is_array($peer_exists)) {
            start($info_hash, $ip, $port, $uid, $tid);
        }
        if ($left == 0){
            $db->query("UPDATE {$tablepre}xbtit_peers SET status=\"seeder\", lastupdate=UNIX_TIMESTAMP() WHERE uid={$uid} AND infohash=\"$info_hash\"");
        }
       sendRandomPeers($info_hash);
    break;
    default:
        show_error("客户端发送未定义的事件。");
}
mysql_close();

//*********************函数*****************//
//******************************************//
function sendRandomPeers($info_hash){
	global $tablepre,$db;
    $query = "SELECT * FROM {$tablepre}xbtit_peers WHERE infohash=\"$info_hash\" ORDER BY RAND() LIMIT 30";
    echo "d";
    echo "8:intervali1800e";
    echo "12:min intervali300e";
    echo "5:peers";
    $result = @$db->query($query);
    if (isset($_GET["compact"]) && $_GET["compact"] == '1'){
        $p='';
        while ($row = mysql_fetch_assoc($result))
            $p .= str_pad(pack("Nn", ip2long($row["ip"]), $row["port"]), 6);//将ip，端口转换为二进制字符串，填充长度为6的长度
        echo strlen($p).':'.$p;
    }
    else{ // no_peer_id or no feature supported没有peer_id的时候发送
        echo 'l';
        while ($row = mysql_fetch_assoc($result))
        {
            echo "d2:ip".strlen($row["ip"]).":".$row["ip"];
            echo "4:porti".$row["port"]."ee";
        }
        echo "e";
    }
    echo "e";
    mysql_free_result($result);
}
// 删除一个种子
function killPeer($uid, $hash){
	global $tablepre,$db;
    @$db->query("DELETE FROM {$tablepre}xbtit_peers WHERE uid=\"$uid\" AND infohash=\"$hash\"");
}

function add_finished($hash){
	global $tablepre,$db;
	$db->query("UPDATE {$tablepre}xbtit_files SET finished=finished+1,lastactive=UNIX_TIMESTAMP() where info_hash='{$hash}'");
}
// Returns info on one peer //返回种子信息
function getPeerInfo($uid, $hash){
	global $tablepre,$db;
	$query = "SELECT * from {$tablepre}xbtit_peers where uid=\"$uid\" AND infohash=\"$hash\"";
	$results = $db->query($query) or show_error("tracker服务器报告：错误的种子");
	$data = mysql_fetch_assoc($results);
    if (!($data))
        return false;
    return $data;
}

function start($info_hash, $ip, $port, $uid,$tid){
	global $tablepre,$db,$left;
	$ip = getip();
    $ip = mysql_real_escape_string($ip);
    $agent = mysql_real_escape_string($_SERVER["HTTP_USER_AGENT"]);
    if ($left == 0){
        $status = "seeder";
	$query=$db->query("select * from {$tablepre}xbtit_peers where infohash=\"$info_hash\" and uid=\"$uid\"");
	$peer = mysql_fetch_array($query);
	if(empty($peer)){
		$db->query("INSERT INTO {$tablepre}xbtit_peers (infohash,port,ip,lastupdate,status,tid,client,uid) values ('$info_hash',$port,'$ip',UNIX_TIMESTAMP(),'$status',$tid,'$agent',$uid)");
	}
        mysql_free_result($query);
}    else{
        $status = "leecher";
        $credits_query=$db->query("select * from {$tablepre}common_member_count where uid={$uid}");
        $credit=mysql_fetch_assoc($credits_query);
     if(($credit['extcredits3']<=0.30 && $credit['extcredits5']>=30.0) or ($credit['extcredits3']<=0.40 && $credit['extcredits5']>=50.0) or ($credit['extcredits3']<=0.50 && $credit['extcredits5']>=100.0) or ($credit['extcredits3']<=0.60 && $credit['extcredits5']>=200.0) or ($credit['extcredits3']<=0.70 && $credit['extcredits5']>=400.0) or ($credit['extcredits3']<=0.80 && $credit['extcredits5']>=800.0)){
		show_error("您的分享率过低只能上传！");
        }    
     else{
	$query=$db->query("select * from {$tablepre}xbtit_peers where infohash=\"$info_hash\" and uid=\"$uid\"");
	$peer = mysql_fetch_array($query);
	if(empty($peer)){
		$db->query("INSERT INTO {$tablepre}xbtit_peers (infohash,port,ip,lastupdate,status,tid,client,uid) values ('$info_hash',$port,'$ip',UNIX_TIMESTAMP(),'$status',$tid,'$agent',$uid)");
	}
        mysql_free_result($query);
}
        mysql_free_result($credits_query);
}
}

function show_error($message, $log=false) {
    if ($log)
        error_log("BtiTracker: ERROR ($message)");
    echo 'd14:failure reason'.strlen($message).":$message".'e';
    die();
}
function getip() {
	if($_SERVER["HTTP_X_REAL_IP"]){
		return $_SERVER["HTTP_X_REAL_IP"];
	}
    if (getenv('HTTP_CLIENT_IP') && long2ip(ip2long(getenv('HTTP_CLIENT_IP')))==getenv('HTTP_CLIENT_IP') && validip(getenv('HTTP_CLIENT_IP')))
        return getenv('HTTP_CLIENT_IP');
    if (getenv('HTTP_X_FORWARDED_FOR') && long2ip(ip2long(getenv('HTTP_X_FORWARDED_FOR')))==getenv('HTTP_X_FORWARDED_FOR') && validip(getenv('HTTP_X_FORWARDED_FOR')))
        return getenv('HTTP_X_FORWARDED_FOR');
    if (getenv('HTTP_X_FORWARDED') && long2ip(ip2long(getenv('HTTP_X_FORWARDED')))==getenv('HTTP_X_FORWARDED') && validip(getenv('HTTP_X_FORWARDED')))
        return getenv('HTTP_X_FORWARDED');
    if (getenv('HTTP_FORWARDED_FOR') && long2ip(ip2long(getenv('HTTP_FORWARDED_FOR')))==getenv('HTTP_FORWARDED_FOR') && validip(getenv('HTTP_FORWARDED_FOR')))
        return getenv('HTTP_FORWARDED_FOR');
    if (getenv('HTTP_FORWARDED') && long2ip(ip2long(getenv('HTTP_FORWARDED')))==getenv('HTTP_FORWARDED') && validip(getenv('HTTP_FORWARDED')))
        return getenv('HTTP_FORWARDED');
    return long2ip(ip2long($_SERVER['REMOTE_ADDR']));
}
function addtraffic($uid,$size,$credit_no){
	global $db,$tablepre;
	$temp="extcredits".$credit_no;
	$$temp=$size;
	$db->query("UPDATE {$tablepre}common_member_count set {$temp}={$temp}+{$size} WHERE uid={$uid}");
        $credits_query=$db->query("select * from {$tablepre}common_member_count where uid={$uid}");
        $credit=mysql_fetch_assoc($credits_query);
        $up_credit=$credit['extcredits4'];
        $down_credit=$credit['extcredits5'];
        if ($down_credit==0){
        $db->query("UPDATE {$tablepre}common_member_count SET extcredits3=99.99 WHERE uid={$uid}");
}       else{
        $ratio=$up_credit/$down_credit;
        if ($ratio>99.99){
        $db->query("UPDATE {$tablepre}common_member_count SET extcredits3=99.99 WHERE uid={$uid}");
}       else{
        $db->query("UPDATE {$tablepre}common_member_count SET extcredits3={$ratio} WHERE uid={$uid}");
}
}       mysql_free_result($credits_query);
}
?>

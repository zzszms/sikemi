<?php
/**************************注意*****************************
1、请勿分表forum_thread
2、设置不同的权重请在插件sikemi目录下上传相应权重命名的图片
************************************************************/


//应用程序数据库连接参数
$dbhost = 'localhost';			// 数据库服务器
$dbuser = 'root';				// 数据库用户名
$dbpw = '198126';					// 数据库密码
$dbname = 'pt';				// 数据库名
$pconnect = 0;					// 数据库持久连接 0=关闭, 1=打开
$tablepre = 'pre_';   			// 表名前缀, 同一数据库安装多个论坛请修改此处
$dbcharset = 'gbk';			// MySQL 字符集, 可选 'gbk', 'big5', 'utf8', 'latin1', 留空为按照论坛字符集设定

//上传下载权重
$upload_weight['top']=2;		//置顶上传权重
$upload_weight['digest']=1.5;           //精华上传权重
$upload_weight['highlight']=1.2;        //高亮上传权重
$upload_weight['normal']=1;		//正常上传权重

$down_weight['top']=0;          //置顶下载权重
$down_weight['digest']=0.5;     //精华下载权重
$down_weight['highlight']=0.8;  //高亮下载权重
$down_weight['normal']=1;       //正常下载权重

$upload_credit=4;				//上传积分编号
$down_credit=5;					//下载积分编号
$tracker_url="http://localhost/xbt/announce.php?pid=";//tracker地址
$ucenter_url="http://localhost/uc_server/";				//UCenter地址
$help_url="http://bbs.timefilm.org/forum.php?mod=viewthread&tid=2605";								//做种教程地址，在发帖页面显示，请自行修改

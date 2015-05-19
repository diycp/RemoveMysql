<?php

//测试demo

header('Content-Type:text/html; charset=utf-8');
require('MysqlClient.class.php');

$mysql=new Mysql('http://wu.com/GitHub/RemoveMysql/Dbserver/','wdkiyttheti7outry');//这里填上服务器地址和密匙

$sql = 'SELECT *  FROM `uc_members` ' ;
$result = $mysql->gets($sql);

if($mysql->errno == 0 ){
	var_dump($result); //输出结果
}else{
	echo $mysql->error_mess;//输出错误信息
}

?>
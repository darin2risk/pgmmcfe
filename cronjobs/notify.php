#!/usr/bin/php
<?php
date_default_timezone_set("America/Los_Angeles");
$install_base="";
$includeDirectory = "$install_base/[path to includes]/includes/";
$pid=trim(file_get_contents("$install_base/pushpool/ltc-pushpoold.pid"));
exec("kill -s USR1 $pid");
exec("$install_base/stratum/scripts/stratumnotify.sh --password 'yourpass' --host localhost --port yourport",$out);

$log=fopen("$install_base/cronjobs/notify.log","a+");
$date=date("Y-m-d H:i:s");

require_once($includeDirectory."requiredFunctions.php");
$litecoinController=new LitecoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);
$info=$litecoinController->query('getmininginfo');
$info['timestamp']=time();

$sql="INSERT INTO networkblocks(blocknumber,timestamp,confirms,difficulty) VALUES ";
$sql.="(".$info['blocks'].",".$info['timestamp'].",0,".$info['difficulty'].")";
//pg_query($sql);
$sql2="UPDATE settings SET value='".$info['blocks']." WHERE setting = 'currentblock'";
//pg_query($sql2);
//fwrite($log,"[$date]\n$sql\n$sql2\n");
fwrite($log,"[$date]\n".$info['blocks']."\n");//\n$sql2\n");
fclose($log);
?>
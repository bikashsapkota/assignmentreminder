<?php
error_reporting(0);
$host = "127.12.196.130";
$user = "adminKPuA16p";
$password = "WsbUh6KJQHrF";
$datbase = "myphpapp";
$port = $OPENSHIFT_MYSQL_DB_PORT;
mysql_connect($host,$user,$password) or die(mysql_error());
mysql_select_db($datbase)or die(mysql_error());
?>

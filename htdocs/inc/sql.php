<?php 
	if (!defined("SQL"))die("Not defined");
	mysql_connect($dbhost,$dbuser,$dbpass);
	mysql_select_db($dbname);
	mysql_query("SET NAMES cp1251"); 
?>
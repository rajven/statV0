<?php
	require_once("../../inc/auth.php");
	require_once("../../inc/languages/".$language.".php");

	$id=$_GET["id"]*1;
	if (!$_POST["id"] && !$id) {
		header("location: index.php");
	}
	
	$msg_error = "";
	
	if ($_POST["edituser"]) {
		$id=$_POST["id"]*1;
		$login=mysql_escape_string(substr($_POST["login"],0,20));
	        $pass=md5($_POST["pass"]);
		
		mysql_query("update Customers set Login='$login',Pwd='$pass' where id='$id'");
		unset($_POST["pass"]);
		header("location: index.php");
		}
	
	unset($_POST); 
	
	list($login,$pass)=mysql_fetch_array(mysql_query("select Login, Pwd from Customers where id=$id"));
	
	require_once("../../inc/header.php");
?>
	<div id="cont">

	<form name="def" action="editcustom.php?id=<? echo $id; ?>" method="post">
	<input type="hidden" name="id" value=<? echo $id; ?>>
        <table  class="data" cellspacing="1" cellpadding="4">
        <tr>
        <td >Login</td>
        <td >Password</td>
	</tr>
	<tr>
        <td><input type="text" name="login" value="<? echo $login; ?>" size=20></td>
        <td><input type="text" name="pass" value="" size=20></td>
	</tr>
        <td colspan=2>
        <input type="submit" name="edituser" value="Save">
	</td>
	</table>
	</form>
<?
	require_once("../../inc/footer.php");
?>